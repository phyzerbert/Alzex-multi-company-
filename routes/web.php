<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect(route('login'));
});

Auth::routes();

Route::get('lang/{locale}', 'VerifyController@lang')->name('lang');

Route::get('/verify', 'VerifyController@show')->name('verify');
Route::post('/verify', 'VerifyController@verify')->name('verify');

Route::any('/home', 'HomeController@index')->name('home');
Route::get('/profile', 'HomeController@profile')->name('profile');

Route::get('/profile', 'UserController@profile')->name('profile');
Route::post('/updateuser', 'UserController@updateuser')->name('updateuser');
Route::get('/users/index', 'UserController@index')->name('users.index');
Route::post('/user/create', 'UserController@create')->name('user.create')->middleware('role:admin');
Route::post('/user/edit', 'UserController@edituser')->name('user.edit')->middleware('role:admin');
Route::get('/user/delete/{id}', 'UserController@delete')->name('user.delete')->middleware('role:admin');

Route::any('/category/index', 'CategoryController@index')->name('category.index');
Route::post('/category/create', 'CategoryController@create')->name('category.create');
Route::post('/category/edit', 'CategoryController@edit')->name('category.edit');
Route::get('/category/delete/{id}', 'CategoryController@delete')->name('category.delete');
Route::get('/get_company_category', 'CategoryController@get_company_category')->name('get_company_category');

Route::get('/company/index', 'CompanyController@index')->name('company.index');
Route::post('/company/create', 'CompanyController@create')->name('company.create');
Route::post('/company/edit', 'CompanyController@edit')->name('company.edit');
Route::get('/company/delete/{id}', 'CompanyController@delete')->name('company.delete');


Route::get('/account/index', 'AccountController@index')->name('account.index');
Route::post('/account/create', 'AccountController@create')->name('account.create');
Route::post('/account/edit', 'AccountController@edit')->name('account.edit');
Route::get('/account/delete/{id}', 'AccountController@delete')->name('account.delete');

Route::any('/transaction/index', 'TransactionController@index')->name('transaction.index');
Route::any('/transaction/daily', 'TransactionController@daily')->name('transaction.daily');
Route::get('/transaction/create', 'TransactionController@create')->name('transaction.create')->middleware('role:user');
Route::post('/transaction/expense', 'TransactionController@expense')->name('transaction.expense')->middleware('role:user');
Route::post('/transaction/incoming', 'TransactionController@incoming')->name('transaction.incoming')->middleware('role:user');
Route::post('/transaction/transfer', 'TransactionController@transfer')->name('transaction.transfer')->middleware('role:user');
Route::get('/transaction/edit/{id}', 'TransactionController@edit')->name('transaction.edit');
Route::post('/transaction/update', 'TransactionController@update')->name('transaction.update');
Route::get('/transaction/delete/{id}', 'TransactionController@delete')->name('transaction.delete');

Route::post('/set_pagesize', 'HomeController@set_pagesize')->name('set_pagesize');

Route::post('/get_transaction', 'TransactionController@get_transaction')->name('get_transaction');
