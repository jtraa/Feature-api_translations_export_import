
<?php

use App\Http\Controllers\Admin\PurchaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\TranslationLoader\LanguageLine;
use Carbon\Carbon;git

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->get('/translations/', function (Request $request) {

    $query = LanguageLine::query();
    $fromDate = $request->input('fromdate');
    $group = $request->input('group');
    $locales = $request->input('locales');

    if($locales) {
        $locales = explode(',', $locales);
    } else {
        $locales = app('translatable.locales')->all();
    }

    if ($fromDate) {
        $fromDate = Carbon::parse($fromDate);
        $query->where('updated_at', '>=', $fromDate);
    }

    if ($group) {
        $query->where('group', $group);
    }

    $keys = $query
        ->orderBy('group')
        ->orderBy('key')->withCasts([
            'text' => 'array',
        ])
        ->get();

    $result = [];
    if($locales) {
        $locales = array_flip($locales);
        foreach ($keys as $key) {
            $new = $key;
            unset($new->id, $new->created_at, $new->updated_at);
            $new->text = array_intersect_key($key->text, $locales);
            $result[] = $new;
        }
    } else {
        foreach ($keys as $key) {
            $new = $key;
            unset($new->id, $new->created_at, $new->updated_at);
            $result[] = $new;
        }
    }

    return response()->json([
        'keys' => $result,
    ]);
});

