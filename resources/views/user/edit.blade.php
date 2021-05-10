<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>
<div class="py-12">
        <div class="sm:max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf

            <input type="hidden" name="_method" value="PUT">

            @include('auth.formfields')

            <div class="flex items-center justify-between mt-4">
                <a class="bg-blue-400 uppercase hover:bg-blue-500 text-white font-bold py-2 px-4 rounded focus:outline-none text-center text-xs" href="{{ route('users.index') }}">{{ __('Back') }}</a>

                <x-button class="bg-green-400 hover:bg-green-500 text-white font-bold py-1 px-3 rounded focus:outline-none ring-opacity-0 focus:border-opacity-0">
                    {{ __('Save') }}
                </x-button>
            </div>
        </form>
              </div>
            </div>
        </div>
    </div>
</x-app-layout>
