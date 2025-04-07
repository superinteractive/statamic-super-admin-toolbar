@php
    $jsPath = collect(glob(base_path('vendor/superinteractive/statamic-super-admin-toolbar/dist/build/assets/load-toolbar-*.js')))
        ->first();
@endphp

@if ($jsPath && file_exists($jsPath))
    <script>{!! file_get_contents($jsPath) !!}</script>
@endif
