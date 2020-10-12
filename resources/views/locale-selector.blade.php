@foreach(config('app.available_locale') as $lang)
    <a class="btn btn-sm {{ getActiveCssClass($lang, 'btn-primary', 'btn-default') }}"
       href="{{ getCurrentRouteForLocale($lang) }}">{{ ucfirst($lang) }}</a>
@endforeach
