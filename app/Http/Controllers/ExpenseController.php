<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Account;
use App\View\Components\ExpenseTables;

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
                    // 轉換民國年為西元年 (例如 115/06/09 -> 2026-06-09)
                    $dateParts = explode('/', $consumeDate);
                    $year  = (int)$dateParts[0] + 1911;
                    $month = $dateParts[1];
                    // $day   = $dateParts[2];

                    // 格式化金額 (去掉逗號，並轉為整數或浮點數)
                    $cleanAmount = abs((float)str_replace(',', '', $amount));

                    Expense::create([
                        'amount'        => $cleanAmount,
                        'account_id'    => 1,
                        'is_expense'    => 1,
                        'other_account' => 0,
                        'expense_time'  => $year . $month,
                        'notes'         => $notes,
                    ]);

                    $count++;
                }
            }
        }

        return back()->with('success', "成功匯入 {$count} 筆資料");
    }
}
