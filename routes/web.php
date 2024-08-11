<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'App\Http\Controllers\ExpenseController@index');

Route::prefix('expense')->group(function () {
    Route::post('/create', 'App\Http\Controllers\ExpenseController@create');
    Route::post('/store', 'App\Http\Controllers\ExpenseController@store')->name('expense.store');
});

Route::prefix('account')->group(function () {
    Route::get('/', 'App\Http\Controllers\AccountController@index')->name('accounts.index');
    Route::post('/update/{id}', 'App\Http\Controllers\AccountController@update')->name('accounts.update');
});
