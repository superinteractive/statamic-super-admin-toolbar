<div id="super-admin-toolbar" class="super-admin-toolbar si-group-toggleable"
     @if($toolbarOpened) si-group-toggled="true" @endif>
    <div class="fixed bottom-4 pr-4 left-4 !font-sans z-50">
        <div class="flex gap-2 p-2 rounded cursor-pointer flex-col md:duration-500 md:transition-all md:items-center md:flex-row min-w-10 max-h-10 max-md:si-group-toggled:max-h-96 si-group-toggled:max-w-full bg-[#F1F1F1] shadow-super-toolbar">

            {{-- Toggle Button --}}
            <div class="order-2 md:order-1">
                <button type="button" id="super-admin-toolbar-toggle"
                        class="w-6 h-6 shrink-0 shadow-toolbar-icon bg-toolbar-gradient text-[#FFFFFF] rounded-sm inline-flex items-center justify-center"
                        aria-label="{{ __('Toggle Admin Toolbar') }}">
                    <x-sat-icon src="chevron-right"
                                class="h-3 w-3 fill-[#FFFFFF] -rotate-90 si-group-toggled:rotate-90 md:rotate-0 si-group-toggled:md:-rotate-180"/>
                </button>
            </div>

            <div class="order-1 md:order-2 gap-2 hidden si-group-toggled:flex md:flex-row flex-col flex-wrap md:flex-nowrap items-center">

                {{-- Dashboard Link --}}
                <a href="{{ cp_route('dashboard') }}" title="{{ __('Go to Control Panel Dashboard') }}"
                   class="py-1 px-1.5 inline-flex items-center shrink-0 hover:bg-[#E0E0E0] text-[#222222] rounded text-sm gap-1.5 w-full md:w-auto justify-start">
                    <x-sat-icon src="dashboard" class="h-2.5 w-2.5"/>
                    <span class="shrink-0">{{ __('Dashboard') }}</span>
                </a>

                {{-- Edit Link --}}
                @if($editUrl)
                    <a href="{{ $editUrl }}" title="{{ __('Edit this item') }}"
                       class="py-1 px-1.5 inline-flex items-center shrink-0 hover:bg-[#E0E0E0] text-[#222222] rounded text-sm gap-1.5 w-full md:w-auto justify-start">
                        <x-sat-icon src="pencil" class="h-2.5 w-2.5"/>
                        <span class="shrink-0">{{ __('Edit :item', ['item' => $itemSingularName]) }}</span>
                    </a>
                @endif

                {{-- SEO Link --}}
                @if ($seoUrl)
                    <a href="{{ $seoUrl }}" title="{{ __('Edit SEO Settings') }}"
                       class="py-1 px-1.5 inline-flex items-center shrink-0 hover:bg-[#E0E0E0] text-[#222222] rounded text-sm gap-1.5 w-full md:w-auto justify-start">
                        <x-sat-icon src="seo" class="h-2.5 w-2.5"/>
                        <span class="shrink-0">{{ __('Edit SEO') }}</span>
                    </a>
                @endif

                {{-- Create Link --}}
                @if($createUrl)
                    <a href="{{ $createUrl }}" title="{{ __('Create new entry') }}"
                       class="py-1 px-1.5 inline-flex items-center shrink-0 hover:bg-[#E0E0E0] text-[#222222] rounded text-sm gap-1.5 w-full md:w-auto justify-start">
                        <x-sat-icon src="plus" class="h-2.5 w-2.5"/>
                        <span class="shrink-0">{{ __('New :item', ['item' => $itemSingularName]) }}</span>
                    </a>
                @endif

                {{-- Multi-Site Dropdown --}}
                @if(!empty($multiSites))
                    <div class="relative si-toolbar-dropdown-container w-full">
                        <button type="button" data-dropdown-toggle="site-switcher-menu" title="{{ __('Switch Site') }}"
                                class="py-1 px-1.5 inline-flex items-center shrink-0 hover:bg-[#E0E0E0] text-[#222222] rounded text-sm gap-1.5 w-full md:w-auto justify-start">
                            <x-sat-icon src="sites" class="w-2.5 h-2.5"/>

                            <span class="shrink-0">{{ __('Sites') }}</span>

                            <x-sat-icon src="chevron-down" class="h-2.5 w-2.5 -rotate-90 md:rotate-0" data-dropdown-chevron />
                        </button>

                        {{-- Dropdown Menu --}}
                        <div id="site-switcher-menu"
                             class="absolute max-w-[12.5rem] bottom-0 md:top-auto md:bottom-full md:mb-2 left-full ml-3 md:left-auto md:-right-2 z-10 hidden bg-[#F1F1F1] rounded shadow-md min-w-[150px] py-1 max-h-60 overflow-y-auto">
                            @foreach($multiSites as $site)
                                <a href="{{ $site['url'] }}"
                                   class="block overflow-ellipsis overflow-hidden px-3 py-1.5 text-sm text-[#222222] hover:bg-[#E0E0E0] whitespace-nowrap @if($site['handle'] === $currentSiteHandle) font-semibold bg-[#E0E0E0] @endif">
                                    {{ $site['name'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
