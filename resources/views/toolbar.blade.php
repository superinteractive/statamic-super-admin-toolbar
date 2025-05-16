<div id="super-admin-toolbar" class="super-admin-toolbar si-group-toggleable" @if($toolbarOpened) si-group-toggled="true" @endif>
    <div class="fixed bottom-4 left-4 z-[2147483647] pr-4 !font-sans">
        <div class="flex max-h-10 min-w-10 cursor-pointer flex-col gap-0 rounded bg-[#F1F1F1] shadow-super-toolbar si-group-toggled:max-w-full max-md:si-group-toggled:max-h-96 md:flex-row md:items-center md:transition-all md:duration-500">
            {{-- Toggle Button --}}
            <div class="order-2 md:order-1">
                <button class="inline-flex w-full md:w-auto items-center justify-start p-2" type="button" id="super-admin-toolbar-toggle" aria-label="{{ __("Toggle Admin Toolbar") }}">
                    <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-sm bg-toolbar-gradient text-[#FFFFFF] shadow-toolbar-icon">
                        <x-sat-icon src="chevron-right" class="h-3 w-3 -rotate-90 fill-[#FFFFFF] si-group-toggled:rotate-90 md:rotate-0 si-group-toggled:md:-rotate-180" />
                    </span>
                </button>
            </div>

            <div class="order-1 hidden flex-col flex-wrap items-center gap-2 p-2 pb-0 si-group-toggled:flex md:order-2 md:flex-row md:flex-nowrap md:pb-2 md:pl-0">
                {{-- Dashboard Link --}}
                <a
                    href="{{ cp_route("dashboard") }}"
                    title="{{ __("Go to Control Panel Dashboard") }}"
                    class="inline-flex w-full shrink-0 items-center justify-start gap-1.5 rounded px-1.5 py-1 text-sm text-[#222222] hover:bg-[#E0E0E0] md:w-auto"
                >
                    <x-sat-icon src="dashboard" class="h-2.5 w-2.5" />
                    <span class="shrink-0">{{ __("Dashboard") }}</span>
                </a>

                {{-- Edit Link --}}
                @if ($editUrl)
                    <a href="{{ $editUrl }}" title="{{ __("Edit this item") }}" class="inline-flex w-full shrink-0 items-center justify-start gap-1.5 rounded px-1.5 py-1 text-sm text-[#222222] hover:bg-[#E0E0E0] md:w-auto">
                        <x-sat-icon src="pencil" class="h-2.5 w-2.5" />
                        <span class="shrink-0">{{ __("Edit :item", ["item" => $itemSingularName]) }}</span>
                    </a>
                @endif

                {{-- SEO Link --}}
                @if ($seoUrl)
                    <a href="{{ $seoUrl }}" title="{{ __("Edit SEO Settings") }}" class="inline-flex w-full shrink-0 items-center justify-start gap-1.5 rounded px-1.5 py-1 text-sm text-[#222222] hover:bg-[#E0E0E0] md:w-auto">
                        <x-sat-icon src="seo" class="h-2.5 w-2.5" />
                        <span class="shrink-0">{{ __("Edit SEO") }}</span>
                    </a>
                @endif

                {{-- Create Link --}}
                @if ($createUrl)
                    <a href="{{ $createUrl }}" title="{{ __("Create new entry") }}" class="inline-flex w-full shrink-0 items-center justify-start gap-1.5 rounded px-1.5 py-1 text-sm text-[#222222] hover:bg-[#E0E0E0] md:w-auto">
                        <x-sat-icon src="plus" class="h-2.5 w-2.5" />
                        <span class="shrink-0">{{ __("New :item", ["item" => $itemSingularName]) }}</span>
                    </a>
                @endif

                {{-- Multi-Site Dropdown --}}
                @if (! empty($multiSites))
                    <div class="si-toolbar-dropdown-container relative w-full">
                        <button
                            type="button"
                            data-dropdown-toggle="site-switcher-menu"
                            title="{{ __("Switch Site") }}"
                            class="inline-flex w-full shrink-0 items-center justify-start gap-1.5 rounded px-1.5 py-1 text-sm text-[#222222] hover:bg-[#E0E0E0] md:w-auto"
                        >
                            <x-sat-icon src="sites" class="h-2.5 w-2.5" />

                            <span class="shrink-0">{{ __("Sites") }}</span>

                            <x-sat-icon src="ellipsis" class="h-2.5 w-2.5 -rotate-90" data-dropdown-chevron />
                        </button>

                        {{-- Dropdown Menu --}}
                        <div
                            id="site-switcher-menu"
                            class="absolute bottom-0 left-full z-10 ml-3 hidden max-h-60 min-w-[150px] max-w-[12.5rem] overflow-y-auto rounded bg-[#F1F1F1] py-1 shadow-md md:-right-2 md:bottom-full md:left-auto md:top-auto md:mb-3"
                        >
                            @foreach ($multiSites as $site)
                                <a
                                    href="{{ $site["url"] }}"
                                    class="@if($site['handle'] === $currentSiteHandle) font-semibold bg-[#E0E0E0] @endif block overflow-hidden overflow-ellipsis whitespace-nowrap px-3 py-1.5 text-sm text-[#222222] hover:bg-[#E0E0E0]"
                                >
                                    {{ $site["name"] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
