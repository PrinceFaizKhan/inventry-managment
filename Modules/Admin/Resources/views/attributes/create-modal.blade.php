@props([
    'types' => [],
    'initialOpen' => false,
])

<attribute-create-modal
    :types='@json($types)'
    :initial-open="{{ $initialOpen ? 'true' : 'false' }}"
    ref="createAttributeModal"
></attribute-create-modal>

@pushOnce('scripts')
<script type="text/x-template" id="v-attribute-create-modal-template">
    <teleport to="body">
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                @click.self="close"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-attribute-create-title"
            >
                <div
                    class="relative w-full max-w-lg rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-2xl overflow-hidden transition-all duration-200 transform scale-100 animate-in fade-in zoom-in-95"
                    @click.stop
                >
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-[var(--border)] flex items-center justify-between bg-[var(--page)]/50">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[var(--soft)] text-[var(--teal)] border border-[var(--teal)]/20 flex items-center justify-center flex-shrink-0 shadow-xs">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/>
                                </svg>
                            </div>
                            <div>
                                <h2 id="modal-attribute-create-title" class="text-sm font-bold text-[var(--text)]">Create Attribute</h2>
                                <p class="text-[11px] text-[var(--muted)]">Choose a name and type to create and configure</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="close"
                            class="p-1.5 rounded-lg text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--border)]/40 transition-colors cursor-pointer text-base leading-none"
                            title="Close (Esc)"
                            aria-label="Close"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form @submit.prevent="saveAttribute" class="p-6 space-y-4">
                        <!-- Global Error Alert -->
                        <div v-if="globalError" class="p-3 bg-red-500/10 border border-red-500/30 text-red-500 text-xs rounded-xl font-medium flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span>@{{ globalError }}</span>
                        </div>

                        <!-- 1. Name Input -->
                        <div>
                            <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                                Attribute Name <span class="text-[var(--teal)]">*</span>
                            </label>
                            <input
                                ref="nameInput"
                                type="text"
                                v-model="name"
                                @input="handleNameInput"
                                placeholder="e.g. Screen Size, Storage, Color"
                                required
                                maxlength="120"
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                                :class="errors.name ? 'border-red-500' : ''"
                            />
                            <p v-if="errors.name" class="mt-1 text-[11px] text-red-500 font-medium">@{{ errors.name }}</p>
                        </div>

                        <!-- 2. Code (Authoritative Auto-Generated Preview) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-[var(--text)]">
                                    System Code
                                </label>
                                <span class="text-[10px] font-mono text-[var(--muted)] flex items-center gap-1">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    Auto-generated
                                </span>
                            </div>
                            <input
                                type="text"
                                :value="code"
                                readonly
                                placeholder="e.g. screen_size"
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--card)]/40 text-xs font-mono text-[var(--muted)] cursor-not-allowed select-all"
                                :class="errors.code ? 'border-red-500' : ''"
                            />
                            <p v-if="errors.code" class="mt-1 text-[11px] text-red-500 font-medium">@{{ errors.code }}</p>
                            <p class="mt-1 text-[10px] text-[var(--muted)]">Unique system key used in APIs and templates. Authoritatively generated by system.</p>
                        </div>

                        <!-- 3. Type Select -->
                        <div>
                            <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                                Type <span class="text-[var(--teal)]">*</span>
                            </label>
                            <select
                                v-model="type"
                                required
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)] transition-all cursor-pointer"
                                :class="errors.type ? 'border-red-500' : ''"
                            >
                                <option v-for="(label, key) in formattedTypes" :key="key" :value="key">
                                    @{{ label }}
                                </option>
                            </select>
                            <p v-if="errors.type" class="mt-1 text-[11px] text-red-500 font-medium">@{{ errors.type }}</p>
                        </div>

                        <!-- Modal Actions Footer -->
                        <div class="pt-3 border-t border-[var(--border)] flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                @click="close"
                                class="button button--outline text-xs px-4"
                                :disabled="saving"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="saving || !name.trim()"
                                class="button button--primary text-xs px-4 inline-flex items-center gap-1.5 shadow-sm"
                            >
                                <svg v-if="!saving" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <svg v-else class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                                <span>@{{ saving ? 'Creating & Redirecting...' : 'Save & Configure' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </teleport>
</script>

<script type="module">
    const AttributeCreateModalComponent = {
        template: '#v-attribute-create-modal-template',
        props: {
            types: {
                type: [Object, Array],
                default: () => ({})
            },
            initialOpen: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                isOpen: Boolean(this.initialOpen),
                name: '',
                code: '',
                type: 'text',
                saving: false,
                errors: {},
                globalError: null,
                localTypes: {}
            };
        },
        computed: {
            formattedTypes() {
                if (this.localTypes && Object.keys(this.localTypes).length > 0) {
                    return this.localTypes;
                }
                if (this.types && typeof this.types === 'object') {
                    if (Array.isArray(this.types)) {
                        const map = {};
                        this.types.forEach(t => {
                            if (typeof t === 'object' && t.key) {
                                map[t.key] = t.label || t.key;
                            } else {
                                map[t] = t;
                            }
                        });
                        return map;
                    }
                    return this.types;
                }
                return {
                    text: 'Text',
                    textarea: 'Textarea',
                    price: 'Price',
                    boolean: 'Boolean',
                    select: 'Select',
                    multiselect: 'Multiselect',
                    checkbox: 'Checkbox'
                };
            }
        },
        mounted() {
            window.addEventListener('ht-open-create-attribute', this.open);
            window.addEventListener('keydown', this.handleKeydown);

            // Fetch types asynchronously if none passed via props
            if (!this.types || Object.keys(this.types).length === 0) {
                this.fetchTypes();
            }

            if (this.isOpen) {
                this.$nextTick(() => {
                    this.$refs.nameInput?.focus();
                });
            }
        },
        beforeUnmount() {
            window.removeEventListener('ht-open-create-attribute', this.open);
            window.removeEventListener('keydown', this.handleKeydown);
        },
        methods: {
            async fetchTypes() {
                try {
                    const res = await this.$axios.get('/api/catalog/attribute-types');
                    if (res?.data) {
                        this.localTypes = res.data;
                    }
                } catch (e) {
                    // Fallback to default types
                }
            },
            open() {
                this.isOpen = true;
                this.name = '';
                this.code = '';
                this.type = 'text';
                this.errors = {};
                this.globalError = null;
                this.$nextTick(() => {
                    this.$refs.nameInput?.focus();
                });
            },
            close() {
                this.isOpen = false;
                this.errors = {};
                this.globalError = null;
            },
            handleKeydown(e) {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            },
            handleNameInput() {
                this.errors = {};
                this.globalError = null;
                // Live preview of authoritative generated code matching backend AttributeCodeGenerator
                const raw = (this.name || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
                this.code = raw;
            },
            async saveAttribute() {
                if (this.saving) return;

                const trimmedName = (this.name || '').trim();
                if (!trimmedName) {
                    this.errors = { name: 'Attribute name is required.' };
                    return;
                }

                if (!this.code) {
                    this.errors = { code: 'This name does not produce a valid attribute code.' };
                    return;
                }

                this.saving = true;
                this.errors = {};
                this.globalError = null;

                try {
                    const payload = {
                        name: trimmedName,
                        type: this.type
                    };

                    const res = await this.$axios.post('/api/catalog/attributes', payload);
                    const newId = res?.data?.id;

                    // Redirect immediately to the edit view of the newly created attribute
                    if (newId) {
                        window.location.href = `/catalog/attributes/${newId}/edit`;
                    } else {
                        window.location.href = '/catalog/attributes';
                    }
                } catch (caught) {
                    this.globalError = caught.message || 'Failed to create attribute.';
                    if (caught.errors) {
                        this.errors = Object.fromEntries(
                            Object.entries(caught.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                        );
                    }
                } finally {
                    this.saving = false;
                }
            }
        }
    };

    app.component('attribute-create-modal', AttributeCreateModalComponent);
    app.component('v-attribute-create-modal', AttributeCreateModalComponent);
    app.component('v-create-attributes', AttributeCreateModalComponent);
</script>
@endpushOnce
