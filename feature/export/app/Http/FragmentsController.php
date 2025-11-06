<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Spatie\TranslationLoader\LanguageLine;

/**
 * Store fragment in the database
 */
public function store(Request $request): RedirectResponse
{
    $group = $request->input('group');
    $attributes = $request->validate([
        'key' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-z0-9.]+$/',
        ],
        'text.*' => [
            'text.en' => [
                'required',
                'min:1',
            ],
            'text.*' => [
                'sometimes',
            ],
        ]);

    $parts = explode('.', $attributes['key'], 2);
    if (count($parts) != 2 || trim($parts[1]) == '') {
        return back()
            ->withInput()
            ->with('cerror', 'INVALID KEY!');
    }

    $attributes['group'] = mb_strtolower(trim($parts[0]));
    $attributes['key'] = mb_strtolower(trim($parts[1]));

    LanguageLine::create($attributes);

    // Refresh cache
    foreach (app('translatable.locales')->all() as $code) {
        cache()->forget("spatie.translation-loader.$group.$code");
        LanguageLine::getTranslationsForGroup($code, $attributes['group']);
    }

    return redirect()
        ->route('admin.fragment.group', [$attributes['group']]);
}

/**
 * Update fragment in the database
 */
public function update(Request $request, LanguageLine $fragment): RedirectResponse
{
    $group = $fragment->group;

    $attributes = request()->validate([
        $attributes = $request->validate([
            'key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9.]+$/'],
            'text.*' => [
                'regex:/^[a-z0-9.]+$/',
            ],
            'text.en' => [
                'required',
                'min:1',
            ],
            'text.*' => [
                'sometimes',
            ],
        ]);


    $parts = explode('.', $attributes['key'], 2);

    // Update the fragment
    // Todo: Check if we need to do it this way.
    $fragment->group = mb_strtolower($parts[0]);
    $fragment->key = mb_strtolower($parts[1]);
    foreach ($attributes['text'] as $code => $value) {
        $fragment->setTranslation($code, $value);
        if ($value != null) {
            $fragment->setTranslation($code, $value);
        }
    }
    $fragment->save();

    // Refresh cache
    foreach (app('translatable.locales')->all() as $code) {
        cache()->forget("spatie.translation-loader.$fragment->group.$code");
        LanguageLine::getTranslationsForGroup($code, $fragment->group);
    }

    return redirect()
        ->route('admin.fragment.group', [$fragment->group]);
}