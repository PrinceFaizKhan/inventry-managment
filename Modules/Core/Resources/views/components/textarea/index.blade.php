@props([
    'name' => null,
    'value' => '',
    'label' => '',
    'placeholder' => '',
    'rows' => 3,
    'maxLength' => null,
    'autoResize' => true,
    'disabled' => false,
    'readonly' => false,
    'id' => null,
    'error' => '',
    'help' => '',
])

<v-textarea
    @if($name) name="{{ $name }}" @endif
    @if($id) id="{{ $id }}" @endif
    :model-value='@json($value)'
    label="{{ $label }}"
    placeholder="{{ $placeholder }}"
    :rows="{{ $rows }}"
    @if($maxLength) :max-length="{{ $maxLength }}" @endif
    :auto-resize="{{ $autoResize ? 'true' : 'false' }}"
    :disabled="{{ $disabled ? 'true' : 'false' }}"
    :readonly="{{ $readonly ? 'true' : 'false' }}"
    error="{{ $error }}"
    help="{{ $help }}"
    {{ $attributes }}
></v-textarea>
