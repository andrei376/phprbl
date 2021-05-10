@props(['disabled' => false, 'id'])

<input id="{{ $id }}" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 '.($errors->has($id) ? 'border-red-700':'')]) !!}>
