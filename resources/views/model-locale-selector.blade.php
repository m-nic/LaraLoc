@foreach(config('app.available_locale') as $lang)
    <a class="btn btn-sm {{ getActiveModelCssClass($lang, 'btn-success', 'btn-default') }}"
       href="{{ getCurrentRouteForModelLocale($lang) }}">
        {{ ucfirst($lang) }}
    </a>
@endforeach
