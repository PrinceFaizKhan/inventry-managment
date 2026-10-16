@props([
    'variant' => 'primary',
    'type' => 'button',
])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'button button--' . $variant]) }}>
    {{ $slot }}
</button>

@pushOnce('scripts')
<script type="text/x-template" id="v-button-template">
    <button :type="type" class="button" :class="`button--${variant}`"><slot/></button>
</script>
<script type="module">
    const buttonComponent = {
        template: '#v-button-template',
        props: {
            variant: { type: String, default: 'primary' },
            type: { type: String, default: 'button' }
        }
    };
    app.component('v-button', buttonComponent);
    app.component('base-button', buttonComponent);
</script>
@endpushOnce
