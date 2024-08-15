<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::all();
        return view('accounts', compact('accounts'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:32',
            'monthly_allowance' => 'required|numeric',
        ]);

        $account = Account::findOrFail($id);
        $account->name = $request->input('name');
        $account->monthly_allowance = $request->input('monthly_allowance');
        $account->save();

        return redirect()->back()->with('success', '帳戶更新成功');
    }
}
