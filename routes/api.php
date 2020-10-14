<?php

use App\Http\Controllers\Authentication;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login',[Authentication::class,'login']);
Route::post('/register',[Authentication::class,'register']);
Route::get('/login',[Authentication::class,'index'])->name('login');

Route::group(['middleware' => 'auth:api'], function (){
    Route::apiResource('post',PostController::class);
    Route::get('/friendSuggestion',[FriendController::class,'showUserList']);


});

Route::group(['middleware'=>'auth:api','prefix'=>'myProfile'],function (){
    Route::post('name',[ProfileController::class,'createUserName']);
    Route::post('pic',[ProfileController::class,'setProfileImage']);
});
