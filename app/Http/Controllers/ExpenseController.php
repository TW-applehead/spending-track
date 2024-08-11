<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Account;

class ExpenseController extends Controller
{
    public function index()
    {
        $accounts = Account::all();
        return view('welcome', ['accounts' => $accounts]);
    }

    public function create()
    {
        // $accounts = Account::all(); // 假設你有一個 Account 模型
        // return view('expenses.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        // 驗證表單資料
        $request->validate([
            'amount' => 'required|numeric',
            'account_id' => 'required',
            'type' => 'required',
            'is_other_account' => 'required|boolean',
        ]);

        // 創建新的花費記錄
        $expense = new Expense();
        $expense->amount = $request->input('amount');
        $expense->account_id = $request->input('account_id');
        $expense->type = $request->input('type');
        $expense->is_other_account = $request->input('is_other_account');
        $expense->save();

        // 重定向回花費列表頁面並顯示成功訊息
        return redirect()->back()->with('success', '花費已成功儲存');
    }
}
