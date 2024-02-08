<?php

use App\Http\Controllers\EnumController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\OutfitController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users', [UserController::class, 'update'])->name('users.update');

    // クローゼットアイテム関連
    Route::post('items', [ItemController::class, 'store']);
    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{id}', [ItemController::class, 'show']);
    Route::post('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);

    // コーディネート投稿関連
    Route::post('outfit', [OutfitController::class, 'store']);
    Route::get('/outfits', [OutfitController::class, 'index']);
    Route::get('/outfit/{id}', [OutfitController::class, 'show']);
    Route::post('/outfit/{id}', [OutfitController::class, 'update']);
    Route::delete('/outfit/{id}', [OutfitController::class, 'destroy']);

    // お気に入り機能
    Route::post('likes', [LikeController::class, 'store'])->name('likes.store');
    Route::delete('/likes/{id}', [LikeController::class, 'destroy'])->name('likes.destroy');
});

Route::get('/home', [HomeController::class, 'index'])->name('home.index')->withoutMiddleware(['auth:sanctum']);
Route::post('register', [RegisteredUserController::class, 'store']);
Route::post('login', [AuthenticatedSessionController::class, 'store']);

Route::get('/enums', [EnumController::class, 'index']);
