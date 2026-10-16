@props(['name', 'context' => []])

<v-extension-slot name="{{ $name }}" :context='@json($context)'></v-extension-slot>

@pushOnce('scripts')
<script type="text/x-template" id="v-extension-slot-template">
    <div>
        <template v-for="(component, index) in components" :key="index">
            <component :is="component" v-bind="context" />
        </template>
    </div>
</script>
<script type="module">
    import { getExtensionComponents } from '/Modules/Core/Resources/js/lib/extensionSlots.ts';

    const extensionSlotComponent = {
        template: '#v-extension-slot-template',
        props: {
            name: { type: String, required: true },
            context: { type: Object, default: () => ({}) }
        },
        computed: {
            components() {
                return getExtensionComponents(this.name).value;
            }
        }
    };
    app.component('v-extension-slot', extensionSlotComponent);
    app.component('extension-slot', extensionSlotComponent);
</script>
@endpushOnce
