<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Account;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('account')->where('expense_time', now()->format('Ym'))->get();
        $accounts = $this->searchAccountData(now()->format('Ym'));

        $months = [
            now()->subMonth(1)->format('Ym'),
            now()->format('Ym'),
            now()->addMonth(1)->format('Ym')
        ];

        return view('welcome', compact('accounts', 'expenses', 'months'));
    }

    public function searchAccountData($time)
    {
        $accounts = Account::withSum(['expenses as expense_sum' => function ($query) use ($time) {
                                $query->where('is_expense', 1)
                                    ->where('other_account', 0)
                                    ->where('expense_time', $time);
                            }], 'amount')
                            ->withSum(['expenses as income_sum' => function ($query) use ($time) {
                                $query->where('is_expense', 0)
                                    ->where('other_account', 0)
                                    ->where('expense_time', $time);
                            }], 'amount')
                            ->with(['expenses' => function ($query) use ($time) {
                                $query->where('expense_time', $time);
                            }])
                            ->get();

        // 個帳戶代收付總額
        $food_behalf_income = Expense::where('is_expense', 0)
                                    ->where('other_account', 1)
                                    ->where('expense_time', $time)
                                    ->sum('amount');
        $entertain_behalf_income = Expense::where('is_expense', 0)
                                    ->where('other_account', 2)
                                    ->where('expense_time', $time)
                                    ->sum('amount');
        $food_behalf_expense = Expense::where('is_expense', 1)
                                    ->where('other_account', 1)
                                    ->where('expense_time', $time)
                                    ->sum('amount');
        $entertain_behalf_expense = Expense::where('is_expense', 1)
                                        ->where('other_account', 2)
                                        ->where('expense_time', $time)
                                        ->sum('amount');
        $food_behalf_sum = $food_behalf_expense - $food_behalf_income;
        $entertain_behalf_sum =  $entertain_behalf_expense - $entertain_behalf_income;

        $accounts = $accounts->map(function ($account) use ($food_behalf_sum, $entertain_behalf_sum, $time) {
            $expense_sum = $account->expense_sum ?? 0;
            $income_sum = $account->income_sum ?? 0;
            // 取當月餘額
            $account_balance = Account::find($account->id)
                                        ->balances()
                                        ->where('time', $time)
                                        ->pluck('balance')
                                        ->first();
            // 取隔月餘額
            $next_month = Carbon::createFromFormat('Ym', $time)->addMonth()->format('Ym');
            $next_account_balance = Account::find($account->id)
                                            ->balances()
                                            ->where('time', $next_month)
                                            ->pluck('balance')
                                            ->first();
            if ($next_account_balance && $account_balance) {
                $balance_difference = $next_account_balance - $account_balance;
                $account->balance_difference = true;
            } else {
                $balance_difference = 0;
                $account->balance_difference = false;
            }

            if ($account->id == 1) {
                $account->quota = $balance_difference - $income_sum - $expense_sum + $food_behalf_sum - $entertain_behalf_sum;
            } else {
                $account->quota = $balance_difference - $income_sum - $expense_sum - $food_behalf_sum + $entertain_behalf_sum;
            }
            return $account;
        });

        return $accounts;
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
}
