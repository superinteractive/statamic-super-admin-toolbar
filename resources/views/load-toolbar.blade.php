@cascade([
    "site",
    "page" => null,
    "live_preview" => false,
])

@php
    $loaderJs = (new \SuperInteractive\SuperAdminToolbar\Services\ManifestService())->getAssetContent("resources/js/load-toolbar.js");

    $pageJsonData = \SuperInteractive\SuperAdminToolbar\Services\ToolbarContextService::getPageJsonData($site, $page);
@endphp

@if ($loaderJs)
    <script>
        {!! $loaderJs !!}
    </script>
@endif

@if ($pageJsonData)
    <script id="super-admin-toolbar-context-json" type="application/json">
        {!! $pageJsonData !!}
    </script>
@endif
