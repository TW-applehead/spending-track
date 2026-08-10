<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Account;
use App\View\Components\ExpenseTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('account')->where('expense_time', now()->format('Ym'))->get();
        $accounts = Account::all();

        $months = [
            now()->subMonth(1)->format('Ym'),
            now()->format('Ym'),
            now()->addMonth(1)->format('Ym')
        ];

        return view('welcome', compact('accounts', 'expenses', 'months'));
    }

    public function store(Request $request)
    {
        // 驗證表單資料
        $request->validate([
            'amount' => 'required|numeric',
            'account_id' => 'required|numeric',
            'is_expense' => 'required|numeric',
            'other_account' => 'required|numeric',
        ]);

        // 創建新的花費記錄
        $expense = new Expense();
        $expense->amount = $request->input('amount');
        $expense->account_id = $request->input('account_id');
        $expense->is_expense = $request->input('is_expense');
        $expense->other_account = $request->input('other_account');
        $expense->expense_time = $request->input('expense_time');
        $expense->notes = $request->input('notes') ?? '';
        $expense->save();

        // 重定向回花費列表頁面並顯示成功訊息
        return redirect()->back()->with('success', '花費已成功儲存');
    }

    public function update(Request $request)
    {
        $expense = Expense::find($request->id);
        $expense->account_id = $request->input('account_id');
        $expense->amount = $request->amount;
        $expense->is_expense = $request->is_expense;
        $expense->other_account = $request->other_account;
        $expense->notes = $request->notes;
        $expense->updated_at = now();
        $expense->save();

        return response()->json(['status' => 'success', 'response' => '花費更新成功']);
    }

    public function delete(Request $request)
    {
        Expense::find($request->id)->delete();

        return response()->json(['status' => 'success', 'response' => '刪除成功']);
    }

    public function getExpenseTable(Request $request)
    {
        $time = $request->time;
        $component = new ExpenseTables($time);

        // 你可以在這裡使用 $component 來進行進一步的操作，例如渲染視圖
        $view = $component->render();

        // 返回渲染後的視圖
        return $view;
    }

    public function import(Request $request)
    {
        $request->validate([
            'text' => 'required'
        ]);

        $text = str_replace("\r", "", $request->text);
        $lines = explode("\n", $text);

        // 1. 清理雜訊 & 處理跨行斷行問題
        $mergedLines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // 判斷是否為消費開頭 (日期格式：兩位或三位民國年/月/日)
            if (preg_match('/^\d{2,3}\/\d{2}\/\d{2}/', $line)) {
                $mergedLines[] = $line;
            } else {
                // 只有當上一行「真的是消費紀錄」且「這行不是系統注意事項雜訊」時才合併
                if (!empty($mergedLines)) {
                    $lastIndex = count($mergedLines) - 1;

                    // 過濾掉信用卡條款、繳款說明等雜訊（以 *、◎、說明 等開頭的行）
                    if (!preg_match('/^[\*◎]|若您要|自動提款/', $line)) {
                        $mergedLines[$lastIndex] .= ' ' . $line;
                    }
                }
            }
        }

        $count = 0;
        foreach ($mergedLines as $line) {
            // 正則表達式解析說明：
            // Group 1: 消費日期 (例: 115/06/09)
            // Group 2: 入帳日期 (例: 115/06/11)
            // Group 3: 說明 + 金額部分
            if (preg_match('/^(\d{2,3}\/\d{2}\/\d{2})\s+(\d{2,3}\/\d{2}\/\d{2})\s+(.+)$/', $line, $match)) {
                $consumeDate = $match[1]; // 消費日期
                $rawContent  = trim($match[3]); // 說明 + 後續所有金額字串
                $notes = '';
                $amount = 0;

                // 【情況 A】：國外交易服務費 (例如：國外交易服務費-290.00 4)
                if (preg_match('/^(國外交易服務費.*?)\s*(-?[\d\.,]+)$/', $rawContent, $subMatch)) {
                    $notes  = $subMatch[1];
                    $amount = $subMatch[2];
                }
                // 【情況 B】：外幣消費 (例如：LAWSONTOKYO 290 0611 JP JPY 1,467.00)
                // 抓取最後面的台幣換算金額 (例: 1,467.00)
                elseif (preg_match('/^(.*?)\s+[\d\.,]+\s+\d{4}\s+[A-Z]{2}\s+[A-Z]{3}\s+(-?[\d\.,]+)$/', $rawContent, $subMatch)) {
                    $notes  = $subMatch[1];
                    $amount = $subMatch[2];
                }
                // 【情況 C】：一般國內消費 (例如：連加*連加*統一超商TAIPEI 119 TW 或無 TW 結尾)
                // 抓取倒數第一個數字做為金額
                elseif (preg_match('/^(.*?)\s+(-?[\d,]+)(?:\s+TW)?$/', $rawContent, $subMatch)) {
                    $notes  = $subMatch[1];
                    $amount = $subMatch[2];
                }

                // 如果成功拆解出金額與備註，才寫入資料庫
                if ($notes !== '' && $amount !== 0) {
                    $dateParts = explode('/', $consumeDate);
                    $year  = (int)$dateParts[0] + 1911;
                    $month = $dateParts[1];
                    $cleanAmount = abs((float)str_replace(',', '', $amount));

                    // 將解析出來的資料存入暫存陣列
                    $parsedExpenses[] = [
                        'amount'       => $cleanAmount,
                        'expense_time' => $year . $month,
                        'notes'        => $notes,
                    ];

                    // 收集純文字說明供 OpenAI 辨識
                    $notesList[] = $notes;
                }
            }
        }

        // 如果沒有解析出任何資料，直接返回
        if (empty($parsedExpenses)) {
            return back()->with('error', '未解析出任何有效帳單資料');
        }

        // 【第二階段】呼叫 OpenAI API 批次分類
        $foodClassifications = $this->classifyNotesWithAi($notesList);

        $count = 0;

        // 【第三階段】結合 AI 判斷結果，寫入資料庫
        foreach ($parsedExpenses as $index => $item) {
            // 取得 AI 的判斷結果 (預設為 false，以防 API 回傳比對失敗)
            $isFood = $foodClassifications[$index] ?? false;

            Expense::create([
                'amount'        => $item['amount'],
                'account_id'    => $isFood ? 1 : 2, // 飲食類為 1，非飲食類為 2
                'is_expense'    => 1,
                'other_account' => 0,
                'expense_time'  => $item['expense_time'],
                'notes'         => $item['notes'],
            ]);

            $count++;
        }

        return back()->with('success', "成功匯入 {$count} 筆資料");
    }

    private function classifyNotesWithAi(array $notesList): array
    {
        $finalResults = [];
        $aiPendingList = [];

        // 先由 PHP 關鍵字「硬性規則」預過濾再丟給 AI 分類
        foreach ($notesList as $index => $note) {
            if (preg_match('/(服務費|手續費|交易費|國外交易|跨行|捷運)/u', $note)) {
                $finalResults[$index] = false;
            }
            elseif (preg_match('/(統一超商|美食|早餐)/u', $note)) {
                $finalResults[$index] = true;
            }
            else {
                // PHP 搞不定的店名、品牌、日文拼音等，放入 AI 待處理清單
                $aiPendingList[$index] = $note;
            }
        }
        // 如果所有項目都被 PHP 規則分類完畢，直接回傳結果，完全不用呼叫 API！
        if (empty($aiPendingList)) {
            ksort($finalResults);
            return array_values($finalResults);
        }

        $apiKey = config('api.groq.secret_key');

        // 如果未設定 API Key，全部預設回傳 false (account_id = 2)
        if (!$apiKey) {
            Log::warning('GROQ_API_KEY 未設定，待分類項目將自動補預設值 false');

            foreach ($aiPendingList as $index => $note) {
                $finalResults[$index] = false;
            }

            ksort($finalResults);
            return array_values($finalResults);
        }

        try {
            // 1. 將陣列轉成清單，加上編號，讓 AI 更好對應與理解
            $itemsText = "";
            $pendingOriginalKeys = array_keys($aiPendingList); // 保存原本的索引號碼
            foreach ($pendingOriginalKeys as $i => $originalIndex) {
                $itemsText .= ($i + 1) . ". " . $aiPendingList[$originalIndex] . "\n";
            }

            // 2. 重新調整 Prompt，加上明確規範與範例 (Few-shot learning)
            $systemPrompt = "你是一個專業的記帳分類助手。你的唯一任務是判斷消費說明是否為「飲食類」。

【判定規則】
1. 飲食類 (true)：嚴格確認說明中的詞彙為何，任何為餐廳名稱、連鎖超商名稱、食物名稱、外送餐點服務、連鎖餐飲名稱等，皆給予 true。
2. 非飲食類 (false)：嚴格確認說明中的詞彙為何，任何交易服務費、手續費、日用品名稱、交通、娛樂、服飾、商場店名等，皆給予 false。
3. 警告：請特別注意！說明中只要包含『服務費』、『手續費』等金融關鍵字，無論前面接什麼字詞，都絕對不是飲食類，請強制給予 false。
4. 警告：請特別注意！嚴格依照下方範例做分類，若說明中的詞彙無法確定是什麼，請強制給予 false。

【規則範例】
- 「早餐店」 -> true
- 「捷運」 -> false
- 「AEON」 -> false
- 「麵屋」 -> true
- 「商場」 -> false
- 「火鍋店」 -> true
- 「brunch」 -> true
- 「廚房」 -> true

【輸出格式】
必須嚴格回傳 JSON 物件，格式如：{\"results\": [true, false, false...]}
回傳陣列長度與順序必須與輸入清單數量完全一致。不要附加任何其他說明文字。";

        $userPrompt = "請分析以下清單（共 " . count($aiPendingList) . " 筆）：\n{$itemsText}";

        $response = Http::withToken($apiKey)
            ->timeout(10)
            ->post(config('api.groq.base_url'), [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                // 關鍵設定 1：設定溫度為 0，確保回應完全穩定不隨機
                'temperature' => 0.0,
                // 關鍵設定 2：強制使用 JSON 格式
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawContent = $data['choices'][0]['message']['content'] ?? '{}';
                $content = json_decode($rawContent, true);

                $aiResults = $content['results'] ?? [];

                // 把 AI 的判斷結果對應回原本的索引位置
                foreach ($pendingOriginalKeys as $i => $originalIndex) {
                    $finalResults[$originalIndex] = $aiResults[$i] ?? false;
                }
            } else {
                Log::error('Groq API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Groq API Exception: ' . $e->getMessage());
        }

        for ($i = 0; $i < count($notesList); $i++) {
            if (!isset($finalResults[$i])) {
                $finalResults[$i] = false; // 防護機制：若有遺漏預設為 false
            }
        }
        ksort($finalResults);

        return array_values($finalResults);
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'account_id' => 'required|integer|exists:accounts,id',
            'raw_text'   => 'required|string|max:255',
        ]);

        $parsedData = $this->parseTextWithGroq($request->raw_text);

        if (empty($parsedData)) {
            return back()->with('error', '無法從輸入文字中解析出金額，請確認輸入內容（如：午餐120）');
        }

        DB::transaction(function () use ($parsedData, $request) {
            $firstExpenseId = null;

            foreach ($parsedData as $item) {
                $expense = Expense::create([
                    'amount'        => $item['amount'],
                    'payer_id'      => $item['payer_id'] ?? 1,
                    'consumer_id'   => $item['consumer_id'] ?? 1,
                    'account_id'    => $request->account_id,
                    'is_expense'    => 1,
                    'other_account' => 0,
                    'expense_time'  => now()->format('Ym'),
                    'notes'         => $item['notes'] ?? $request->raw_text,
                ]);

                // 判斷是否需要處理 split_group_id
                if (!empty($item['is_split'])) {
                    if ($firstExpenseId === null) {
                        // 該組拆分的第一筆：記錄自己的 ID，並更新回自己的 split_group_id
                        $firstExpenseId = $expense->id;
                        $expense->update(['split_group_id' => $firstExpenseId]);
                    } else {
                        // 該組拆分的後續筆數：直接綁定第一筆的 ID
                        $expense->update(['split_group_id' => $firstExpenseId]);
                    }
                }
            }
        });

        $success_message = '成功新增消費紀錄！';
        if (count($parsedData) > 1 && !empty($parsedData[0]['is_split'])) {
            $amounts = array_column($parsedData, 'amount');
            $totalAmount = array_sum($amounts);
            $amountStr = implode(" 與 $", $amounts);
            $success_message .= "（總金額 $" . $totalAmount . " 拆分為 $" . $amountStr . ")";
        }

        return back()->with('success', $success_message);
    }

    /**
     * 呼叫 Groq 拆解【金額】與【說明】
     */
    private function parseTextWithGroq(string $rawText): ?array
    {
        $apiKey = config('api.groq.secret_key');

        if (!$apiKey) {
            Log::warning('GROQ_API_KEY 未設定');
            return null;
        }

        try {
            $systemPrompt = "你是一個精準的記帳拆分助手。請分析使用者輸入的消費文字，將其拆解為資料庫紀錄。

【人物與 ID 對應】
- 「我」或省略主詞的預設使用者 = ID: 1
- 「老婆」或任何非「我」的人物 = ID: 2

【拆分與付款邏輯】
1. 一般個人消費（如：「午餐麥當勞 120」）：
    - 產生 1 筆紀錄。
    - amount: 120, payer_id: 1, consumer_id: 1, is_split: false

2. 平分/共同消費/老婆代付（如：「午餐麥當勞 120，老婆代付」或「被代付」）：
    - 拆分為 2 筆紀錄，且兩筆的 is_split 皆為 true。
    - 誰付錢：若是「老婆付/老婆代付」，兩筆的 payer_id 都是 2；若是強調「我付的/我出的」，或是只說了「平分/一起付」而根本沒說是誰出的，兩筆的 payer_id 都是 1。
    - 算誰的：一筆 consumer_id 為 1（我），另一筆 consumer_id 為 2（老婆）。

3. 金額無法整除規則（如：總額 35 元）：
    - 多出來的 1 元必須分配給 consumer_id = 1（我）。
    - 例如總額 35 元平分：
     * consumer_id: 1 拿到 18 元
     * consumer_id: 2 拿到 17 元

【範例參考】
輸入：「午餐麥當勞 120，老婆代付」
輸出：
{
    \"items\": [
        {\"notes\": \"午餐麥當勞\", \"amount\": 60, \"payer_id\": 2, \"consumer_id\": 1, \"is_split\": true},
        {\"notes\": \"午餐麥當勞\", \"amount\": 60, \"payer_id\": 2, \"consumer_id\": 2, \"is_split\": true}
    ]
}

輸入：「晚餐 35，平分」
輸出：
{
    \"items\": [
        {\"notes\": \"晚餐\", \"amount\": 18, \"payer_id\": 1, \"consumer_id\": 1, \"is_split\": true},
        {\"notes\": \"晚餐\", \"amount\": 17, \"payer_id\": 1, \"consumer_id\": 2, \"is_split\": true}
    ]
}

【輸出格式】
必須嚴格僅回傳 JSON 物件，格式如：{\"items\": [...]}。不要附加任何 Markdown Fences 或額外說明文字。";

            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->post(config('api.groq.base_url'), [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "待分析文字：" . $rawText]
                    ],
                    'temperature' => 0.0,
                    'response_format' => ['type' => 'json_object']
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawContent = $data['choices'][0]['message']['content'] ?? '{}';
                $result = json_decode($rawContent, true);

                return $result['items'] ?? [];
            } else {
                Log::error('Groq API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Groq API Exception: ' . $e->getMessage());
        }

        return null;
    }
}
