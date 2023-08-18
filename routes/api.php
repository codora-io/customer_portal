<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', 'API\APIController@loginAPI');
// Route::get('billing-page/{token}', 'BillingController@getBillingPage');
Route::post('register', 'API\APIController@registerAPI');
Route::post('resendEmail', 'API\APIController@resendEmail');
Route::post('registerUser', 'API\APIController@registerUser');
Route::post('resetPassword', 'API\APIController@resetPasswordEmail');
