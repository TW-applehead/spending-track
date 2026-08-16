<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'App\Http\Controllers\ExpenseController@index');

Route::prefix('expense')->group(function () {
    Route::post('/create', 'App\Http\Controllers\ExpenseController@create');
    Route::post('/store', 'App\Http\Controllers\ExpenseController@store')->name('expense.store');
    Route::post('/update', 'App\Http\Controllers\ExpenseController@update')->name('expense.update');
    Route::post('/batch-delete', 'App\Http\Controllers\ExpenseController@batchDelete')->name('expense.batch-delete');
    Route::post('/import', 'App\Http\Controllers\ExpenseController@import')->name('expense.import');
    Route::post('/quick-store', 'App\Http\Controllers\ExpenseController@quickStore')->name('expense.quick-store');
    Route::get('/tables', 'App\Http\Controllers\ExpenseController@getExpenseTable')->name('expense.tables');
    Route::post('/split', 'App\Http\Controllers\ExpenseController@split')->name('expense.split');
});

Route::prefix('account')->group(function () {
    Route::get('/', 'App\Http\Controllers\AccountController@index')->name('accounts.index');
    Route::post('/update/{id}', 'App\Http\Controllers\AccountController@update')->name('accounts.update');
});
