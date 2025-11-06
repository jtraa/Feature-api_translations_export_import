@extends('layouts.main')

@section('content')

    <div class="header-buttons title">
        <h1>{{ __('fragments.header.merge') }}</h1>
    </div>
    <form action="{{ route('admin.fragment.merge') }}" method="POST">

        @csrf
        @method('PUT')

        <h4>Merge</h4>
        <div style="display: block; overflow-x: auto;">

            @if(!empty($merge))
                <table class="table" style="width:100%; margin-bottom:1rem; border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th width="1"><strong>{{__('column.key')}}</strong></th>
                        <th width="1">{{__('column.locale')}}</th>
                        <th>{{__('column.new')}}</th>
                        <th>{{__('column.current')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($merge as $groupKey => $group)
                        @foreach($group as $key => $translations)
                            @foreach($translations as $lang => $value)
                                @php
                                    $idMerge = "$groupKey-$key-$lang-merge";
                                    $idCurrent = "$groupKey-$key-$lang-current";
                                    $currentValue = $current[$groupKey][$key][$lang] ?? '';
                                @endphp

                                @if ($currentValue)
                                    <tr>
                                        <td>{{ $groupKey }}.{{ $key }}</td>
                                        <td>{{ strtoupper($lang) }}</td>
                                        <td>


                                            <input
                                                    type="radio"
                                                    name="merge[{{ $groupKey }}][{{ $key }}][{{ $lang }}]"
                                                    id="{{ $idMerge }}"
                                                    value="{{ $value }}"
                                                    checked
                                                    required
                                            >
                                            <label for="{{ $idMerge }}" class="radio"><span class="radio"></span><span>{{ $value }}</span></label>
                                        </td>
                                        <td>
                                            <input
                                                    type="radio"
                                                    name="merge[{{ $groupKey }}][{{ $key }}][{{ $lang }}]"
                                                    id="{{ $idCurrent }}"
                                                    value="{{ $currentValue }}"
                                                    required
                                            >
                                            <label for="{{ $idCurrent }}" class="radio"><span class="radio"></span><span>{{ $currentValue }}</span></label>
                                        </td>
                                    </tr>
                                @else
                                    <input
                                            type="hidden"
                                            name="merge[{{ $groupKey }}][{{ $key }}][{{ $lang }}]"
                                            value="{{ $value }}"
                                    >
                                @endif
                            @endforeach
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            @else
                <p>{{ trans('column.nodata') }}</p>
            @endif

        </div>
        <div class="buttons">
            <button type="submit" class="button primary has-icon">
                <span class="material-symbols-outlined icon">merge</span>
                <span>{{ __('button.merge') }}</span>
            </button>
            <a href="{{ route('admin.fragment.group', [$fragment->group]) }}">{{ __('button.cancel') }}</a>
        </div>
    </form>

@endsection
