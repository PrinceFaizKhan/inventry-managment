@props([
    'name' => null,
    'value' => false,
    'label' => '',
    'description' => '',
    'disabled' => false,
    'size' => 'md',
    'id' => null,
])

<v-toggle
    @if($name) name="{{ $name }}" @endif
    @if($id) id="{{ $id }}" @endif
    :model-value='@json((bool) $value)'
    label="{{ $label }}"
    description="{{ $description }}"
    size="{{ $size }}"
    :disabled="{{ $disabled ? 'true' : 'false' }}"
    {{ $attributes }}
></v-toggle>
