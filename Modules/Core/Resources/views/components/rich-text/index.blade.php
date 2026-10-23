@props([
    'name' => null,
    'value' => '',
    'label' => '',
    'placeholder' => 'Write detailed description...',
    'disabled' => false,
    'minHeight' => '180px',
    'id' => null,
    'error' => '',
])

<v-rich-text
    @if($name) name="{{ $name }}" @endif
    @if($id) id="{{ $id }}" @endif
    :model-value='@json($value)'
    label="{{ $label }}"
    placeholder="{{ $placeholder }}"
    min-height="{{ $minHeight }}"
    :disabled="{{ $disabled ? 'true' : 'false' }}"
    error="{{ $error }}"
    {{ $attributes }}
></v-rich-text>
