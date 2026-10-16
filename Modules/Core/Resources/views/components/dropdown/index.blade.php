@props(['position' => 'bottom-left'])

<v-dropdown position="{{ $position }}" {{ $attributes->merge(['class' => 'relative']) }}>
    @isset($toggle)
        <template v-slot:toggle>
            {{ $toggle }}
        </template>
    @endisset

    @isset($content)
        <template v-slot:content>
            <div {{ $content->attributes->merge(['class' => 'p-4']) }}>
                {{ $content }}
            </div>
        </template>
    @endisset

    @isset($menu)
        <template v-slot:menu>
            <ul {{ $menu->attributes->merge(['class' => 'py-2']) }}>
                {{ $menu }}
            </ul>
        </template>
    @endisset
</v-dropdown>

@pushOnce('scripts')
    <script type="text/x-template" id="v-dropdown-template">
        <div>
            <div
                class="select-none flex cursor-pointer"
                ref="toggleBlock"
                @click="toggle()"
            >
                <slot name="toggle">Toggle</slot>
            </div>

            <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    class="dropdown-menu"
                    v-show="isActive"
                >
                    <slot name="content"></slot>
                    <slot name="menu"></slot>
                    <slot></slot>
                </div>
            </transition>
        </div>
    </script>

    <script type="module">
        const dropdownComponent = {
            template: '#v-dropdown-template',
            props: {
                position: {
                    type: String,
                    default: 'bottom-left'
                },
                closeOnClick: {
                    type: Boolean,
                    default: true
                }
            },
            data() {
                return {
                    isActive: false
                };
            },
            mounted() {
                document.addEventListener('pointerdown', this.handleOutsideClick);
                document.addEventListener('keydown', this.handleKeydown);
            },
            beforeUnmount() {
                document.removeEventListener('pointerdown', this.handleOutsideClick);
                document.removeEventListener('keydown', this.handleKeydown);
            },
            methods: {
                toggle() {
                    this.isActive = !this.isActive;
                },
                close() {
                    this.isActive = false;
                },
                handleOutsideClick(event) {
                    if (this.$el && !this.$el.contains(event.target)) {
                        this.close();
                    }
                },
                handleKeydown(event) {
                    if (event.key === 'Escape') {
                        this.close();
                    }
                }
            }
        };

        app.component('v-dropdown', dropdownComponent);
        app.component('app-dropdown', dropdownComponent);
    </script>
@endpushOnce
