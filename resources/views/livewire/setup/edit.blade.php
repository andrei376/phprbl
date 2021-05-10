<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Setup') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-6/12 mx-auto sm:px-6 lg:px-8">
            @if (session()->has('message'))
                <x-alert class="mb-4" :status="session('message')" />
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Validation Errors -->
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />

                    <form wire:submit.prevent="save" novalidate>
                        @csrf

                        <input type="hidden" name="id" wire:model.defer="list_id">

                        <!-- Name -->
                        <div class="mt-4">
                            <x-label for="name" :value="__('Name').' *'" />

                            <x-input id="name" class="block mt-1 w-full" type="text" name="name" wire:model.defer="name" required />
                        </div>

                        <!-- Email -->
                        <div class="mt-4">
                            <x-label for="email" :value="__('Email').' *'" />

                            <x-input id="email" class="block mt-1 w-full" type="text" name="email" wire:model.defer="email" placeholder="{{ __('ex: root.newsbox.ro') }}" required />
                        </div>

                        <!-- expire  -->
                        <div class="mt-4">
                            <x-label for="expire" :value="__('Expire').' *'" />

                            <x-input id="expire" class="block mt-1 w-full" type="text" name="expire" wire:model.defer="expire" placeholder="{{ __('ex: 1w') }}" required />
                        </div>

                        <!-- host  -->
                        <div class="mt-4">
                            <x-label for="host" :value="__('Host').' *'" />

                            <x-input id="host" class="block mt-1 w-full" type="text" name="host" wire:model.defer="host" placeholder="{{ __('ex: NAME.list.newsbox.ro') }}" required />
                        </div>

                        <!-- list  -->
                        <div class="mt-4">
                            <x-label for="list" :value="__('List').' *'" />

                            <x-input id="list" class="block mt-1 w-full" type="text" name="list" wire:model.defer="list" placeholder="{{ __('ex: /var/lib/rbldns/NAME/NAME.list') }}" required />
                        </div>

                        <!-- minttl  -->
                        <div class="mt-4">
                            <x-label for="minttl" :value="__('minttl').' *'" />

                            <x-input id="minttl" class="block mt-1 w-full" type="text" name="minttl" wire:model.defer="minttl" placeholder="{{ __('ex: 1m') }}" required />
                        </div>

                        <!-- nss -->
                        <div class="mt-4">
                            <x-label for="nss" :value="__('nss').' *'" />

                            <x-input id="nss" class="block mt-1 w-full" type="text" name="nss" wire:model.defer="nss" placeholder="{{ __('ex: list.newsbox.ro') }}" required />
                        </div>

                        <!-- primaryns -->
                        <div class="mt-4">
                            <x-label for="primaryns" :value="__('primaryns').' *'" />

                            <x-input id="primaryns" class="block mt-1 w-full" type="text" name="primaryns" wire:model.defer="primaryns" placeholder="{{ __('ex: list.newsbox.ro') }}" required />
                        </div>

                        <!-- refresh -->
                        <div class="mt-4">
                            <x-label for="refresh" :value="__('refresh').' *'" />

                            <x-input id="refresh" class="block mt-1 w-full" type="text" name="refresh" wire:model.defer="refresh" placeholder="{{ __('ex: 1h') }}" required />
                        </div>

                        <!-- retry -->
                        <div class="mt-4">
                            <x-label for="retry" :value="__('retry').' *'" />

                            <x-input id="retry" class="block mt-1 w-full" type="text" name="retry" wire:model.defer="retry" placeholder="{{ __('ex: 5m') }}" required />
                        </div>

                        <!-- soansttl -->
                        <div class="mt-4">
                            <x-label for="soansttl" :value="__('soansttl').' *'" />

                            <x-input id="soansttl" class="block mt-1 w-full" type="text" name="soansttl" wire:model.defer="soansttl" placeholder="{{ __('ex: 1w') }}" required />
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <a class="bg-blue-400 uppercase hover:bg-blue-500 text-white font-bold py-2 px-4 rounded focus:outline-none text-center text-xs" href="{{ route('setup.index') }}">{{ __('Back') }}</a>

                            <x-button class="bg-green-400 hover:bg-green-500 text-white font-bold py-1 px-3 rounded focus:outline-none ring-opacity-0 focus:border-opacity-0">
                                {{ __('Save') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>