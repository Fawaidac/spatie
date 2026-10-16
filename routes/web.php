<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ErrorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/*
 * Dynamic Captcha Route
 */
Route::get('/captcha', [CaptchaController::class, 'generate'])->name('captcha');

/*
 * Auth Routes Protected by JWT Guest Middleware
 * Users with a valid JWT token are redirected to the dashboard.
 */
Route::middleware('jwt.guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register.post');
});

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SettingController;

/*
 * Dashboard & Protected Routes Protected by JWT Auth Middleware
 * Requires a valid JWT token in Cookie, Bearer Header, or Session.
 */
Route::middleware('jwt.auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // 1. User Management CRUD
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // 2. Role Management CRUD & Permission Assignment
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::post('/roles/{role}/give-permission', [RoleController::class, 'givePermission'])->name('roles.givePermission');
    Route::post('/roles/{role}/remove-permission', [RoleController::class, 'removePermission'])->name('roles.removePermission');

    // 3. Permission Management CRUD
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    // 4. Settings & Profile Management
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.updateProfile');
    Route::put('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.updatePassword');
});

/*
 * Manual Error Page Route (For preview / demo)
 */
Route::get('/404', [ErrorController::class, 'notFound'])->name('error.404');

/*
 * Fallback Route — any unregistered route automatically renders custom 404 page
 */
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
