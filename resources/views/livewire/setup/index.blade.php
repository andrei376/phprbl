<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Setup') }}
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
                                <th class="w-5/12 border">{{ __('Name') }}</th>
                                <th class="w-2/12 border">{{ __('currentsn') }}</th>
                                <th class="w-2/12 border">{{ __('lastsn') }}</th>
                                <th class="w-2/12 border">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($lists as $list)
                            <tr>
                                <td class="border p-1">{{ $list->id }}</td>
                                <td class="border p-1" title="">
                                    <span class="">
                                    {{ $list->name }}
                                    </span>
                                </td>
                                <td class="border p-1">{{ $list->currentsn }}</td>
                                <td class="border p-1">{{ $list->lastsn }}</td>
                                <td class="border p-1">
                                    <div class="inline-flex w-full">
                                        <a class="bg-blue-400 hover:bg-blue-500 text-white font-bold py-1 px-3 rounded-l w-1/2 focus:outline-none text-center" href="{{ route('setup.edit', $list->id) }}">{{ __('Edit') }}</a>

                                        <button wire:click="$emit('listdeleteTriggered', {{ $list->id }})" type="button" class="bg-red-400 hover:bg-red-500 text-white font-bold py-1 px-3 w-1/2 focus:outline-none rounded-r">
                                        {{ __('Delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="flex items-center justify-end mt-4">
                        <a class="bg-blue-700 uppercase hover:bg-blue-800 text-white font-bold py-2 px-4 rounded focus:outline-none text-center text-xs" href="{{ route('setup.create', 'create') }}">{{ __('New list') }}</a>
                    </div>

                </div>
            </div>
        </div>
    </div>


    @push('livewirescripts')
    <script type="text/javascript">

    Livewire.on("listdeleteTriggered", (id) => {
        const proceed = confirm(`Are you sure you want to delete this list?`);

        if (proceed) {
            Livewire.emit("listdelete", id);
        }
    });

    </script>
    @endpush
</div>