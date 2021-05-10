@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-white bg-green-500 rounded px-3 py-3']) }}>
        {{ $status }}
    </div>
@endif
