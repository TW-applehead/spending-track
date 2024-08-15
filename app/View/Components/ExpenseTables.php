<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Expense;
use App\Models\Account;
use Carbon\Carbon;

class ExpenseTables extends Component
{
    public $accounts;
    /**
     * Create a new component instance.
     */
    public function __construct($time)
    {
        $this->accounts = Account::withSum(['expenses as expense_sum' => function ($query) use ($time) {
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

        $this->accounts = $this->accounts->map(function ($account) use ($food_behalf_sum, $entertain_behalf_sum, $time) {
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
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.expense-tables', ['accounts' => $this->accounts]);
    }
}
