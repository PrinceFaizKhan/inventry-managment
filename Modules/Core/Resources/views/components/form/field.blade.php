@props([
    'label' => '',
    'type' => 'text',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
])

<label {{ $attributes->merge(['class' => 'field']) }}>
    @if($label)
        <span>{{ $label }} @if($required)<b class="text-[var(--teal)]">*</b>@endif</span>
    @endif
    @if($slot->isNotEmpty())
        {{ $slot }}
    @else
        <input
            type="{{ $type }}"
            @if($name) name="{{ $name }}" id="{{ $name }}" @endif
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
        />
    @endif
</label>

@pushOnce('scripts')
<script type="text/x-template" id="v-form-field-template">
    <label class="field">
        <span v-if="label">@{{ label }}</span>
        <input :type="type" :value="modelValue" :placeholder="placeholder" @input="$emit('update:modelValue', $event.target.value)" />
        <slot />
    </label>
</script>
<script type="module">
    const formFieldComponent = {
        template: '#v-form-field-template',
        props: {
            label: String,
            modelValue: String,
            type: { type: String, default: 'text' },
            placeholder: String
        },
        emits: ['update:modelValue']
    };
    app.component('v-form-field', formFieldComponent);
    app.component('form-field', formFieldComponent);
</script>
@endpushOnce
