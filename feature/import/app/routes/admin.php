
<?php

use App\Http\Controllers\Admin\PurchaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\TranslationLoader\LanguageLine;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
*/

    Route::put('/master/fragment/merge', [FragmentsController::class, 'merge'])->name('fragment.merge');
    Route::get('/master/fragment/fetch', [FragmentsController::class, 'fetch'])->name('fragment.fetch');

