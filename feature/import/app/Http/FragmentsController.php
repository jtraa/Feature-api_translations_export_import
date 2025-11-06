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

public function fetch(LanguageLine $fragment, Request $request) {

    if ($locales = settings('translations.locales')) {
        $locales = explode('|', $locales);
    } else {
        $locales = app('translatable.locales')->all();
    }

    $response = Http::withToken(settings('support.key'))->get(settings('api.translations.url'), [
        'locales' => implode(',', $locales),
        'fromdate' => settings('translations.lastupdate', '2020-01-01'),
    ]);

    if ($response->getStatusCode() != 200) {
        dd('error');
    }

    $result = json_decode($response->getBody(), true);

    $asEnglish = '';
    if ($asEnglish) {
        foreach ($result[' keys'] as $key => $value) {
            if (isset($value['text'][$asEnglish]) && $value['text'][$asEnglish]) {
                $value['text']['en'] = $value['text'][$asEnglish];
            }
        }
    }

    $rawAll = LanguageLine::all();
    $current = [];

    foreach ($rawAll as $fragment) {
        $current[$fragment->group][$fragment->key] = $fragment->text;
    }

    $new = [];
    $merge = [];
    foreach ($result['keys'] as $entry) {
        if ($current[$entry['group']][$entry['key']] ?? false) {
            $merge[$entry['group']][$entry['key']] = array_merge($current[$entry['group']][$entry['key']], $entry['text']);
            foreach ($merge[$entry['group']][$entry['key']] as $ll => $vv) {
                if (isset($current[$entry['group']][$entry['key']][$ll])) {
                    if ($current[$entry['group']][$entry['key']][$ll] === $merge[$entry['group']][$entry['key']][$ll]) {
                        unset($merge[$entry['group']][$entry['key']][$ll]);
                    }
                }
            }
            if ($merge[$entry['group']][$entry['key']] == []) {
                unset($merge[$entry['group']][$entry['key']]);
            }
        } else {
            $new[$entry['group']][$entry['key']] = $entry['text'];
        }

        if (isset($merge[$entry['group']]) && $merge[$entry['group']] == []) {
            unset($merge[$entry['group']]);
        }
    }

    foreach ($new as $group => $keys) {
        foreach ($keys as $key => $translations) {
            LanguageLine::updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['text' => $translations],
                );
        }
    }

    $groups = array_keys($new);

    foreach (app('translatable.locales')->all() as $code) {
        foreach ($groups as $group) {
            cache()->forget("spatie.translation-loader.$group.$code");
            LanguageLine::getTranslationsForGroup($code, $group);
        }
    }

    if (!$merge) {

        $setting = CPQSetting::where('setting_key', 'tranlations.lastupdate')->first();

        if (!$setting) {
            CpqSetting::create(['setting_key' => 'tranlations.lastupdate', 'setting_value' => now()->format('Y-m-d')]);
        } else {
            $setting->setting_value = now()->format('Y-m-d');
            $setting->save();
        }

        return redirect()
            ->back()->with('success', __('success.synchronized'));
    }

    return view('admin.master.fragment.merge', compact([
        'fragment',
        'locales',
        'merge',
        'current',
    ]));
}

public function merge(Request $request)
{
    $updates = $request->input('merge');

    $touchedGroups = [];
    foreach ($updates as $group => $keys) {
        foreach ($keys as $key => $translations) {
            $line = LanguageLine::firstOrNew([
                'group' => $group,
                'key'   => $key,
            ]);

            $line->text = array_merge($line->text, $translations);
            $line->save();

            $touchedGroups[$group] = true;
        }
    }

    foreach (array_keys($touchedGroups) as $group) {
        foreach (app('translatable.locales')->all() as $code) {
            cache()->forget("spatie.translation-loader.$group.$code");
            LanguageLine::getTranslationsForGroup($code, $group);
        }
    }
    return redirect()
        ->route('admin.fragment.index')
        ->with('success', __('success.merged'));
}

/**
 * Update the fragment
 */
public function update(Request $request, LanguageLine $fragment): RedirectResponse
{
    $group = $fragment->group;

    $attributes = $request->validate([
        'key' => [
            'required',
            'string',
            'max:255',
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
