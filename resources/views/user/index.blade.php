<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-8/12 mx-auto sm:px-6 lg:px-8">
            @if (session()->has('message'))
                <x-alert class="mb-4" :status="session('message')" />
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="table-fixed border-collapse border w-full">
                        <thead>
                            <tr>
                                <th class="w-1/12 border">{{ __('ID') }}</th>
                                <th class="w-9/12 border">{{ __('Email') }}</th>
                                <th class="w-2/12 border">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="border p-1">{{ $user->id }}</td>
                                <td class="border p-1" title="@if($user->hasVerifiedEmail()) Email verified @else Email not verified @endif">
                                    <span class="@if($user->hasVerifiedEmail()) text-green-700 @else text-red-700 @endif">
                                    {{ $user->email }}
                                    </span>
                                </td>
                                <td class="border p-1">
                                    <div class="inline-flex w-full">
                                        <a class="bg-blue-400 hover:bg-blue-500 text-white font-bold py-1 px-3 rounded-l w-1/2 focus:outline-none text-center" href="{{ route('users.edit', $user->id) }}">{{ __('Edit') }}</a>

                                        <button data-id="{{ $user->id }}" type="button" class="bg-red-400 hover:bg-red-500 text-white font-bold py-1 px-3 w-1/2 focus:outline-none rounded-r btn-delete">
                                        {{ __('Delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <br>
                </div>
            </div>
        </div>
    </div>


    @push('livewirescripts')
    <script>
    $(document).ready(function (){

        $(document).off('click', '.btn-delete').on('click', '.btn-delete', function () {
            const proceed = confirm(`Are you sure you want to delete this user?`);

            if (proceed) {
                $.ajax({
                    method: "DELETE",
                    url: '{{ route('users.destroy', '') }}/' + $(this).data('id'),
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert('Error deleting user');
                    },
                    success: function (data, textStatus, jqXHR) {
                        window.location = '{{ route('users.index') }}';
                    }
                });
            }
        });
    });

    </script>
    @endpush

</x-app-layout>
