<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$logout = function (string $actor) {
    return function (Request $request) use ($actor) {
        app('komopay.tokens.' . $actor)->clear();
        $request->session()->forget(['actor_type', 'auth_user']);
        $request->session()->regenerate();
        return redirect()->route($actor . '.login');
    };
};

// Root — redirect to portal selection (or to dashboard if already signed in)
Route::get('/', function () {
    return view('welcome');
})->middleware('guest.portal');

// ── Customer portal ────────────────────────────────────────────────────
Route::prefix('customer')->name('customer.')->group(function () use ($logout) {
    Route::get('/login', \App\Livewire\Auth\CustomerLogin::class)->middleware('guest.portal')->name('login');

    Route::get('/dashboard', \App\Livewire\Customer\Dashboard::class)->name('dashboard');
    Route::get('/transactions', \App\Livewire\Customer\Transactions::class)->name('transactions');
    Route::get('/transactions/{id}', \App\Livewire\Customer\TransactionDetail::class)->name('transactions.show');
    Route::get('/send', \App\Livewire\Customer\SendMoney::class)->name('send');
    Route::get('/cards', \App\Livewire\Customer\Cards::class)->name('cards');
    Route::get('/statement', \App\Livewire\Customer\Statement::class)->name('statement');
    Route::get('/profile', \App\Livewire\Customer\Profile::class)->name('profile');
    Route::get('/security', \App\Livewire\Customer\Security::class)->name('security');
    Route::post('/logout', $logout('customer'))->name('logout');
});

// ── Merchant portal ────────────────────────────────────────────────────
Route::prefix('merchant')->name('merchant.')->group(function () use ($logout) {
    Route::get('/login', \App\Livewire\Auth\MerchantLogin::class)->middleware('guest.portal')->name('login');

    Route::get('/dashboard', \App\Livewire\Merchant\Dashboard::class)->name('dashboard');
    Route::get('/transactions', \App\Livewire\Merchant\Transactions::class)->name('transactions');
    Route::get('/transactions/{id}', \App\Livewire\Merchant\TransactionDetail::class)->name('transactions.show');
    Route::get('/send', \App\Livewire\Merchant\SendMoney::class)->name('send');
    Route::get('/operators', \App\Livewire\Merchant\Operators::class)->name('operators');
    Route::get('/terminals', \App\Livewire\Merchant\Terminals::class)->name('terminals');
    Route::get('/statement', \App\Livewire\Merchant\Statement::class)->name('statement');
    Route::get('/profile', \App\Livewire\Merchant\Profile::class)->name('profile');
    Route::get('/security', \App\Livewire\Merchant\Security::class)->name('security');
    Route::post('/logout', $logout('merchant'))->name('logout');
});
