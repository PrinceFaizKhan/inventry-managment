@extends('admin::layouts.admin')

@section('title', 'Edit Attribute')
@section('crumb', 'Edit Attribute')

@section('content')
<div class="attribute-edit-page">
    {!! view_event_render('catalog.attributes.edit.before') !!}

    <v-edit-attributes
        :initial-attribute='@json($attribute)'
        :initial-types='@json($types)'
        :attribute-id="{{ is_array($attribute) ? $attribute['id'] : $attribute->id }}"
        back-url="{{ url('catalog/attributes') }}"
        csrf-token="{{ csrf_token() }}"
    ></v-edit-attributes>

    <div class="hidden" aria-hidden="true">
        <attribute-edit-form
            :initial-attribute='@json($attribute)'
            :initial-types='@json($types)'
            :attribute-id="{{ is_array($attribute) ? $attribute['id'] : $attribute->id }}"
            back-url="{{ url('catalog/attributes') }}"
            csrf-token="{{ csrf_token() }}"
        ></attribute-edit-form>
    </div>

    {!! view_event_render('catalog.attributes.edit.after') !!}
</div>

@pushOnce('scripts')
<script type="text/x-template" id="v-edit-attributes-template">
    <form @submit.prevent="saveAttribute" class="space-y-6">
        <!-- Page Header & Action Buttons -->
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-[var(--border)]">
            <div>
                <p class="eyebrow">Catalog / Attributes</p>
                <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">
                    Edit Attribute: <span class="text-[var(--teal)]">@{{ name || (attr ? attr.name : '') }}</span>
                </h1>
            </div>
            <div class="flex items-center gap-2.5">
                <a :href="backUrl" class="button button--outline text-xs">
                    Back
                </a>
                <button
                    type="submit"
                    :disabled="saving"
                    class="button button--primary text-xs inline-flex items-center gap-1.5"
                >
                    <svg v-if="!saving" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>@{{ saving ? 'Saving...' : saved ? 'Saved!' : 'Save Attribute' }}</span>
                </button>
            </div>
        </div>

        <!-- Success Toast -->
        <div v-if="saved" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg font-medium flex items-center justify-between">
            <span>Attribute updated successfully.</span>
            <button type="button" @click="saved = false" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>

        <!-- Global Error Alert -->
        <div v-if="globalError" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg font-medium">
            @{{ globalError }}
        </div>

        <!-- Two Column Layout matching UnoPim -->
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
                            @input="errors.name = null; saved = false"
                            required
                            maxlength="120"
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                            :class="errors.name ? 'border-red-500' : ''"
                        />
                        <p v-if="errors.name" class="mt-1 text-xs text-red-600">@{{ errors.name }}</p>
                    </div>

                    <!-- Attribute Code (Read-Only) -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            Code
                        </label>
                        <input
                            type="text"
                            :value="attr ? attr.code : ''"
                            readonly
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-sm font-mono text-[var(--muted)] cursor-not-allowed select-all"
                        />
                        <p class="mt-1 text-[11px] text-[var(--muted)]">Permanent unique identifier. Cannot be modified.</p>
                    </div>

                    <!-- Attribute Type (Read-Only) -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            Type
                        </label>
                        <input
                            type="text"
                            :value="typeLabel"
                            readonly
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-sm text-[var(--muted)] cursor-not-allowed uppercase font-semibold"
                        />
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
                        <div v-for="(opt, index) in options" :key="opt.id || index" class="flex items-center gap-2 p-2 rounded-lg bg-[var(--page)] border border-[var(--border)]">
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
                            @change="saved = false"
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
                            @change="saved = false"
                            class="rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)] w-4 h-4"
                        />
                    </label>
                </div>
            </div>
        </div>
    </form>
</script>

<script type="module">
    const EditAttributesComponent = {
        template: '#v-edit-attributes-template',
        props: {
            initialAttribute: {
                type: Object,
                default: null
            },
            initialTypes: {
                type: Object,
                default: () => ({})
            },
            attributeId: {
                type: [Number, String],
                required: true
            },
            backUrl: {
                type: String,
                default: '/catalog/attributes'
            },
            csrfToken: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                attr: this.initialAttribute,
                name: this.initialAttribute ? this.initialAttribute.name : '',
                required: this.initialAttribute ? Boolean(this.initialAttribute.required) : false,
                isFilterable: this.initialAttribute ? Boolean(this.initialAttribute.is_filterable) : false,
                settings: this.initialAttribute && this.initialAttribute.settings ? { ...this.initialAttribute.settings } : {},
                options: this.initialAttribute && this.initialAttribute.options ? this.initialAttribute.options.map(o => ({ ...o })) : [],
                saving: false,
                saved: false,
                errors: {},
                globalError: null
            };
        },
        computed: {
            typeLabel() {
                if (!this.attr) return '';
                const key = this.attr.type;
                if (this.initialTypes && this.initialTypes[key]) {
                    return this.initialTypes[key].label || key;
                }
                return key;
            },
            supportsOptions() {
                if (!this.attr) return false;
                return ['select', 'multiselect', 'radio', 'checkbox'].includes(this.attr.type);
            }
        },
        mounted() {
            if (!this.attr && this.attributeId) {
                this.loadAttribute();
            }
        },
        methods: {
            async loadAttribute() {
                try {
                    const res = await this.$axios.get(`/api/catalog/attributes/${this.attributeId}`);
                    if (res?.data) {
                        this.attr = res.data;
                        this.name = res.data.name;
                        this.required = Boolean(res.data.required);
                        this.isFilterable = Boolean(res.data.is_filterable);
                        this.settings = res.data.settings || {};
                        this.options = res.data.options ? res.data.options.map(o => ({ ...o })) : [];
                    }
                } catch (e) {
                    console.error(e);
                }
            },
            addOption() {
                const num = this.options.length + 1;
                this.options.push({
                    code: 'option_' + num,
                    label: 'Option ' + num,
                    sort_order: this.options.length
                });
                this.saved = false;
            },
            removeOption(idx) {
                this.options.splice(idx, 1);
                this.saved = false;
            },
            async saveAttribute() {
                if (this.saving || !this.attr) return;

                if (!this.name.trim()) {
                    this.errors = { name: 'Attribute name is required.' };
                    return;
                }

                this.saving = true;
                this.saved = false;
                this.errors = {};
                this.globalError = null;

                try {
                    const payload = {
                        name: this.name.trim(),
                        required: Boolean(this.required),
                        is_filterable: Boolean(this.isFilterable),
                        settings: this.settings,
                    };

                    if (this.supportsOptions) {
                        payload.options = this.options.map((opt, idx) => ({
                            id: opt.id || undefined,
                            code: opt.code || `opt_${idx}`,
                            label: opt.label || `Option ${idx + 1}`,
                            sort_order: idx
                        }));
                    }

                    await this.$axios.put(`/api/catalog/attributes/${this.attr.id}`, payload);
                    this.saved = true;
                } catch (caught) {
                    this.globalError = caught.message || 'Failed to update attribute.';
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

    app.component('v-edit-attributes', EditAttributesComponent);
    app.component('attribute-edit-form', EditAttributesComponent);
</script>
@endPushOnce
@endsection
