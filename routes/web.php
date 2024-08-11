<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'App\Http\Controllers\ExpenseController@index');
Route::post('/create', 'App\Http\Controllers\ExpenseController@create');
Route::post('/store', 'App\Http\Controllers\ExpenseController@store')->name('expense.store');
