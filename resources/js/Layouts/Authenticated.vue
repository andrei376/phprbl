<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100 nav nav-pills">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex-shrink-0 flex items-center">
                                <inertia-link :href="this.route('stats.index')">
                                    <breeze-application-logo class="block h-9 w-auto" />
                                </inertia-link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <breeze-nav-link :href="this.route('stats.index')" class="text-decoration-none" :active="this.route().current('stats.index')">
                                    {{ this.__('STATS') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('top.index')" class="text-decoration-none" :active="this.route().current('top.index')">
                                    {{ this.__('TOP') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('rbl.browse')" class="text-decoration-none" :active="this.route().current('rbl.browse')">
                                    {{ this.__('BROWSE') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('rbl.lookup')" class="text-decoration-none" :active="this.route().current('rbl.lookup')">
                                    {{ this.__('LOOKUP') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('rbl.new4')" class="text-decoration-none" :active="this.route().current('rbl.new4')">
                                    {{ this.__('NEW V4') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('rbl.new6')" class="text-decoration-none" :active="this.route().current('rbl.new6')">
                                    {{ this.__('NEW V6') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('rbl.logs')" class="text-decoration-none" :active="this.route().current('rbl.logs')">
                                    {{ this.__('LOG') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('rbl.whois')" class="text-decoration-none" :active="this.route().current('rbl.whois')">
                                    {{ this.__('WHOIS') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('rbl.as')" class="text-decoration-none" :active="this.route().current('rbl.as')">
                                    {{ this.__('AS IP') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('rbl.findas')" class="text-decoration-none" :active="this.route().current('rbl.findas')">
                                    {{ this.__('Find AS') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('setup.index')" class="text-decoration-none" :active="this.route().current('setup.index')">
                                    {{ this.__('SETUP') }}
                                </breeze-nav-link>

                                <breeze-nav-link :href="this.route('users.index')" class="text-decoration-none" :active="this.route().current('users.index')">
                                    {{ this.__('USERS') }}
                                </breeze-nav-link>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <breeze-dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <breeze-dropdown-link :href="this.route('logout')" method="post" as="button">
                                            Log Out
                                        </breeze-dropdown-link>
                                    </template>
                                </breeze-dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = ! showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <breeze-responsive-nav-link :href="this.route('stats.index')" class="text-decoration-none" :active="this.route().current('stats.index')">
                            {{ this.__('STATS') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('top.index')" class="text-decoration-none" :active="this.route().current('top.index')">
                            {{ this.__('TOP') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('rbl.browse')" class="text-decoration-none" :active="this.route().current('rbl.browse')">
                            {{ this.__('BROWSE') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('rbl.lookup')" class="text-decoration-none" :active="this.route().current('rbl.lookup')">
                            {{ this.__('LOOKUP') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('rbl.new4')" class="text-decoration-none" :active="this.route().current('rbl.new4')">
                            {{ this.__('NEW V4') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('rbl.new6')" class="text-decoration-none" :active="this.route().current('rbl.new6')">
                            {{ this.__('NEW V6') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('rbl.logs')" class="text-decoration-none" :active="this.route().current('rbl.logs')">
                            {{ this.__('LOG') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('rbl.whois')" class="text-decoration-none" :active="this.route().current('rbl.whois')">
                            {{ this.__('WHOIS') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('rbl.as')" class="text-decoration-none" :active="this.route().current('rbl.as')">
                            {{ this.__('AS IP') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('rbl.findas')" class="text-decoration-none" :active="this.route().current('rbl.findas')">
                            {{ this.__('Find AS') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('setup.index')" class="text-decoration-none" :active="this.route().current('setup.index')">
                            {{ this.__('SETUP') }}
                        </breeze-responsive-nav-link>

                        <breeze-responsive-nav-link :href="this.route('users.index')" class="text-decoration-none" :active="this.route().current('users.index')">
                            {{ this.__('USERS') }}
                        </breeze-responsive-nav-link>

                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div class="flex-shrink-0">
                                <svg class="h-10 w-10 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>

                            <div class="ml-3">
                                <div class="font-medium text-base text-gray-800">{{ $page.props.auth.user.name }}</div>
                                <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <breeze-responsive-nav-link :href="this.route('logout')" method="post" as="button">
                                Log Out
                            </breeze-responsive-nav-link>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="container-fluid">
                <div class="col-sm-12 col-xl-10 inline-block container-fluid">
                    <slot />
                </div>

                <div class="col-sm-12 col-xl-2 inline-block align-top pt-2 pr-1 container-fluid">
                    <rbl-stats />
                </div>
            </main>
        </div>
    </div>
</template>

<script>
    import BreezeApplicationLogo from '@/Components/ApplicationLogo'
    import BreezeDropdown from '@/Components/Dropdown'
    import BreezeDropdownLink from '@/Components/DropdownLink'
    import BreezeNavLink from '@/Components/NavLink'
    import BreezeResponsiveNavLink from '@/Components/ResponsiveNavLink'
    import RblStats from '@/Components/RblStats'

    export default {
        components: {
            BreezeApplicationLogo,
            BreezeDropdown,
            BreezeDropdownLink,
            BreezeNavLink,
            BreezeResponsiveNavLink,
            RblStats
        },

        data() {
            return {
                showingNavigationDropdown: false,
            }
        }
    }
</script>
