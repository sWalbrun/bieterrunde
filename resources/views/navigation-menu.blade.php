<?php
/**
 * @var BidderRound $round
 */

use App\Models\BidderRound;

?>
<div x-show="sidebarIsOpened" class="fixed inset-0 bg-gray-600 bg-opacity-75" aria-hidden="true"></div>

<div x-show="sidebarIsOpened" class="relative flex-1 flex flex-col max-w-xs w-full pt-5 pb-4 bg-solawi_green">
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

    <!-- Sidebar component, swap this element with another sidebar if you like -->
    <div class="flex flex-col flex-grow bg-green-400 pt-5 pb-4 overflow-y-auto">
        <a href="{{route('dashboard')}}">
            <div class="flex items-center flex-shrink-0 px-4">
                <x-application-logo/>
            </div>
        </a>
        <nav class="mt-5 flex-1 flex flex-col divide-y divide-cyan-800 overflow-y-auto" aria-label="Sidebar">

            <div class="px-2 space-y-1">
                @can('createBidderRound')
                    <span class="my-5 font-bold">{{trans('Bieterrunden')}}</span>
                    <div class="px-2 space-y-1">
                        @foreach(App\Models\BidderRound::orderedRounds() as $round)
                            <a href="/bidderRounds/{{$round->id}}"
                               class="text-stone-600 group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md"
                               aria-current="page">
                                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $round->__toString()}}
                                @endforeach
                            </a>
                            <a href="/bidderRounds/create"
                               class="text-stone-600 group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md"
                               aria-current="page">
                                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ trans('Neue Bieterrunde anlegen') }}
                            </a>
                    </div>
            </div>
            @endcan
            <div class="px-2 space-y-1 pt-3">
                <span class="py-5 font-bold">{{trans('Deine Gebote')}}</span>
                <div class="px-2 space-y-1">
                    @foreach(App\Models\BidderRound::orderedRounds() as $round)
                        <a href="/bidderRounds/{{$round->id}}/offers"
                           class="text-stone-600 group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md"
                           aria-current="page">
                            <!-- Heroicon name: outline/home -->
                            <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14.121 15.536c-1.171 1.952-3.07 1.952-4.242 0-1.172-1.953-1.172-5.119 0-7.072 1.171-1.952 3.07-1.952 4.242 0M8 10.5h4m-4 3h4m9-1.5a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{$round->__toString() . ($round->allOffersGivenFor(auth()->user()) ? '' : trans(' (Ausstehend)'))}}
                            @endforeach
                        </a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="flex-shrink-0 w-14" aria-hidden="true">
    <!-- Dummy element to force sidebar to shrink to fit close icon -->
</div>
</div>

<!-- Static sidebar for desktop -->
<div class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0">
    <!-- Sidebar component, swap this element with another sidebar if you like -->
    <div class="flex flex-col flex-grow bg-solawi_green rounded pt-5 pb-4 overflow-y-auto">
        <a href="{{route('dashboard')}}">
            <div class="flex items-center flex-shrink-0 px-4">
                <x-application-logo/>
            </div>
        </a>
        <nav class="mt-5 flex-1 flex flex-col divide-y divide-cyan-800 overflow-y-auto" aria-label="Sidebar">

            <div class="px-2 space-y-1">
                @can('createBidderRound')
                    <span class="my-5 font-bold">{{trans('Bieterrunden')}}</span>
                    <div class="px-2 space-y-1">
                        @foreach(App\Models\BidderRound::orderedRounds() as $round)
                            <a href="/bidderRounds/{{$round->id}}"
                               class="text-stone-600 group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md"
                               aria-current="page">
                                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $round->__toString()}}
                                @endforeach
                            </a>
                            <a href="/bidderRounds/create"
                               class="text-stone-600 white group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md"
                               aria-current="page">
                                <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ trans('Neue Bieterrunde anlegen') }}
                            </a>
                    </div>
            </div>
            @endcan
            <div class="px-2 space-y-1 pt-3">
                <span class="py-5 font-bold">{{trans('Deine Gebote')}}</span>
                <div class="px-2 space-y-1">
                    @foreach(App\Models\BidderRound::orderedRounds() as $round)
                        <a href="/bidderRounds/{{$round->id}}/offers"
                           class="text-stone-600 group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md"
                           aria-current="page">
                            <!-- Heroicon name: outline/home -->
                            <svg class="mr-4 flex-shrink-0 h-6 w-6 text-cyan-200" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14.121 15.536c-1.171 1.952-3.07 1.952-4.242 0-1.172-1.953-1.172-5.119 0-7.072 1.171-1.952 3.07-1.952 4.242 0M8 10.5h4m-4 3h4m9-1.5a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{$round->__toString() . ($round->allOffersGivenFor(auth()->user()) ? '' : trans(' (Ausstehend)'))}}
                            @endforeach
                        </a>
                </div>
            </div>
            <div class="mt-6 pt-6">
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
                        <a href="{{route('profile.show')}}"
                           class="block px-4 py-2 text-sm text-gray-700"
                           role="menuitem"
                           tabindex="-1"
                           id="user-menu-item-0">{{__('Dein Profil')}}</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-jet-dropdown-link href="{{ route('logout') }}"
                                                 onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-jet-dropdown-link>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
