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

    public function getExpenseTable(Request $request)
    {
        $time = $request->time;
        $component = new ExpenseTables($time);

        // 你可以在這裡使用 $component 來進行進一步的操作，例如渲染視圖
        $view = $component->render();

        // 返回渲染後的視圖
        return $view;
    }
}
