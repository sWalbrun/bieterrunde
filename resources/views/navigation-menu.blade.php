<?php
/**
 * @var BidderRound $round
 */

use App\Models\BidderRound;

?>
<div x-show="sidebarIsOpened" class="fixed inset-0 bg-gray-600 bg-opacity-75" aria-hidden="true"></div>

<div x-show="sidebarIsOpened" class="relative flex-1 flex flex-col max-w-xs w-full pt-5 pb-4 bg-green-400">
    <div class="absolute top-0 right-0 -mr-12 pt-2">
        <button @click="sidebarIsOpened = false" type="button"
                class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
            <span class="sr-only">Close sidebar</span>
            <!-- Heroicon name: outline/x -->
            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div class="flex-shrink-0 flex items-center px-4">
        <img class="h-12 w-auto"
             src="data:image/jpeg;base64,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"
             alt="Easywire logo">
    </div>
    <nav class="mt-5 flex-shrink-0 h-full divide-y divide-cyan-800 overflow-y-auto" aria-label="Sidebar">
        <div class="px-2 space-y-1">
            <!-- Current: "bg-cyan-800 text-white", Default: "text-cyan-100 hover:text-white hover:bg-cyan-600" -->
            <a href="#" class="bg-cyan-800 text-white group flex items-center px-2 py-2 text-base font-medium rounded-md"
               aria-current="page">
                <!-- Heroicon name: outline/home -->
                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Home Responsive
            </a>

            <a href="#"
               class="text-cyan-100 hover:text-white hover:bg-cyan-600 group flex items-center px-2 py-2 text-base font-medium rounded-md">
                <!-- Heroicon name: outline/clock -->
                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                History
            </a>

            <a href="#"
               class="text-cyan-100 hover:text-white hover:bg-cyan-600 group flex items-center px-2 py-2 text-base font-medium rounded-md">
                <!-- Heroicon name: outline/scale -->
                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
                Balances
            </a>

            <a href="#"
               class="text-cyan-100 hover:text-white hover:bg-cyan-600 group flex items-center px-2 py-2 text-base font-medium rounded-md">
                <!-- Heroicon name: outline/credit-card -->
                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Cards
            </a>

            <a href="#"
               class="text-cyan-100 hover:text-white hover:bg-cyan-600 group flex items-center px-2 py-2 text-base font-medium rounded-md">
                <!-- Heroicon name: outline/user-group -->
                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Recipients
            </a>

            <a href="#"
               class="text-cyan-100 hover:text-white hover:bg-cyan-600 group flex items-center px-2 py-2 text-base font-medium rounded-md">
                <!-- Heroicon name: outline/document-report -->
                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Reports
            </a>
        </div>
        <div class="mt-6 pt-6">
            <div class="px-2 space-y-1">
                <a href="#"
                   class="group flex items-center px-2 py-2 text-base font-medium rounded-md text-cyan-100 hover:text-white hover:bg-cyan-600">
                    <!-- Heroicon name: outline/cog -->
                    <svg class="mr-4 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>

                <a href="#"
                   class="group flex items-center px-2 py-2 text-base font-medium rounded-md text-cyan-100 hover:text-white hover:bg-cyan-600">
                    <!-- Heroicon name: outline/question-mark-circle -->
                    <svg class="mr-4 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Help
                </a>

                <a href="#"
                   class="group flex items-center px-2 py-2 text-base font-medium rounded-md text-cyan-100 hover:text-white hover:bg-cyan-600">
                    <!-- Heroicon name: outline/shield-check -->
                    <svg class="mr-4 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Privacy
                </a>
            </div>
        </div>
    </nav>
</div>

<div class="flex-shrink-0 w-14" aria-hidden="true">
    <!-- Dummy element to force sidebar to shrink to fit close icon -->
</div>
</div>

<!-- Static sidebar for desktop -->
<div class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0">
    <!-- Sidebar component, swap this element with another sidebar if you like -->
    <div class="flex flex-col flex-grow bg-green-400 pt-5 pb-4 overflow-y-auto">
        <div class="flex items-center flex-shrink-0 px-4">
            <img class="h-12 w-auto"
                 src="data:image/jpeg;base64,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"
                 alt="Easywire logo">
        </div>
        <nav class="mt-5 flex-1 flex flex-col divide-y divide-cyan-800 overflow-y-auto" aria-label="Sidebar">

            <div class="px-2 space-y-1">
                {{trans('Bieterrunden')}}
                <div class="px-2 space-y-1">
                    @foreach(App\Models\BidderRound::orderedRounds() as $round)
                        <a href="/bidderRounds/{{$round->id}}"
                           class="bg-cyan-800 text-white group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md"
                           aria-current="page">
                            <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200"
                                 xmlns="http://www.w3.org/2000/svg"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ $round->__toString()}}
                            @endforeach
                        </a>

                        @can('createBidderRound')
                            <a href="/bidderRounds/create"
                               class="bg-cyan-800 text-white group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md"
                               aria-current="page">
                                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ trans('Neue Bieterrunde anlegen') }}
                            </a>
                        @endcan
                </div>
            </div>
            <div class="px-2 space-y-1">
                {{trans('Deine Gebote')}}
                <div class="px-2 space-y-1">
                    @foreach(App\Models\BidderRound::orderedRounds() as $round)
                        <a href="/bidderRounds/{{$round->id}}/offers"
                           class="bg-cyan-800 text-white group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md"
                           aria-current="page">
                            <!-- Heroicon name: outline/home -->
                            <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 15.536c-1.171 1.952-3.07 1.952-4.242 0-1.172-1.953-1.172-5.119 0-7.072 1.171-1.952 3.07-1.952 4.242 0M8 10.5h4m-4 3h4m9-1.5a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{$round->__toString() . ($round->allOffersGivenFor(auth()->user()) ? '' : trans(' (Ausstehend)'))}}
                            @endforeach
                        </a>
                </div>
            </div>
            <div class="mt-6 pt-6">
                <div class="px-2 space-y-1">
                    <a href="#"
                       class="group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md text-cyan-100 hover:text-white hover:bg-cyan-600">
                        <!-- Heroicon name: outline/cog -->
                        <svg class="mr-4 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ trans('Einstellungen') }}
                    </a>

                    <a href="#"
                       class="group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md text-cyan-100 hover:text-white hover:bg-cyan-600">
                        <!-- Heroicon name: outline/question-mark-circle -->
                        <svg class="mr-4 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ trans('Hilfe') }}
                    </a>

                    <a href="#"
                       class="group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md text-cyan-100 hover:text-white hover:bg-cyan-600">
                        <!-- Heroicon name: outline/shield-check -->
                        <svg class="mr-4 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ trans('Privat') }}
                    </a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="lg:pl-64 flex flex-col flex-1">
    <div class="relative z-10 flex-shrink-0 flex h-16 bg-white border-b border-gray-200 lg:border-none">
        <button type="button"
                class="px-4 border-r border-gray-200 text-gray-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-cyan-500 lg:hidden"
                @click="sidebarIsOpened = true">
            <span class="sr-only">Open sidebar</span>
            <!-- Heroicon name: outline/menu-alt-1 -->
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
            </svg>
        </button>
        <!-- Search bar -->
        <div class="flex-1 px-4 flex justify-between sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
            <div class="flex-1 flex">
                <form class="w-full flex md:ml-0" action="#" method="GET">
                    <label for="search-field" class="sr-only">Search</label>
                    <div class="relative w-full text-gray-400 focus-within:text-gray-600">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none" aria-hidden="true">
                            <!-- Heroicon name: solid/search -->
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                 aria-hidden="true">
                                <path fill-rule="evenodd"
                                      d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <input id="search-field" name="search-field"
                               class="block w-full h-full pl-8 pr-3 py-2 border-transparent text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-0 focus:border-transparent sm:text-sm"
                               placeholder="Suche" type="search">
                    </div>
                </form>
            </div>
            <div class="ml-4 flex items-center md:ml-6">
                <button type="button"
                        class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                    <span class="sr-only">View notifications</span>
                    <!-- Heroicon name: outline/bell -->
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </button>

                <!-- Profile dropdown -->
                <div x-data="{menuIsShown:false}" class="ml-3 relative">
                    <div>
                        <button type="button" @click="menuIsShown = !menuIsShown"
                                class="max-w-xs bg-white rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 lg:p-2 lg:rounded-md lg:hover:bg-gray-50"
                                id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                            <img class="h-8 w-8 rounded-full" src="https://picsum.photos/200/300" alt="">
                            <span class="hidden ml-3 text-gray-700 text-sm font-medium lg:block"><span
                                    class="sr-only">Open user menu for </span>{{ auth()->user()->name }}</span>
                            <!-- Heroicon name: solid/chevron-down -->
                            <svg class="hidden flex-shrink-0 ml-1 h-5 w-5 text-gray-400 lg:block" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <div x-show="menuIsShown"
                         class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                         role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                        <!-- Active: "bg-gray-100", Not Active: "" -->
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-0">Your
                            Profile</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-1">Settings</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1"
                           id="user-menu-item-2">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
