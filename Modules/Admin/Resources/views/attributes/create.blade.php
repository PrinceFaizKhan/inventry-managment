@extends('admin::layouts.admin')

@section('title', 'Create Attribute')
@section('crumb', 'Create Attribute')

@section('content')
<div class="attribute-create-page">
    {!! view_event_render('catalog.attributes.create.before') !!}

    <v-create-attributes :types='@json($types)'></v-create-attributes>
    <div class="hidden" aria-hidden="true"><attribute-create-modal :types='@json($types)'></attribute-create-modal></div>

    {!! view_event_render('catalog.attributes.create.after') !!}
</div>

@pushOnce('scripts')
<script type="text/x-template" id="v-create-attributes-template">
    <form @submit.prevent="saveAttribute" class="space-y-6">
        <!-- Page Header & Action Buttons -->
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-[var(--border)]">
            <div>
                <p class="eyebrow">Catalog / Attributes</p>
                <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">Create Attribute</h1>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ url('catalog/attributes') }}" class="button button--outline text-xs">
                    Back
                </a>
                <button
                    type="submit"
                    :disabled="saving"
                    class="button button--primary text-xs inline-flex items-center gap-1.5"
                >
                    <svg v-if="!saving" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>@{{ saving ? 'Saving...' : 'Save Attribute' }}</span>
                </button>
            </div>
        </div>

        <!-- Global Error Alert -->
        <div v-if="globalError" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg font-medium">
            @{{ globalError }}
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Left Main Column (2 spans) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- General Section Card -->
                <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm space-y-4">
                    <h2 class="text-base font-bold text-[var(--text)] border-b border-[var(--border)] pb-2.5">
                        General Information
                    </h2>

                    <!-- Attribute Name -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            Name <span class="text-[var(--teal)]">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="name"
                            @input="handleNameInput"
                            placeholder="e.g. Screen Size"
                            required
                            maxlength="120"
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                            :class="errors.name ? 'border-red-500' : ''"
                        />
                        <p v-if="errors.name" class="mt-1 text-xs text-red-600">@{{ errors.name }}</p>
                    </div>

                    <!-- Attribute Code (Normalized) -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            Code
                        </label>
                        <input
                            type="text"
                            :value="code"
                            readonly
                            placeholder="auto_generated_code"
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-sm font-mono text-[var(--muted)] cursor-not-allowed select-all"
                            :class="errors.code ? 'border-red-500' : ''"
                        />
                        <p v-if="errors.code" class="mt-1 text-xs text-red-600">@{{ errors.code }}</p>
                        <p class="mt-1 text-[11px] text-[var(--muted)]">Unique system identifier. Automatically generated from name.</p>
                    </div>

                    <!-- Attribute Type Select -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            Type <span class="text-[var(--teal)]">*</span>
                        </label>
                        <select
                            v-model="type"
                            required
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                            :class="errors.type ? 'border-red-500' : ''"
                        >
                            <option value="" disabled>Select attribute type</option>
                            <option v-for="(label, key) in availableTypes" :key="key" :value="key">
                                @{{ label }}
                            </option>
                        </select>
                        <p v-if="errors.type" class="mt-1 text-xs text-red-600">@{{ errors.type }}</p>
                    </div>
                </div>

                <!-- Options Management (for select, multiselect, radio, checkbox) -->
                <div v-if="supportsOptions" class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-[var(--border)] pb-2.5">
                        <div>
                            <h2 class="text-base font-bold text-[var(--text)]">Options</h2>
                            <p class="text-xs text-[var(--muted)]">Manage the selectable choices for this attribute.</p>
                        </div>
                        <button
                            type="button"
                            @click="addOption"
                            class="button button--outline text-xs h-8 px-2.5 inline-flex items-center gap-1"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Add Option</span>
                        </button>
                    </div>

                    <div v-if="options.length === 0" class="py-6 text-center text-xs text-[var(--muted)] border border-dashed border-[var(--border)] rounded-lg">
                        No options added yet. Click "Add Option" above to define values.
                    </div>

                    <div v-else class="space-y-2">
                        <div v-for="(opt, index) in options" :key="index" class="flex items-center gap-2 p-2 rounded-lg bg-[var(--page)] border border-[var(--border)]">
                            <span class="text-xs font-mono text-[var(--muted)] w-6 text-center">@{{ index + 1 }}</span>
                            <input
                                type="text"
                                v-model="opt.code"
                                placeholder="Option code"
                                class="h-8 px-2.5 rounded border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] flex-1 font-mono"
                            />
                            <input
                                type="text"
                                v-model="opt.label"
                                placeholder="Display label"
                                class="h-8 px-2.5 rounded border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] flex-1"
                            />
                            <button
                                type="button"
                                @click="removeOption(index)"
                                class="w-8 h-8 rounded text-red-500 hover:bg-red-50 hover:text-red-700 grid place-items-center"
                                title="Remove option"
                            >
                                &times;
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Column (1 span) -->
            <div class="space-y-6">
                <!-- Validations Accordion Card -->
                <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm space-y-4">
                    <h2 class="text-base font-bold text-[var(--text)] border-b border-[var(--border)] pb-2.5">
                        Validations
                    </h2>

                    <!-- Is Required Toggle -->
                    <label class="flex items-center justify-between cursor-pointer select-none">
                        <div>
                            <span class="block text-xs font-semibold text-[var(--text)]">Is Required</span>
                            <span class="text-[11px] text-[var(--muted)]">Require value before saving catalog items</span>
                        </div>
                        <input
                            type="checkbox"
                            v-model="required"
                            class="rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)] w-4 h-4"
                        />
                    </label>

                    <!-- Is Unique (only for text) -->
                    <label v-if="type === 'text'" class="flex items-center justify-between cursor-pointer select-none border-t border-[var(--border)] pt-3">
                        <div>
                            <span class="block text-xs font-semibold text-[var(--text)]">Is Unique</span>
                            <span class="text-[11px] text-[var(--muted)]">Enforce unique values across products</span>
                        </div>
                        <input
                            type="checkbox"
                            v-model="isUnique"
                            class="rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)] w-4 h-4"
                        />
                    </label>
                </div>

                <!-- Configurations Accordion Card -->
                <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm space-y-4">
                    <h2 class="text-base font-bold text-[var(--text)] border-b border-[var(--border)] pb-2.5">
                        Configuration
                    </h2>

                    <!-- Is Filterable Toggle -->
                    <label class="flex items-center justify-between cursor-pointer select-none">
                        <div>
                            <span class="block text-xs font-semibold text-[var(--text)]">Is Filterable</span>
                            <span class="text-[11px] text-[var(--muted)]">Allow filtering products by this attribute</span>
                        </div>
                        <input
                            type="checkbox"
                            v-model="isFilterable"
                            class="rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)] w-4 h-4"
                        />
                    </label>
                </div>
            </div>
        </div>
    </form>
</script>

<script type="module">
    function normalizeCode(val) {
        if (!val) return '';
        return val.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/[-\s]+/g, '_');
    }

    const CreateAttributesComponent = {
        template: '#v-create-attributes-template',
        props: {
            types: {
                type: Object,
                default: () => ({})
            }
        },
        data() {
            return {
                name: '',
                type: 'text',
                required: false,
                isUnique: false,
                isFilterable: false,
                options: [],
                saving: false,
                errors: {},
                globalError: null,
                availableTypes: this.types && Object.keys(this.types).length > 0
                    ? this.types
                    : {
                        text: 'Text',
                        textarea: 'Textarea',
                        boolean: 'Boolean',
                        checkbox: 'Checkbox',
                        select: 'Select',
                        multiselect: 'Multiselect',
                        image: 'Image',
                        gallery: 'Gallery',
                        radio: 'Radio',
                        money: 'Money'
                    }
            };
        },
        computed: {
            code() {
                return normalizeCode(this.name);
            },
            supportsOptions() {
                return ['select', 'multiselect', 'radio', 'checkbox'].includes(this.type);
            }
        },
        mounted() {
            if (!this.types || Object.keys(this.types).length === 0) {
                this.fetchTypes();
            }
        },
        methods: {
            async fetchTypes() {
                try {
                    const res = await this.$axios.get('/api/catalog/attribute-types');
                    if (res?.data) {
                        this.availableTypes = res.data;
                    }
                } catch (e) {
                    console.error(e);
                }
            },
            handleNameInput() {
                this.errors = {};
                this.globalError = null;
            },
            addOption() {
                const num = this.options.length + 1;
                this.options.push({
                    code: 'option_' + num,
                    label: 'Option ' + num,
                    sort_order: this.options.length
                });
            },
            removeOption(idx) {
                this.options.splice(idx, 1);
            },
            async saveAttribute() {
                if (this.saving) return;

                if (!this.name.trim()) {
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
                        name: this.name.trim(),
                        type: this.type,
                        required: Boolean(this.required),
                        is_filterable: Boolean(this.isFilterable),
                    };

                    if (this.supportsOptions && this.options.length > 0) {
                        payload.options = this.options.map((opt, idx) => ({
                            code: opt.code || `opt_${idx}`,
                            label: opt.label || `Option ${idx + 1}`,
                            sort_order: idx
                        }));
                    }

                    const res = await this.$axios.post('/api/catalog/attributes', payload);
                    const newId = res?.data?.id;

                    // Redirect to attribute edit or index view
                    if (newId) {
                        window.location.href = `/catalog/attributes/${newId}/edit`;
                    } else {
                        window.location.href = '/catalog/attributes';
                    }
                } catch (caught) {
                    this.globalError = caught.message || 'Failed to save attribute.';
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

    app.component('v-create-attributes', CreateAttributesComponent);
    app.component('attribute-create-modal', CreateAttributesComponent);
</script>
@endPushOnce
@endsection
