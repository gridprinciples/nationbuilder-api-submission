<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\NationBuilderAccess;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::middleware(NationBuilderAccess::class)->group(function () {
        Route::get('nationbuilder', function () {
            dd('you are authorized');
        })->name('nationbuilder.index');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


// The route which NationBuilder redirects to
Route::get('auth/nationbuilder_oauth', function (Request $request) {
    if($request->get('error') === 'access_denied') {
        // API access error
        return abort(403);
    }

    // Get the token
    $token = app()->nationbuilder->exchangeCodeForToken($request->get('code'));

    if($token) {
        // Save the token to the user
        Auth::user()->setExternalAuthToken('nationbuilder', $token);

        return redirect()->to(
            // Redirect to the originally-intended URL
            session()->pull('nationbuilder-redirect', url('/'))
        );
    }

    return redirect()->to('/');
})->name('nationbuilder.oauth_callback');
