@extends('admin::layouts.admin')

@section('title', 'Edit Product: ' . $product->sku)
@section('crumb', $product->sku)

@section('content')
<div class="product-edit-page space-y-6">
    <v-product-editor
        :initial-product='@json($product)'
        :initial-groups='@json($groupsData)'
        :initial-values='@json($initialValues)'
        :initial-child-variants='@json($childVariantsData)'
        :variant-axes='@json($variantAxesData)'
        :variant-attributes='@json($variantAttributesData)'
        back-url="{{ route('admin.catalog.products.index') }}"
    ></v-product-editor>
</div>

@pushOnce('scripts')
<script type="text/x-template" id="v-product-editor-template">
    <div class="space-y-6">
        <!-- Top Sticky Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[var(--border)]">
            <div class="flex items-center gap-3 min-w-0">
                <a
                    :href="backUrl"
                    class="p-2 rounded-xl border border-[var(--border)] bg-[var(--card)] text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--soft)] transition-colors cursor-pointer shrink-0"
                    title="Back to Products"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="eyebrow hidden sm:block">Catalog / Products /</p>
                        <span class="font-mono text-xs px-2 py-0.5 rounded bg-[var(--soft)] text-[var(--teal)] border border-[var(--teal)]/20 font-semibold truncate max-w-[150px] sm:max-w-none">
                            @{{ product.sku }}
                        </span>
                        <!-- Type Badge -->
                        <span
                            v-if="product.type === 'configurable'"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-teal-500/10 text-[var(--teal)] border border-[var(--teal)]/30"
                        >
                            Configurable
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-500/10 text-blue-500 border border-blue-500/30"
                        >
                            Simple
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[var(--text)] mt-1 flex items-center gap-2 truncate">
                        <span class="truncate">@{{ displayName || product.sku }}</span>
                    </h1>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-3 w-full md:w-auto">
                <!-- Status Toggle -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-[var(--border)] bg-[var(--card)] shrink-0">
                    <span class="text-xs font-medium text-[var(--muted)]">Status</span>
                    <v-toggle
                        v-model="status"
                        @change="markDirty"
                    />
                </div>

                <!-- Save Changes Button -->
                <button
                    type="button"
                    @click="saveProduct"
                    :disabled="saving"
                    class="button button--primary text-xs px-4 py-2 inline-flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 cursor-pointer flex-1 sm:flex-initial"
                >
                    <svg v-if="!saving" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <svg v-else class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                    <span>@{{ saving ? 'Saving Changes...' : 'Save Product' }}</span>
                </button>
            </div>
        </div>

        <!-- Success Toast Notification -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-[-8px]"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-[-8px]"
        >
            <div v-if="saveSuccess" class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 text-xs rounded-xl font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>Product updated successfully.</span>
                </div>
                <button type="button" @click="saveSuccess = false" class="text-emerald-500 hover:opacity-80">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </transition>

        <!-- Error Alert -->
        <div v-if="saveError" class="p-3 bg-red-500/10 border border-red-500/30 text-red-500 text-xs rounded-xl font-medium flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>@{{ saveError }}</span>
        </div>

        <!-- Main Workspace: 2-Column Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Left Column: Stacked Collapsible Attribute Groups with Full Width Inputs -->
            <div class="lg:col-span-2 space-y-5">
                <!-- Empty state if no groups with attributes -->
                <div v-if="visibleGroups.length === 0" class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-8 text-center text-[var(--muted)] text-xs">
                    No attributes assigned to this product family.
                </div>

                <!-- Loop over all Attribute Groups that have attributes assigned -->
                <div
                    v-for="grp in visibleGroups"
                    :key="grp.id"
                    class="rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-xs overflow-hidden transition-all"
                >
                    <!-- Collapsible Group Header -->
                    <div
                        @click="toggleGroup(grp.id)"
                        class="px-6 py-4 flex items-center justify-between cursor-pointer select-none bg-[var(--page)]/30 hover:bg-[var(--soft)]/50 transition-colors"
                        :class="isGroupOpen(grp.id) ? 'border-b border-[var(--border)]' : ''"
                        title="Click to toggle group"
                    >
                        <div class="flex items-center gap-3">
                            <h2 class="text-sm font-bold text-[var(--text)]">@{{ grp.name }}</h2>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Open/Close Arrow Button: ^ for open, v for closed -->
                            <div
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--border)]/40 transition-colors"
                            >
                                <!-- Open Arrow ^ (Chevron UP) -->
                                <svg
                                    v-if="isGroupOpen(grp.id)"
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="text-[var(--teal)] transition-transform duration-200"
                                >
                                    <polyline points="18 15 12 9 6 15"></polyline>
                                </svg>
                                <!-- Closed Inverted Arrow v (Chevron DOWN) -->
                                <svg
                                    v-else
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="text-[var(--muted)] transition-transform duration-200"
                                >
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Group Body -->
                    <div v-show="isGroupOpen(grp.id)" class="p-6 space-y-5 animate-in fade-in duration-150">
                        <!-- Empty state if group has no attributes -->
                        <div v-if="grp.attributes.length === 0" class="py-6 text-center text-[var(--muted)] text-xs">
                            No attributes assigned to this group yet. You can assign attributes in the Product Type builder.
                        </div>

                        <!-- Full Width Attribute Form Inputs -->
                        <div
                            v-for="attr in grp.attributes"
                            :key="attr.id"
                            class="w-full space-y-1.5"
                        >
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <label class="block text-xs font-semibold text-[var(--text)]">
                                    @{{ attr.name }}
                                    <span v-if="attr.required" class="text-[var(--teal)]">*</span>
                                </label>

                                <!-- Scope Badges for Configurable Product -->
                                <div v-if="product.type === 'configurable'" class="flex items-center gap-1.5">
                                    <span
                                        v-if="attr.scope === 'parent'"
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20"
                                        title="Common attribute across all variants"
                                    >
                                        Parent Level
                                    </span>
                                    <span
                                        v-else-if="attr.scope === 'variant'"
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"
                                        title="Fillable on child variants in the matrix below"
                                    >
                                        Variant Level
                                    </span>
                                    <span
                                        v-else-if="attr.scope === 'axis_1'"
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-teal-500/10 text-[var(--teal)] border border-[var(--teal)]/20"
                                        title="Variation Axis Level 1"
                                    >
                                        Axis (L1)
                                    </span>
                                    <span
                                        v-else-if="attr.scope === 'axis_2'"
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20"
                                        title="Variation Axis Level 2"
                                    >
                                        Axis (L2)
                                    </span>
                                </div>
                            </div>

                            <!-- Small hint if variant-level or axis -->
                            <p v-if="product.type === 'configurable' && attr.scope === 'variant'" class="text-[11px] text-[var(--muted)] italic">
                                Note: This attribute can be filled individually per child variant in the Child Variants Matrix below.
                            </p>

                            <!-- Input based on attribute.type: FULL WIDTH (w-full) -->
                            <!-- 1. Text input -->
                            <div v-if="attr.type === 'text'" class="w-full">
                                <input
                                    type="text"
                                    v-model="values[attr.id]"
                                    @input="markDirty"
                                    class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                                    :placeholder="'Enter ' + attr.name.toLowerCase() + '...'"
                                />
                            </div>

                            <!-- 2. Textarea input -->
                            <div v-else-if="attr.type === 'textarea'" class="w-full">
                                <textarea
                                    v-model="values[attr.id]"
                                    @input="markDirty"
                                    rows="4"
                                    class="w-full p-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                                    :placeholder="'Enter ' + attr.name.toLowerCase() + '...'"
                                ></textarea>
                            </div>

                            <!-- 3. Number / Price / Money -->
                            <div v-else-if="attr.type === 'price' || attr.type === 'money'" class="w-full">
                                <div class="relative w-full">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs text-[var(--muted)] font-mono">$</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="values[attr.id]"
                                        @input="markDirty"
                                        class="w-full h-10 pl-8 pr-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs font-mono text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>

                            <!-- 4. Boolean Toggle -->
                            <div v-else-if="attr.type === 'boolean'" class="w-full pt-1">
                                <v-toggle
                                    v-model="values[attr.id]"
                                    :label="attr.name"
                                    @change="markDirty"
                                />
                            </div>

                            <!-- 5. Select dropdown -->
                            <div v-else-if="attr.type === 'select' || attr.type === 'radio'" class="w-full">
                                <select
                                    v-model="values[attr.id]"
                                    @change="markDirty"
                                    class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all cursor-pointer"
                                >
                                    <option :value="null">-- Select option --</option>
                                    <option v-for="opt in attr.options" :key="opt.id" :value="opt.id">
                                        @{{ opt.admin_name }}
                                    </option>
                                </select>
                            </div>

                            <!-- 6. Multiselect / Checkbox list -->
                            <div v-else-if="attr.type === 'multiselect' || attr.type === 'checkbox'" class="w-full space-y-2 p-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)]">
                                <div v-if="attr.options.length === 0" class="text-[11px] text-[var(--muted)]">No options configured for this attribute.</div>
                                <label
                                    v-for="opt in attr.options"
                                    :key="opt.id"
                                    class="flex items-center gap-2 text-xs text-[var(--text)] cursor-pointer hover:text-[var(--teal)]"
                                >
                                    <input
                                        type="checkbox"
                                        :value="opt.id"
                                        v-model="values[attr.id]"
                                        @change="markDirty"
                                        class="rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)]"
                                    />
                                    <span>@{{ opt.admin_name }}</span>
                                </label>
                            </div>

                            <!-- 7. Fallback input -->
                            <div v-else class="w-full">
                                <input
                                    type="text"
                                    v-model="values[attr.id]"
                                    @input="markDirty"
                                    class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: Product Meta & Variant Profile (1 col) -->
            <div class="space-y-6">
                <!-- Product Metadata Card -->
                <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--muted)]">Product Specifications</h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-[var(--border)]">
                            <span class="text-[var(--muted)]">SKU</span>
                            <span class="font-mono font-semibold text-[var(--text)]">@{{ product.sku }}</span>
                        </div>

                        <div class="flex items-center justify-between py-1 border-b border-[var(--border)]">
                            <span class="text-[var(--muted)]">Product Type</span>
                            <span class="capitalize font-semibold text-[var(--text)]">@{{ product.type }}</span>
                        </div>

                        <div class="flex items-center justify-between py-1 border-b border-[var(--border)]">
                            <span class="text-[var(--muted)]">Family</span>
                            <span class="font-semibold text-[var(--text)]">@{{ product.product_type ? product.product_type.name : '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between py-1 border-b border-[var(--border)]">
                            <span class="text-[var(--muted)]">Created</span>
                            <span class="text-[var(--muted)]">@{{ formattedCreatedAt }}</span>
                        </div>
                    </div>
                </div>

                <!-- Configurable Product Profile Card (if configurable) -->
                <div v-if="product.type === 'configurable' && product.product_type_variant" class="rounded-2xl border border-[var(--teal)]/30 bg-[var(--card)] p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-teal-500/10 text-[var(--teal)] flex items-center justify-center">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><path d="M6 21V9a9 9 0 0 0 9 9"/></svg>
                            </div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--teal)]">Variant Profile</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-teal-500/10 text-[var(--teal)] border border-[var(--teal)]/20">
                            @{{ product.product_type_variant.level_count }}-Level
                        </span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[var(--muted)]">Profile Name:</span>
                            <span class="font-semibold text-[var(--text)]">@{{ product.product_type_variant.name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[var(--muted)]">Code:</span>
                            <span class="font-mono text-[var(--muted)]">@{{ product.product_type_variant.code }}</span>
                        </div>
                    </div>

                    <!-- Axes List -->
                    <div v-if="variantAxes.length > 0" class="pt-2 border-t border-[var(--border)] space-y-2">
                        <p class="text-[11px] font-semibold text-[var(--muted)]">Variation Axes:</p>
                        <div class="space-y-1">
                            <div
                                v-for="axis in variantAxes"
                                :key="axis.id"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-[var(--page)] border border-[var(--border)] text-xs"
                            >
                                <span class="font-medium text-[var(--text)]">@{{ axis.attribute ? axis.attribute.name : ('Attribute #' + axis.attribute_id) }}</span>
                                <span class="text-[10px] font-mono text-[var(--teal)] font-bold">L@{{ axis.level }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Variant-Level Attributes in Sidebar -->
                    <div v-if="variantAttributesList.length > 0" class="pt-2 border-t border-[var(--border)] space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-semibold text-[var(--muted)]">Variant-Level Attributes:</p>
                            <span class="text-[10px] font-mono text-[var(--muted)]">@{{ variantAttributesList.length }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <span
                                v-for="va in variantAttributesList"
                                :key="va.id"
                                class="px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"
                            >
                                @{{ va.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Parent-Level Attributes in Sidebar -->
                    <div class="pt-2 border-t border-[var(--border)] space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-semibold text-[var(--muted)]">Parent-Level Attributes:</p>
                            <span class="text-[10px] font-mono text-[var(--muted)]">@{{ parentLevelAttributes.length }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <span
                                v-for="pa in parentLevelAttributes"
                                :key="pa.id"
                                class="px-2 py-0.5 rounded text-[10px] font-medium bg-[var(--soft)] text-[var(--muted)] border border-[var(--border)]"
                            >
                                @{{ pa.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Child Variants Matrix (Configurable Products) -->
        <div v-if="product.type === 'configurable'" class="rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-xs overflow-hidden transition-all space-y-4">
            <!-- Header -->
            <div class="px-4 sm:px-6 py-4 border-b border-[var(--border)] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[var(--page)]/30">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-bold text-[var(--text)]">Child Variants Matrix</h2>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-teal-500/10 text-[var(--teal)] border border-[var(--teal)]/20 font-mono">
                            @{{ childVariants.length }} @{{ childVariants.length === 1 ? 'variant' : 'variants' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-[var(--muted)] mt-0.5">
                        Child SKUs are generated with formula: <code class="font-mono text-[var(--teal)]">@{{ product.sku }}-{level1}-{level2}</code>. Fill variant-level attributes for each SKU.
                    </p>
                </div>

                <!-- Action Buttons: Generate All & Add Row -->
                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    <button
                        type="button"
                        @click="generateAllVariants"
                        class="px-3 py-1.5 rounded-xl border border-[var(--teal)]/40 bg-[var(--teal)]/10 text-[var(--teal)] text-xs font-semibold hover:bg-[var(--teal)] hover:text-white transition-all cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-initial"
                        title="Automatically generate variants from all axis options"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        <span>Generate All</span>
                    </button>
                    <button
                        type="button"
                        @click="addNewVariantRow"
                        class="px-3 py-1.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-[var(--text)] text-xs font-semibold hover:border-[var(--teal)] transition-all cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-initial"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Add Row</span>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="childVariants.length === 0" class="p-8 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-[var(--soft)] flex items-center justify-center mx-auto text-[var(--muted)]">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><path d="M6 21V9a9 9 0 0 0 9 9"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-xs font-bold text-[var(--text)]">No child variants created yet</h3>
                    <p class="text-[11px] text-[var(--muted)]">Click "Generate All" to automatically generate combinations from axes options, or "Add Row" to manually create variant SKUs.</p>
                </div>
            </div>

            <!-- Variants List -->
            <div v-else>
                <!-- Mobile Card List View (Visible on block md:hidden for phones & small screens) -->
                <div class="block md:hidden divide-y divide-[var(--border)]">
                    <div
                        v-for="(variantRow, rIdx) in childVariants"
                        :key="variantRow.id || ('mobile_temp_' + rIdx)"
                        class="p-4 space-y-3 bg-[var(--card)]"
                    >
                        <!-- Card Top: Variant Title/Index, Status & Delete -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-2 h-2 rounded-full bg-[var(--teal)] shrink-0"></span>
                                <span class="text-xs font-bold text-[var(--text)] truncate">Variant #@{{ rIdx + 1 }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <v-toggle
                                    v-model="variantRow.status"
                                    @change="markDirty"
                                />
                                <button
                                    type="button"
                                    @click="removeVariantRow(rIdx)"
                                    class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer transition-colors"
                                    title="Delete variant"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- SKU Field -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-[var(--muted)]">Variant SKU</label>
                            <input
                                type="text"
                                v-model="variantRow.sku"
                                @input="markDirty"
                                class="w-full h-9 px-3 rounded-lg border border-[var(--border)] bg-[var(--page)] font-mono text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                placeholder="Variant SKU"
                            />
                        </div>

                        <!-- Variation Axes (Level 1 & Level 2 in a 2-col or 1-col grid) -->
                        <div v-if="level1Axis || level2Axis" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <!-- Level 1 Axis -->
                            <div v-if="level1Axis" class="space-y-1">
                                <label class="flex items-center gap-1.5 text-[11px] font-semibold text-[var(--muted)] truncate">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--teal)] shrink-0"></span>
                                    <span class="truncate">@{{ level1Axis.attribute?.name || 'Level 1 Axis' }}</span>
                                </label>
                                <select
                                    v-model="variantRow.axes_values[level1Axis.attribute_id]"
                                    @change="onAxisValueChange(variantRow)"
                                    class="w-full h-9 px-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                >
                                    <option :value="null">-- Select --</option>
                                    <option
                                        v-for="opt in (level1Axis.attribute?.options || [])"
                                        :key="opt.id"
                                        :value="opt.id"
                                    >
                                        @{{ opt.admin_name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Level 2 Axis -->
                            <div v-if="level2Axis" class="space-y-1">
                                <label class="flex items-center gap-1.5 text-[11px] font-semibold text-[var(--muted)] truncate">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500 shrink-0"></span>
                                    <span class="truncate">@{{ level2Axis.attribute?.name || 'Level 2 Axis' }}</span>
                                </label>
                                <select
                                    v-model="variantRow.axes_values[level2Axis.attribute_id]"
                                    @change="onAxisValueChange(variantRow)"
                                    class="w-full h-9 px-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                >
                                    <option :value="null">-- Select --</option>
                                    <option
                                        v-for="opt in (level2Axis.attribute?.options || [])"
                                        :key="opt.id"
                                        :value="opt.id"
                                    >
                                        @{{ opt.admin_name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Variant-Level Attributes (e.g. Price, Weight) -->
                        <div v-if="variantAttributesList.length > 0" class="pt-2 border-t border-[var(--border)]/60 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div
                                v-for="vAttr in variantAttributesList"
                                :key="'m_' + vAttr.id"
                                class="space-y-1"
                            >
                                <label class="flex items-center gap-1.5 text-[11px] font-semibold text-[var(--muted)] truncate">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                    <span class="truncate">@{{ vAttr.name }}</span>
                                </label>

                                <!-- Price / Money -->
                                <div v-if="vAttr.type === 'price' || vAttr.type === 'money'" class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-xs text-[var(--muted)] font-mono">$</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="variantRow.values[vAttr.id]"
                                        @input="markDirty"
                                        class="w-full h-9 pl-6 pr-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs font-mono text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                        placeholder="0.00"
                                    />
                                </div>

                                <!-- Select / Radio -->
                                <select
                                    v-else-if="vAttr.type === 'select' || vAttr.type === 'radio'"
                                    v-model="variantRow.values[vAttr.id]"
                                    @change="markDirty"
                                    class="w-full h-9 px-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                >
                                    <option :value="null">--</option>
                                    <option v-for="opt in vAttr.options" :key="opt.id" :value="opt.id">
                                        @{{ opt.admin_name }}
                                    </option>
                                </select>

                                <!-- Boolean -->
                                <div v-else-if="vAttr.type === 'boolean'" class="flex items-center pt-1">
                                    <v-toggle
                                        v-model="variantRow.values[vAttr.id]"
                                        @change="markDirty"
                                    />
                                </div>

                                <!-- Text fallback -->
                                <input
                                    v-else
                                    type="text"
                                    v-model="variantRow.values[vAttr.id]"
                                    @input="markDirty"
                                    class="w-full h-9 px-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                    :placeholder="'Enter ' + vAttr.name.toLowerCase()"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table of Variants (Visible on hidden md:block) -->
                <div class="hidden md:block overflow-x-auto touch-pan-x">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-[var(--page)] border-y border-[var(--border)] text-[var(--muted)] font-semibold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-4 py-3">Variant SKU</th>
                                <!-- Level 1 Axis Column -->
                                <th v-if="level1Axis" class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--teal)]"></span>
                                        <span>@{{ level1Axis.attribute?.name || 'Level 1 Axis' }}</span>
                                    </div>
                                </th>
                                <!-- Level 2 Axis Column (if 2 levels) -->
                                <th v-if="level2Axis" class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                        <span>@{{ level2Axis.attribute?.name || 'Level 2 Axis' }}</span>
                                    </div>
                                </th>
                                <!-- Variant-Level Attributes Columns (e.g. Price, Weight) -->
                                <th
                                    v-for="vAttr in variantAttributesList"
                                    :key="vAttr.id"
                                    class="px-4 py-3"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        <span>@{{ vAttr.name }}</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-center w-20">Status</th>
                                <th class="px-4 py-3 text-right w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            <tr
                                v-for="(variantRow, rIdx) in childVariants"
                                :key="variantRow.id || ('temp_' + rIdx)"
                                class="hover:bg-[var(--soft)]/40 transition-colors"
                            >
                                <!-- SKU Input -->
                                <td class="px-4 py-2.5">
                                    <input
                                        type="text"
                                        v-model="variantRow.sku"
                                        @input="markDirty"
                                        class="w-full min-w-[170px] h-8 px-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] font-mono text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                        placeholder="Variant SKU"
                                    />
                                </td>

                                <!-- Level 1 Option Selector -->
                                <td v-if="level1Axis" class="px-4 py-2.5 min-w-[140px]">
                                    <select
                                        v-model="variantRow.axes_values[level1Axis.attribute_id]"
                                        @change="onAxisValueChange(variantRow)"
                                        class="w-full h-8 px-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                    >
                                        <option :value="null">-- Select --</option>
                                        <option
                                            v-for="opt in (level1Axis.attribute?.options || [])"
                                            :key="opt.id"
                                            :value="opt.id"
                                        >
                                            @{{ opt.admin_name }}
                                        </option>
                                    </select>
                                </td>

                                <!-- Level 2 Option Selector -->
                                <td v-if="level2Axis" class="px-4 py-2.5 min-w-[140px]">
                                    <select
                                        v-model="variantRow.axes_values[level2Axis.attribute_id]"
                                        @change="onAxisValueChange(variantRow)"
                                        class="w-full h-8 px-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                    >
                                        <option :value="null">-- Select --</option>
                                        <option
                                            v-for="opt in (level2Axis.attribute?.options || [])"
                                            :key="opt.id"
                                            :value="opt.id"
                                        >
                                            @{{ opt.admin_name }}
                                        </option>
                                    </select>
                                </td>

                                <!-- Variant-Level Attributes Inputs -->
                                <td
                                    v-for="vAttr in variantAttributesList"
                                    :key="vAttr.id"
                                    class="px-4 py-2.5 min-w-[130px]"
                                >
                                    <!-- Price / Number -->
                                    <div v-if="vAttr.type === 'price' || vAttr.type === 'money'" class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-[11px] text-[var(--muted)] font-mono">$</span>
                                        <input
                                            type="number"
                                            step="0.01"
                                            v-model="variantRow.values[vAttr.id]"
                                            @input="markDirty"
                                            class="w-full h-8 pl-5 pr-2 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs font-mono text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <!-- Select / Radio -->
                                    <select
                                        v-else-if="vAttr.type === 'select' || vAttr.type === 'radio'"
                                        v-model="variantRow.values[vAttr.id]"
                                        @change="markDirty"
                                        class="w-full h-8 px-2 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                    >
                                        <option :value="null">--</option>
                                        <option v-for="opt in vAttr.options" :key="opt.id" :value="opt.id">
                                            @{{ opt.admin_name }}
                                        </option>
                                    </select>
                                    <!-- Boolean -->
                                    <div v-else-if="vAttr.type === 'boolean'" class="flex items-center">
                                        <v-toggle
                                            v-model="variantRow.values[vAttr.id]"
                                            @change="markDirty"
                                        />
                                    </div>
                                    <!-- Text fallback -->
                                    <input
                                        v-else
                                        type="text"
                                        v-model="variantRow.values[vAttr.id]"
                                        @input="markDirty"
                                        class="w-full h-8 px-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                                        :placeholder="'Enter ' + vAttr.name.toLowerCase()"
                                    />
                                </td>

                                <!-- Status Toggle -->
                                <td class="px-4 py-2.5 text-center">
                                    <v-toggle
                                        v-model="variantRow.status"
                                        @change="markDirty"
                                    />
                                </td>

                                <!-- Action: Delete -->
                                <td class="px-4 py-2.5 text-right">
                                    <button
                                        type="button"
                                        @click="removeVariantRow(rIdx)"
                                        class="p-1 rounded text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer transition-colors"
                                        title="Delete variant"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</script>

<script>
(function() {
    const ProductEditorComponent = {
        template: '#v-product-editor-template',
        props: {
            initialProduct: {
                type: Object,
                required: true
            },
            initialGroups: {
                type: Array,
                default: () => []
            },
            initialValues: {
                type: Object,
                default: () => ({})
            },
            initialChildVariants: {
                type: Array,
                default: () => []
            },
            variantAxes: {
                type: Array,
                default: () => []
            },
            variantAttributes: {
                type: Array,
                default: () => []
            },
            backUrl: {
                type: String,
                default: '/catalog/products'
            }
        },
        data() {
            // Prepare initial values map ensuring array for multiselect
            const values = {};
            const groups = Array.isArray(this.initialGroups) ? this.initialGroups : [];

            groups.forEach(grp => {
                (grp.attributes || []).forEach(attr => {
                    const existing = this.initialValues[attr.id];
                    if (attr.type === 'multiselect' || attr.type === 'checkbox') {
                        values[attr.id] = Array.isArray(existing) ? existing : (existing ? [existing] : []);
                    } else if (attr.type === 'boolean') {
                        values[attr.id] = Boolean(existing);
                    } else {
                        values[attr.id] = existing !== undefined && existing !== null ? existing : '';
                    }
                });
            });

            // Parse initial child variants
            const childVariants = (this.initialChildVariants || []).map(v => ({
                id: v.id || null,
                sku: v.sku || '',
                status: v.status !== undefined ? Boolean(v.status) : true,
                axes_values: { ...(v.axes_values || {}) },
                values: { ...(v.values || {}) }
            }));

            return {
                product: this.initialProduct,
                groups: groups,
                values: values,
                status: Boolean(this.initialProduct.status),
                childVariants: childVariants,
                axesList: Array.isArray(this.variantAxes) ? this.variantAxes : [],
                variantAttributesList: Array.isArray(this.variantAttributes) ? this.variantAttributes : [],
                collapsedGroups: {}, // false by default (open)
                saving: false,
                isDirty: false,
                saveSuccess: false,
                saveError: null
            };
        },
        computed: {
            visibleGroups() {
                return (this.groups || []).filter(grp => Array.isArray(grp.attributes) && grp.attributes.length > 0);
            },
            displayName() {
                // Find attribute named 'name' or 'title' if present in values
                for (const grp of this.groups) {
                    for (const attr of grp.attributes) {
                        if (attr.code === 'name' || attr.code === 'title') {
                            const val = this.values[attr.id];
                            if (val && typeof val === 'string') {
                                return val;
                            }
                        }
                    }
                }
                return null;
            },
            formattedCreatedAt() {
                if (!this.product.created_at) return '—';
                const d = new Date(this.product.created_at);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            },
            level1Axis() {
                return this.axesList.find(a => a.level === 1) || null;
            },
            level2Axis() {
                return this.axesList.find(a => a.level === 2) || null;
            },
            parentLevelAttributes() {
                const axesIds = new Set(this.axesList.map(a => a.attribute_id));
                const vAttrIds = new Set(this.variantAttributesList.map(a => a.id));
                const parents = [];
                for (const g of this.groups) {
                    for (const a of (g.attributes || [])) {
                        if (!axesIds.has(a.id) && !vAttrIds.has(a.id)) {
                            parents.push(a);
                        }
                    }
                }
                return parents;
            }
        },
        methods: {
            toggleGroup(groupId) {
                this.collapsedGroups[groupId] = !this.collapsedGroups[groupId];
            },
            isGroupOpen(groupId) {
                return !this.collapsedGroups[groupId];
            },
            markDirty() {
                this.isDirty = true;
                this.saveSuccess = false;
                this.saveError = null;
            },
            formatOptionTag(opt) {
                if (!opt) return '';
                const raw = opt.admin_name || opt.code || '';
                return raw.toString().toLowerCase().trim().replace(/[\s_]+/g, '-');
            },
            buildGeneratedSku(opt1, opt2 = null) {
                const parentSku = this.product.sku || 'SKU';
                const tag1 = this.formatOptionTag(opt1);
                if (opt2) {
                    const tag2 = this.formatOptionTag(opt2);
                    return `${parentSku}-${tag1}-${tag2}`;
                }
                return `${parentSku}-${tag1}`;
            },
            generateAllVariants() {
                if (!this.level1Axis || !this.level1Axis.attribute) return;

                const l1Options = this.level1Axis.attribute.options || [];
                const l2Options = (this.level2Axis && this.level2Axis.attribute) ? (this.level2Axis.attribute.options || []) : [];

                if (l1Options.length === 0) {
                    this.saveError = 'Please configure options for Level 1 axis attribute first.';
                    return;
                }

                const existingCombinations = new Set();
                this.childVariants.forEach(v => {
                    const key = `${v.axes_values[this.level1Axis.attribute_id] || ''}__${this.level2Axis ? (v.axes_values[this.level2Axis.attribute_id] || '') : ''}`;
                    existingCombinations.add(key);
                });

                let addedCount = 0;

                if (this.level2Axis && l2Options.length > 0) {
                    // 2-level cartesian product
                    l1Options.forEach(opt1 => {
                        l2Options.forEach(opt2 => {
                            const key = `${opt1.id}__${opt2.id}`;
                            if (!existingCombinations.has(key)) {
                                existingCombinations.add(key);
                                this.childVariants.push({
                                    id: null,
                                    sku: this.buildGeneratedSku(opt1, opt2),
                                    status: true,
                                    axes_values: {
                                        [this.level1Axis.attribute_id]: opt1.id,
                                        [this.level2Axis.attribute_id]: opt2.id
                                    },
                                    values: {}
                                });
                                addedCount++;
                            }
                        });
                    });
                } else {
                    // 1-level direct variants
                    l1Options.forEach(opt1 => {
                        const key = `${opt1.id}__`;
                        if (!existingCombinations.has(key)) {
                            existingCombinations.add(key);
                            this.childVariants.push({
                                id: null,
                                sku: this.buildGeneratedSku(opt1),
                                status: true,
                                axes_values: {
                                    [this.level1Axis.attribute_id]: opt1.id
                                },
                                values: {}
                            });
                            addedCount++;
                        }
                    });
                }

                if (addedCount > 0) {
                    this.markDirty();
                }
            },
            addNewVariantRow() {
                const tempIndex = this.childVariants.length + 1;
                this.childVariants.push({
                    id: null,
                    sku: `${this.product.sku}-variant-${tempIndex}`,
                    status: true,
                    axes_values: {},
                    values: {}
                });
                this.markDirty();
            },
            removeVariantRow(idx) {
                this.childVariants.splice(idx, 1);
                this.markDirty();
            },
            onAxisValueChange(variantRow) {
                this.markDirty();
                if (this.level1Axis) {
                    const opt1Id = variantRow.axes_values[this.level1Axis.attribute_id];
                    const opt1 = (this.level1Axis.attribute?.options || []).find(o => o.id == opt1Id);
                    if (opt1) {
                        let opt2 = null;
                        if (this.level2Axis) {
                            const opt2Id = variantRow.axes_values[this.level2Axis.attribute_id];
                            opt2 = (this.level2Axis.attribute?.options || []).find(o => o.id == opt2Id);
                        }
                        variantRow.sku = this.buildGeneratedSku(opt1, opt2);
                    }
                }
            },
            async saveProduct() {
                if (this.saving) return;

                this.saving = true;
                this.saveSuccess = false;
                this.saveError = null;

                try {
                    const payload = {
                        status: this.status,
                        values: this.values
                    };

                    if (this.product.type === 'configurable') {
                        payload.variants = this.childVariants.map(v => ({
                            id: v.id,
                            sku: v.sku,
                            status: v.status,
                            axes_values: v.axes_values,
                            values: v.values
                        }));
                    }

                    const axiosClient = window.axios || this.$axios;
                    const res = await axiosClient.put(`/api/catalog/products/${this.product.id}`, payload);

                    if (res?.data?.data) {
                        this.product = { ...this.product, ...res.data.data };
                    }

                    this.isDirty = false;
                    this.saveSuccess = true;

                    setTimeout(() => {
                        this.saveSuccess = false;
                    }, 4000);
                } catch (caught) {
                    const resp = caught?.response?.data || caught;
                    this.saveError = resp?.message || 'Failed to save product changes.';
                } finally {
                    this.saving = false;
                }
            }
        }
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-product-editor', ProductEditorComponent);
    } else if (window.app && typeof window.app.component === 'function') {
        window.app.component('v-product-editor', ProductEditorComponent);
    }
})();
</script>
@endpushOnce
@endsection
