@php
    $loaderJs = (new \SuperInteractive\SuperAdminToolbar\Services\ManifestService())
                    ->getAssetContent('resources/js/load-toolbar.js');
@endphp

@if ($loaderJs)
    <script>{!! $loaderJs !!}</script>
@endif
