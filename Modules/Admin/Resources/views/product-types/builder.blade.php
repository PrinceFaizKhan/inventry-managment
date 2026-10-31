@pushOnce('scripts')
<script type="text/x-template" id="v-product-type-builder-template">
    <div class="space-y-6">
        <!-- Top Sticky Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[var(--border)]">
            <div>
                <p class="eyebrow">Catalog / Product Types</p>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[var(--text)]">
                    @{{ mode === 'create' ? 'Create Product Type' : ('Edit Product Type: ' + (form.name || '')) }}
                </h1>
                <p class="page-copy text-xs text-[var(--muted)] mt-1">
                    Configure general attribute groups and 1-level or 2-level multi-variant types for configurable products.
                </p>
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <a :href="backUrl" class="button button--outline text-xs flex-1 sm:flex-initial text-center justify-center">
                    Cancel
                </a>
                <button
                    type="button"
                    @click="submitForm"
                    :disabled="saving"
                    class="button button--primary text-xs inline-flex items-center justify-center gap-1.5 cursor-pointer shadow-sm disabled:opacity-50 flex-1 sm:flex-initial"
                >
                    <svg v-if="!saving" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <svg v-else class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>@{{ saving ? 'Saving...' : (saved ? 'Saved!' : (mode === 'create' ? 'Create Product Type' : 'Save Changes')) }}</span>
                </button>
            </div>
        </div>

        <!-- Alerts -->
        <div v-if="saved" class="p-3.5 bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs rounded-xl font-medium flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-600 dark:text-emerald-400">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span>Product type saved successfully! Redirecting...</span>
            </div>
            <button type="button" @click="saved = false" class="text-emerald-600 dark:text-emerald-400 hover:opacity-80 text-base leading-none cursor-pointer">&times;</button>
        </div>

        <div v-if="errorMessage" class="p-3.5 bg-rose-50 border border-rose-200 dark:bg-rose-950/40 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-xs rounded-xl font-medium flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-600 dark:text-rose-400">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>@{{ errorMessage }}</span>
            </div>
            <button type="button" @click="errorMessage = null" class="text-rose-600 dark:text-rose-400 hover:opacity-80 text-base leading-none cursor-pointer">&times;</button>
        </div>

        <!-- 2 Main Tabs: General & Variant Types -->
        <div class="flex items-center border-b border-[var(--border)] gap-4 sm:gap-6 overflow-x-auto whitespace-nowrap scrollbar-none">
            <!-- Tab 1: General -->
            <button
                type="button"
                @click="activeMainTab = 'general'"
                class="pb-3 px-1 text-xs font-bold inline-flex items-center gap-2 border-b-2 transition-all cursor-pointer shrink-0"
                :class="activeMainTab === 'general' ? 'border-[var(--teal)] text-[var(--teal)]' : 'border-transparent text-[var(--muted)] hover:text-[var(--text)]'"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
                <span>General</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[var(--soft)] text-[var(--muted)] border border-[var(--border)]">
                    @{{ form.groups.length }} Groups
                </span>
            </button>

            <!-- Tab 2: Variant Types -->
            <button
                type="button"
                @click="activeMainTab = 'variants'"
                class="pb-3 px-1 text-xs font-bold inline-flex items-center gap-2 border-b-2 transition-all cursor-pointer shrink-0"
                :class="activeMainTab === 'variants' ? 'border-[var(--teal)] text-[var(--teal)]' : 'border-transparent text-[var(--muted)] hover:text-[var(--text)]'"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="18" cy="18" r="3"/>
                    <circle cx="6" cy="6" r="3"/>
                    <path d="M6 21V9a9 9 0 0 0 9 9"/>
                </svg>
                <span>Variant Types (Configurable)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[var(--soft)] text-[var(--muted)] border border-[var(--border)]">
                    @{{ form.variants.length }} Variant Types
                </span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: GENERAL (Basic Info + Group/Attr DND) -->
        <!-- ========================================== -->
        <div v-show="activeMainTab === 'general'" class="space-y-6">
            <!-- General Info Card -->
            <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--border)] pb-3">
                    <h2 class="text-sm font-bold text-[var(--text)] flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                        Basic Information
                    </h2>
                    <div class="flex items-center gap-3 text-xs text-[var(--muted)]">
                        <span>Groups: <strong class="text-[var(--text)]">@{{ form.groups.length }}</strong></span>
                        <span>•</span>
                        <span>Assigned Attributes: <strong class="text-[var(--text)]">@{{ totalAssignedAttributesCount }}</strong></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div class="space-y-1.5">
                        <label for="pt-name" class="block text-xs font-semibold text-[var(--text)]">
                            Name <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="pt-name"
                            type="text"
                            v-model="form.name"
                            @input="onNameInput"
                            placeholder="e.g. Default, Mobile Phones, Clothing"
                            required
                            maxlength="120"
                            class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                            :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500': errors.name }"
                        />
                        <p v-if="errors.name" class="text-[11px] text-rose-500 font-medium">@{{ errors.name }}</p>
                    </div>

                    <!-- Code -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="pt-code" class="block text-xs font-semibold text-[var(--text)]">
                                Code <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] font-mono text-[var(--muted)]">Unique slug</span>
                        </div>
                        <input
                            id="pt-code"
                            type="text"
                            v-model="form.code"
                            @input="codeTouched = true"
                            placeholder="e.g. default, mobile_phones, clothing"
                            required
                            maxlength="120"
                            class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] font-mono text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                            :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500': errors.code }"
                        />
                        <p v-if="errors.code" class="text-[11px] text-rose-500 font-medium">@{{ errors.code }}</p>
                    </div>
                </div>
            </div>

            <!-- 2-Column Drag and Drop Builder Workspace -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Left Column: Attribute Groups (7 cols) -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[var(--border)]">
                        <div class="flex items-center gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                            </svg>
                            <h2 class="text-sm font-bold text-[var(--text)]">Attribute Groups</h2>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--soft)] text-[var(--text)] border border-[var(--border)]">
                                @{{ form.groups.length }}
                            </span>
                        </div>

                        <button
                            type="button"
                            @click="openAddGroupModal"
                            class="button button--secondary text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            <span>Add Group</span>
                        </button>
                    </div>

                    <!-- Empty Groups State -->
                    <div
                        v-if="form.groups.length === 0"
                        class="p-8 text-center rounded-xl border border-dashed border-[var(--border)] bg-[var(--card)] space-y-3"
                    >
                        <div class="inline-flex p-3 rounded-xl bg-[var(--soft)] text-[var(--muted)]">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-[var(--text)]">No attribute groups configured</h3>
                            <p class="text-[11px] text-[var(--muted)] mt-0.5">Click "Add Group" to add groups like General, Description, or Money.</p>
                        </div>
                        <button
                            type="button"
                            @click="openAddGroupModal"
                            class="button button--primary text-xs inline-flex items-center gap-1.5"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            <span>Add First Group</span>
                        </button>
                    </div>

                    <!-- Groups List -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="(group, gIndex) in form.groups"
                            :key="group.attribute_group_id"
                            class="rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs transition-all duration-150"
                            :class="{
                                'ring-2 ring-[var(--teal)] border-transparent': dropTargetGroupIndex === gIndex,
                                'opacity-40': dragType === 'group' && draggedGroupIndex === gIndex
                            }"
                            @dragover.prevent="onGroupDragOver($event, gIndex)"
                            @drop="onGroupDrop($event, gIndex)"
                        >
                            <!-- Group Header -->
                            <div
                                class="p-3.5 flex items-center justify-between border-b border-[var(--border)] bg-[var(--card)] select-none rounded-t-xl"
                                draggable="true"
                                @dragstart="onGroupDragStart($event, gIndex)"
                                @dragend="onDragEnd"
                            >
                                <div class="flex items-center gap-2.5">
                                    <!-- Grip Drag Handle -->
                                    <div class="cursor-grab active:cursor-grabbing text-[var(--muted)] hover:text-[var(--text)] p-1 -ml-1 transition-colors" title="Drag to reorder group">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                                            <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                            <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                                        </svg>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-[var(--text)]">@{{ group.name }}</span>
                                        <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-[var(--soft)] text-[var(--muted)] border border-[var(--border)]">
                                            @{{ group.code }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-medium text-[var(--muted)] px-2 py-0.5 rounded-full bg-[var(--page)] border border-[var(--border)]">
                                        @{{ group.attributes.length }} @{{ group.attributes.length === 1 ? 'attribute' : 'attributes' }}
                                    </span>

                                    <!-- Move Up -->
                                    <button
                                        type="button"
                                        @click="moveGroup(gIndex, -1)"
                                        :disabled="gIndex === 0"
                                        class="p-1 rounded text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--soft)] disabled:opacity-20 cursor-pointer"
                                        title="Move group up"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                                    </button>

                                    <!-- Move Down -->
                                    <button
                                        type="button"
                                        @click="moveGroup(gIndex, 1)"
                                        :disabled="gIndex === form.groups.length - 1"
                                        class="p-1 rounded text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--soft)] disabled:opacity-20 cursor-pointer"
                                        title="Move group down"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>

                                    <!-- Remove Group -->
                                    <button
                                        type="button"
                                        @click="removeGroup(gIndex)"
                                        class="p-1 rounded text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer transition-colors ml-1"
                                        title="Remove this group and unassign its attributes"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Assigned Attributes Drop Zone -->
                            <div
                                class="p-3 space-y-2 min-h-[68px] transition-colors"
                                :class="{
                                    'bg-[var(--teal)]/5': dropTargetGroupIndex === gIndex
                                }"
                                @dragover.prevent="onAttrDragOver($event, gIndex)"
                                @dragleave="onAttrDragLeave($event, gIndex)"
                                @drop="onAttrDrop($event, gIndex)"
                            >
                                <!-- Assigned Attributes List -->
                                <div
                                    v-for="(attr, aIndex) in group.attributes"
                                    :key="attr.id"
                                    draggable="true"
                                    @dragstart="onAttrDragStart($event, attr, gIndex, aIndex)"
                                    @dragend="onDragEnd"
                                    class="p-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] flex items-center justify-between gap-3 text-xs shadow-2xs hover:border-[var(--teal)] transition-all cursor-grab active:cursor-grabbing select-none"
                                    :class="{
                                        'opacity-40': dragType === 'attribute' && draggedAttr && draggedAttr.id === attr.id
                                    }"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="text-[var(--muted)] hover:text-[var(--text)]">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                                                <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                                <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-semibold text-[var(--text)] truncate">@{{ attr.name }}</span>
                                                <span v-if="attr.required" class="text-rose-500 font-bold" title="Required attribute">*</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="font-mono text-[10px] text-[var(--muted)]">@{{ attr.code }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-[10px] px-2 py-0.5 rounded font-mono font-medium bg-[var(--soft)] text-[var(--muted)] uppercase border border-[var(--border)]">
                                            @{{ attr.type }}
                                        </span>

                                        <!-- Unassign Button -->
                                        <button
                                            type="button"
                                            @click="unassignAttribute(gIndex, aIndex)"
                                            class="p-1 rounded text-[var(--muted)] hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer transition-colors"
                                            title="Unassign attribute"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Empty Group Drop Placeholder -->
                                <div
                                    v-if="group.attributes.length === 0"
                                    class="py-4 px-3 text-center rounded-lg border border-dashed border-[var(--border)] text-[var(--muted)] text-[11px] bg-[var(--card)] flex flex-col items-center justify-center gap-1"
                                >
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="opacity-50">
                                        <path d="M12 5v14M5 12h14"/>
                                    </svg>
                                    <span>Drop attributes here from the available list</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Available Attributes Pool (5 cols) -->
                <div class="lg:col-span-5 sticky top-20 space-y-4">
                    <div class="p-4 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs space-y-4">
                        <!-- Pool Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                            <div class="flex items-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                <h2 class="text-sm font-bold text-[var(--text)]">Available Attributes</h2>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--soft)] text-[var(--text)] border border-[var(--border)]">
                                @{{ unassignedAttributes.length }} available
                            </span>
                        </div>

                        <!-- Search & Filter Controls -->
                        <div class="space-y-2">
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="unassignedSearch"
                                    placeholder="Search attributes..."
                                    class="w-full h-8 pl-8 pr-3 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] transition-all"
                                />
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-2.5 top-2.5 text-[var(--muted)]">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                            </div>

                            <!-- Filter by Type -->
                            <div v-if="attributeTypes.length > 1" class="flex flex-wrap gap-1">
                                <button
                                    type="button"
                                    @click="selectedTypeFilter = ''"
                                    class="px-2 py-0.5 rounded text-[10px] font-medium border transition-colors cursor-pointer"
                                    :class="selectedTypeFilter === '' ? 'bg-[var(--teal)] text-white border-[var(--teal)]' : 'bg-[var(--page)] text-[var(--muted)] border-[var(--border)] hover:text-[var(--text)]'"
                                >
                                    All (@{{ totalUnassignedCount }})
                                </button>
                                <button
                                    v-for="type in attributeTypes"
                                    :key="type"
                                    type="button"
                                    @click="selectedTypeFilter = type"
                                    class="px-2 py-0.5 rounded text-[10px] font-medium uppercase border transition-colors cursor-pointer"
                                    :class="selectedTypeFilter === type ? 'bg-[var(--teal)] text-white border-[var(--teal)]' : 'bg-[var(--page)] text-[var(--muted)] border-[var(--border)] hover:text-[var(--text)]'"
                                >
                                    @{{ type }}
                                </button>
                            </div>
                        </div>

                        <!-- Available Attributes Draggable Pool -->
                        <div
                            class="space-y-2 max-h-[520px] overflow-y-auto pr-1"
                            @dragover.prevent
                            @drop="onDropToPool($event)"
                        >
                            <div
                                v-for="attr in filteredUnassignedAttributes"
                                :key="attr.id"
                                draggable="true"
                                @dragstart="onPoolAttrDragStart($event, attr)"
                                @dragend="onDragEnd"
                                class="p-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] flex items-center justify-between gap-2.5 text-xs shadow-2xs hover:border-[var(--teal)] transition-all cursor-grab active:cursor-grabbing select-none"
                                :class="{
                                    'opacity-40': dragType === 'attribute' && draggedAttr && draggedAttr.id === attr.id
                                }"
                            >
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="text-[var(--muted)]">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                                            <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                            <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-semibold text-[var(--text)] truncate">@{{ attr.name }}</span>
                                            <span v-if="attr.required" class="text-rose-500 font-bold" title="Required attribute">*</span>
                                        </div>
                                        <span class="font-mono text-[10px] text-[var(--muted)]">@{{ attr.code }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-medium bg-[var(--soft)] text-[var(--muted)] uppercase border border-[var(--border)]">
                                        @{{ attr.type }}
                                    </span>

                                    <!-- Quick Assign Button -->
                                    <button
                                        v-if="form.groups.length > 0"
                                        type="button"
                                        @click="quickAssignAttribute(attr)"
                                        class="p-1 rounded text-[var(--teal)] hover:bg-[var(--teal)]/10 cursor-pointer transition-colors"
                                        :title="'Assign to ' + (form.groups[0]?.name || 'group')"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <line x1="12" y1="5" x2="12" y2="19"/>
                                            <line x1="5" y1="12" x2="19" y2="12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Empty Pool State -->
                            <div
                                v-if="filteredUnassignedAttributes.length === 0"
                                class="p-6 text-center rounded-lg border border-dashed border-[var(--border)] text-[var(--muted)] text-xs space-y-1"
                            >
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto opacity-40">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <p class="font-medium text-[var(--text)]">
                                    @{{ unassignedSearch ? 'No attributes match your search' : 'All attributes are assigned!' }}
                                </p>
                                <p class="text-[11px] text-[var(--muted)]">
                                    @{{ unassignedSearch ? 'Try a different search term or filter' : 'Drag attributes back here to unassign them' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- TAB 2: VARIANT TYPES (1-Level & 2-Level Profiles)      -->
        <!-- ====================================================== -->
        <div v-show="activeMainTab === 'variants'" class="space-y-6">
            <!-- Header with Add Variant Type Button -->
            <div class="flex items-center justify-between pb-2 border-b border-[var(--border)]">
                <div class="flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]">
                        <circle cx="18" cy="18" r="3"/>
                        <circle cx="6" cy="6" r="3"/>
                        <path d="M6 21V9a9 9 0 0 0 9 9"/>
                    </svg>
                    <h2 class="text-sm font-bold text-[var(--text)]">Variant Types</h2>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--soft)] text-[var(--text)] border border-[var(--border)]">
                        @{{ form.variants.length }}
                    </span>
                </div>

                <button
                    type="button"
                    @click="openVariantModal()"
                    class="button button--primary text-xs inline-flex items-center gap-1.5 shrink-0 cursor-pointer shadow-xs"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    <span>Add Variant Type</span>
                </button>
            </div>

            <!-- Empty Variant Types State -->
            <div
                v-if="form.variants.length === 0"
                class="p-12 text-center rounded-xl border border-dashed border-[var(--border)] bg-[var(--card)] space-y-4"
            >
                <div class="inline-flex p-3 rounded-2xl bg-[var(--soft)] text-[var(--muted)]">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="18" cy="18" r="3"/>
                        <circle cx="6" cy="6" r="3"/>
                        <path d="M6 21V9a9 9 0 0 0 9 9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[var(--text)]">No Variant Types Configured</h3>
                    <p class="text-xs text-[var(--muted)] mt-1 max-w-md mx-auto">
                        Click "Add Variant Type" to configure variant types for configurable products.
                    </p>
                </div>
                <button
                    type="button"
                    @click="openVariantModal()"
                    class="button button--primary text-xs inline-flex items-center gap-1.5"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    <span>Add First Variant Type</span>
                </button>
            </div>

            <!-- Configured Variant Types List -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="(variant, vIndex) in form.variants"
                    :key="variant.code || vIndex"
                    class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs space-y-4 hover:border-[var(--teal)]/50 transition-all"
                >
                    <div class="flex items-start justify-between gap-3 border-b border-[var(--border)] pb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-[var(--text)]">@{{ variant.name }}</h3>
                                <span
                                    class="text-[10px] px-2 py-0.5 rounded-full font-semibold border"
                                    :class="variant.level_count === 1 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800'"
                                >
                                    @{{ variant.level_count === 1 ? '1-Level Variant' : '2-Level Variant' }}
                                </span>
                            </div>
                            <span class="font-mono text-[11px] text-[var(--muted)] mt-0.5 block">@{{ variant.code }}</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                @click="openVariantModal(vIndex)"
                                class="p-1.5 rounded text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--soft)] cursor-pointer transition-colors"
                                title="Edit Variant Type"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                </svg>
                            </button>
                            <button
                                type="button"
                                @click="removeVariantType(vIndex)"
                                class="p-1.5 rounded text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer transition-colors"
                                title="Delete Variant Type"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Level 1 Display -->
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-semibold text-[var(--muted)] uppercase tracking-wider block">
                            @{{ variant.level_count === 1 ? 'Variant Axes (Direct Variants)' : 'Level 1: Variant Group (Sub-parent)' }}
                        </span>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="attr in variant.level_1_attributes"
                                :key="attr.id"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-[var(--page)] border border-[var(--border)] text-[var(--text)]"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-[var(--teal)]"></span>
                                <span>@{{ attr.name }}</span>
                                <span class="font-mono text-[10px] text-[var(--muted)]">(@{{ attr.code }})</span>
                            </span>
                            <span v-if="!variant.level_1_attributes || variant.level_1_attributes.length === 0" class="text-xs text-[var(--muted)] italic">
                                No attributes assigned to Level 1
                            </span>
                        </div>
                    </div>

                    <!-- Level 2 Display (if 2-level) -->
                    <div v-if="variant.level_count === 2" class="space-y-1.5 pt-2 border-t border-[var(--border)]/60">
                        <span class="text-[11px] font-semibold text-[var(--muted)] uppercase tracking-wider block">
                            Level 2: Final Variant (Leaf SKU Level)
                        </span>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="attr in variant.level_2_attributes"
                                :key="attr.id"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-[var(--page)] border border-[var(--border)] text-[var(--text)]"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                <span>@{{ attr.name }}</span>
                                <span class="font-mono text-[10px] text-[var(--muted)]">(@{{ attr.code }})</span>
                            </span>
                            <span v-if="!variant.level_2_attributes || variant.level_2_attributes.length === 0" class="text-xs text-[var(--muted)] italic">
                                No attributes assigned to Level 2
                            </span>
                        </div>
                    </div>

                    <!-- Variant-Level Attributes Display -->
                    <div class="space-y-1.5 pt-2 border-t border-[var(--border)]/60">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-[var(--muted)] uppercase tracking-wider block">
                                Variant-Level Attributes (Fillable on child variants)
                            </span>
                            <span class="text-[10px] font-mono text-[var(--muted)]">
                                @{{ (variant.variant_attributes || []).length }} configured
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="attr in variant.variant_attributes"
                                :key="attr.id"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-500/10 border border-indigo-500/20 text-indigo-400"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                <span>@{{ attr.name }}</span>
                                <span class="font-mono text-[10px] opacity-75">(@{{ attr.code }})</span>
                            </span>
                            <span v-if="!variant.variant_attributes || variant.variant_attributes.length === 0" class="text-xs text-[var(--muted)] italic">
                                None configured (axes only)
                            </span>
                        </div>
                    </div>

                    <!-- Parent-Level Attributes (Remaining family attributes) -->
                    <div class="space-y-1.5 pt-2 border-t border-[var(--border)]/60">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-[var(--muted)] uppercase tracking-wider block">
                                Parent-Level Attributes (Shared across all variants)
                            </span>
                            <span class="text-[10px] font-mono text-[var(--muted)]">
                                @{{ getParentAttributesForVariant(variant).length }} attributes
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="attr in getParentAttributesForVariant(variant)"
                                :key="attr.id"
                                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-[var(--soft)] border border-[var(--border)] text-[var(--muted)]"
                            >
                                <span>@{{ attr.name }}</span>
                            </span>
                            <span v-if="getParentAttributesForVariant(variant).length === 0" class="text-xs text-[var(--muted)] italic">
                                All family attributes are assigned to variant level.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: ADD / EDIT VARIANT TYPE             -->
        <!-- ========================================== -->
        <div
            v-if="showVariantModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
            @click.self="showVariantModal = false"
        >
            <div class="w-full max-w-4xl max-h-[calc(100vh-2rem)] flex flex-col rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 sm:p-6 shadow-2xl space-y-4 sm:space-y-5 animate-in fade-in zoom-in-95 duration-150 my-auto overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-[var(--border)] pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-[var(--teal)]/10 text-[var(--teal)]">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="18" cy="18" r="3"/>
                                <circle cx="6" cy="6" r="3"/>
                                <path d="M6 21V9a9 9 0 0 0 9 9"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[var(--text)]">
                                @{{ editingVariantIndex === null ? 'Add Variant Type Profile' : 'Edit Variant Type Profile' }}
                            </h3>
                            <p class="text-[11px] text-[var(--muted)]">
                                Configure variant axes for 1-level or 2-level product variation.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="showVariantModal = false"
                        class="text-[var(--muted)] hover:text-[var(--text)] text-lg leading-none cursor-pointer"
                    >
                        &times;
                    </button>
                </div>

                <!-- Modal Body: Name, Code & Level Toggle -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Variant Name -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[var(--text)]">
                            Variant Type Name <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="variantModalForm.name"
                            @input="onVariantModalNameInput"
                            placeholder="e.g. Color & Size (1 Level)"
                            class="w-full h-9 px-3 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)]"
                        />
                    </div>

                    <!-- Variant Code -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-[var(--text)]">
                                Code <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] font-mono text-[var(--muted)]">Unique slug</span>
                        </div>
                        <input
                            type="text"
                            v-model="variantModalForm.code"
                            @input="variantModalCodeTouched = true"
                            placeholder="e.g. color_size_1level"
                            class="w-full h-9 px-3 rounded-xl border border-[var(--border)] bg-[var(--page)] font-mono text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)]"
                        />
                    </div>

                    <!-- Level Selector Toggle -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[var(--text)]">Variation Structure</label>
                        <div class="grid grid-cols-2 gap-1.5 p-1 rounded-xl bg-[var(--page)] border border-[var(--border)] h-9">
                            <button
                                type="button"
                                @click="variantModalForm.level_count = 1"
                                class="text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
                                :class="variantModalForm.level_count === 1 ? 'bg-[var(--teal)] text-white shadow-xs' : 'text-[var(--muted)] hover:text-[var(--text)]'"
                            >
                                <span>1 Level</span>
                            </button>
                            <button
                                type="button"
                                @click="variantModalForm.level_count = 2"
                                class="text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
                                :class="variantModalForm.level_count === 2 ? 'bg-[var(--teal)] text-white shadow-xs' : 'text-[var(--muted)] hover:text-[var(--text)]'"
                            >
                                <span>2 Levels</span>
                            </button>
                        </div>
                    </div>
                </div>

                <p v-if="variantModalError" class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-xs font-medium">
                    @{{ variantModalError }}
                </p>

                <!-- Workspace: Left Target Drop Zones vs Right Available Select Attributes -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 pt-2 border-t border-[var(--border)] items-start">
                    <!-- Left: Drop Zones for Level 1 & Level 2 (7 cols) -->
                    <div class="lg:col-span-7 space-y-4">
                        <!-- Level 1 Zone -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-[var(--text)] flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[var(--teal)]"></span>
                                    <span>@{{ variantModalForm.level_count === 1 ? 'Level 1: Variant Axes (Direct Variants)' : 'Level 1: Variant Group (Sub-parent axes)' }}</span>
                                </label>
                                <span class="text-[10px] text-[var(--muted)]">
                                    @{{ variantModalForm.level_1_attributes.length }} assigned
                                </span>
                            </div>

                            <div
                                class="p-3 rounded-xl border border-[var(--border)] bg-[var(--page)] min-h-[90px] space-y-2 transition-all"
                                :class="{ 'ring-2 ring-[var(--teal)] bg-[var(--teal)]/5': dropTargetLevel === 1 }"
                                @dragover.prevent="dropTargetLevel = 1"
                                @dragleave="dropTargetLevel = null"
                                @drop="onModalAttrDrop($event, 1)"
                            >
                                <div
                                    v-for="(attr, aIdx) in variantModalForm.level_1_attributes"
                                    :key="attr.id"
                                    class="p-2 rounded-lg border border-[var(--border)] bg-[var(--card)] flex items-center justify-between gap-2 text-xs shadow-2xs select-none"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="font-bold text-[var(--text)] truncate">@{{ attr.name }}</span>
                                        <span class="font-mono text-[10px] text-[var(--muted)]">@{{ attr.code }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-medium bg-[var(--soft)] text-[var(--muted)] uppercase border border-[var(--border)]">
                                            @{{ attr.type }}
                                        </span>
                                        <button
                                            type="button"
                                            @click="removeModalAttr(1, aIdx)"
                                            class="p-1 rounded text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer"
                                            title="Remove attribute"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    v-if="variantModalForm.level_1_attributes.length === 0"
                                    class="py-5 text-center text-[var(--muted)] text-xs flex flex-col items-center justify-center gap-1 border border-dashed border-[var(--border)] rounded-lg"
                                >
                                    <span>Drag select attributes here or click "+ Level 1"</span>
                                </div>
                            </div>
                        </div>

                        <!-- Level 2 Zone (Only for 2-Level Variation) -->
                        <div v-if="variantModalForm.level_count === 2" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-[var(--text)] flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    <span>Level 2: Final Variant (Leaf SKU axes)</span>
                                </label>
                                <span class="text-[10px] text-[var(--muted)]">
                                    @{{ variantModalForm.level_2_attributes.length }} assigned
                                </span>
                            </div>

                            <div
                                class="p-3 rounded-xl border border-[var(--border)] bg-[var(--page)] min-h-[90px] space-y-2 transition-all"
                                :class="{ 'ring-2 ring-indigo-500 bg-indigo-500/5': dropTargetLevel === 2 }"
                                @dragover.prevent="dropTargetLevel = 2"
                                @dragleave="dropTargetLevel = null"
                                @drop="onModalAttrDrop($event, 2)"
                            >
                                <div
                                    v-for="(attr, aIdx) in variantModalForm.level_2_attributes"
                                    :key="attr.id"
                                    class="p-2 rounded-lg border border-[var(--border)] bg-[var(--card)] flex items-center justify-between gap-2 text-xs shadow-2xs select-none"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="font-bold text-[var(--text)] truncate">@{{ attr.name }}</span>
                                        <span class="font-mono text-[10px] text-[var(--muted)]">@{{ attr.code }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-medium bg-[var(--soft)] text-[var(--muted)] uppercase border border-[var(--border)]">
                                            @{{ attr.type }}
                                        </span>
                                        <button
                                            type="button"
                                            @click="removeModalAttr(2, aIdx)"
                                            class="p-1 rounded text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer"
                                            title="Remove attribute"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    v-if="variantModalForm.level_2_attributes.length === 0"
                                    class="py-5 text-center text-[var(--muted)] text-xs flex flex-col items-center justify-center gap-1 border border-dashed border-[var(--border)] rounded-lg"
                                >
                                    <span>Drag select attributes here or click "+ Level 2"</span>
                                </div>
                            </div>
                        </div>

                        <!-- Variant-Level Attributes Zone (Fillable on child variants) -->
                        <div class="space-y-2 pt-2 border-t border-[var(--border)]/60">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-[var(--text)] flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    <span>Variant-Level Attributes (Fillable on Child Variants)</span>
                                </label>
                                <span class="text-[10px] text-[var(--muted)]">
                                    @{{ variantModalForm.variant_attributes.length }} assigned
                                </span>
                            </div>
                            <p class="text-[11px] text-[var(--muted)]">
                                Attributes customized per child variant (e.g. Price, Weight). All other family attributes remain at Parent Level.
                            </p>

                            <div
                                class="p-3 rounded-xl border border-[var(--border)] bg-[var(--page)] min-h-[90px] space-y-2 transition-all"
                                :class="{ 'ring-2 ring-indigo-500 bg-indigo-500/5': dropTargetLevel === 'variant' }"
                                @dragover.prevent="dropTargetLevel = 'variant'"
                                @dragleave="dropTargetLevel = null"
                                @drop="onModalAttrDrop($event, 'variant')"
                            >
                                <div
                                    v-for="(attr, aIdx) in variantModalForm.variant_attributes"
                                    :key="attr.id"
                                    class="p-2 rounded-lg border border-[var(--border)] bg-[var(--card)] flex items-center justify-between gap-2 text-xs shadow-2xs select-none"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="font-bold text-[var(--text)] truncate">@{{ attr.name }}</span>
                                        <span class="font-mono text-[10px] text-[var(--muted)]">@{{ attr.code }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-medium bg-[var(--soft)] text-[var(--muted)] uppercase border border-[var(--border)]">
                                            @{{ attr.type }}
                                        </span>
                                        <button
                                            type="button"
                                            @click="removeModalAttr('variant', aIdx)"
                                            class="p-1 rounded text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer"
                                            title="Remove attribute"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    v-if="variantModalForm.variant_attributes.length === 0"
                                    class="py-5 text-center text-[var(--muted)] text-xs flex flex-col items-center justify-center gap-1 border border-dashed border-[var(--border)] rounded-lg"
                                >
                                    <span>Drag attributes here or click "+ Variant Attr" below</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Available Attributes Pool (5 cols) -->
                    <div class="lg:col-span-5 space-y-3">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <label class="text-xs font-bold text-[var(--text)]">Attributes Pool</label>
                            <!-- Tab selector: Axes vs Family -->
                            <div class="flex items-center gap-1 p-0.5 rounded-lg bg-[var(--page)] border border-[var(--border)] text-[10px]">
                                <button
                                    type="button"
                                    @click="modalAttrPoolTab = 'axes'"
                                    class="px-2 py-0.5 rounded font-medium transition-colors cursor-pointer"
                                    :class="modalAttrPoolTab === 'axes' ? 'bg-[var(--card)] text-[var(--teal)] font-bold shadow-2xs' : 'text-[var(--muted)] hover:text-[var(--text)]'"
                                >
                                    Axes (Select)
                                </button>
                                <button
                                    type="button"
                                    @click="modalAttrPoolTab = 'family'"
                                    class="px-2 py-0.5 rounded font-medium transition-colors cursor-pointer"
                                    :class="modalAttrPoolTab === 'family' ? 'bg-[var(--card)] text-[var(--teal)] font-bold shadow-2xs' : 'text-[var(--muted)] hover:text-[var(--text)]'"
                                >
                                    Family Attrs
                                </button>
                                <button
                                    type="button"
                                    @click="modalAttrPoolTab = 'all'"
                                    class="px-2 py-0.5 rounded font-medium transition-colors cursor-pointer"
                                    :class="modalAttrPoolTab === 'all' ? 'bg-[var(--card)] text-[var(--teal)] font-bold shadow-2xs' : 'text-[var(--muted)] hover:text-[var(--text)]'"
                                >
                                    All
                                </button>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="relative">
                            <input
                                type="text"
                                v-model="modalAttrSearch"
                                placeholder="Search attributes..."
                                class="w-full h-8 pl-8 pr-3 rounded-lg border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] transition-all"
                            />
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-2.5 top-2.5 text-[var(--muted)]">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </div>

                        <!-- List of Attributes -->
                        <div class="space-y-1.5 max-h-[380px] overflow-y-auto pr-1">
                            <div
                                v-for="attr in filteredModalAttributes"
                                :key="attr.id"
                                draggable="true"
                                @dragstart="onModalDragStart($event, attr)"
                                class="p-2 rounded-lg border border-[var(--border)] bg-[var(--page)] flex items-center justify-between gap-2 text-xs shadow-2xs hover:border-[var(--teal)] transition-all cursor-grab active:cursor-grabbing select-none"
                            >
                                <div class="min-w-0">
                                    <div class="font-semibold text-[var(--text)] truncate">@{{ attr.name }}</div>
                                    <span class="font-mono text-[10px] text-[var(--muted)]">@{{ attr.code }}</span>
                                </div>

                                <div class="flex items-center gap-1 shrink-0 flex-wrap justify-end">
                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-mono font-medium bg-[var(--soft)] text-[var(--muted)] uppercase border border-[var(--border)]">
                                        @{{ attr.type }}
                                    </span>

                                    <!-- If select/radio/checkbox: Can add as Axis -->
                                    <template v-if="['select', 'multiselect', 'radio', 'checkbox'].includes(attr.type)">
                                        <!-- If 1 level -->
                                        <button
                                            v-if="variantModalForm.level_count === 1"
                                            type="button"
                                            @click="addAttrToModalLevel(attr, 1)"
                                            class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[var(--teal)] text-white hover:opacity-90 cursor-pointer"
                                            title="Add as Variation Axis"
                                        >
                                            + Axis
                                        </button>

                                        <!-- If 2 levels -->
                                        <div v-else class="flex items-center gap-1">
                                            <button
                                                type="button"
                                                @click="addAttrToModalLevel(attr, 1)"
                                                class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[var(--teal)] text-white hover:opacity-90 cursor-pointer"
                                                title="Add to Level 1 (Variant Group)"
                                            >
                                                + L1
                                            </button>
                                            <button
                                                type="button"
                                                @click="addAttrToModalLevel(attr, 2)"
                                                class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-600 text-white hover:opacity-90 cursor-pointer"
                                                title="Add to Level 2 (Final Variant)"
                                            >
                                                + L2
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Add as Variant-Level Attribute button -->
                                    <button
                                        type="button"
                                        @click="addAttrToModalVariant(attr)"
                                        class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 text-indigo-500 border border-indigo-500/20 hover:bg-indigo-500 hover:text-white cursor-pointer transition-colors"
                                        title="Add as Variant-Level Attribute (fillable per child variant)"
                                    >
                                        + Variant
                                    </button>
                                </div>
                            </div>

                            <div v-if="filteredModalAttributes.length === 0" class="p-4 text-center text-xs text-[var(--muted)]">
                                No matching attributes found.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[var(--border)] shrink-0">
                    <button
                        type="button"
                        @click="showVariantModal = false"
                        class="button button--outline text-xs flex-1 sm:flex-initial text-center justify-center"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="saveVariantModal"
                        class="button button--primary text-xs flex-1 sm:flex-initial text-center justify-center"
                    >
                        Save Variant Type Profile
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Group Modal -->
        <div
            v-if="showAddGroupModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/50 backdrop-blur-xs overflow-y-auto"
            @click.self="showAddGroupModal = false"
        >
            <div class="w-full max-w-md max-h-[calc(100vh-2rem)] flex flex-col rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 sm:p-6 shadow-xl space-y-4 sm:space-y-5 animate-in fade-in zoom-in-95 duration-150 my-auto overflow-y-auto">
                <div class="flex items-center justify-between border-b border-[var(--border)] pb-3">
                    <h3 class="text-sm font-bold text-[var(--text)] flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                        </svg>
                        Add Attribute Group
                    </h3>
                    <button
                        type="button"
                        @click="showAddGroupModal = false"
                        class="text-[var(--muted)] hover:text-[var(--text)] text-lg leading-none cursor-pointer"
                    >
                        &times;
                    </button>
                </div>

                <!-- Tabs: Existing vs New -->
                <div class="flex border-b border-[var(--border)] gap-4 text-xs font-semibold">
                    <button
                        type="button"
                        @click="modalTab = 'existing'"
                        class="pb-2 transition-colors cursor-pointer"
                        :class="modalTab === 'existing' ? 'border-b-2 border-[var(--teal)] text-[var(--teal)]' : 'text-[var(--muted)] hover:text-[var(--text)]'"
                    >
                        Select Existing Group
                    </button>
                    <button
                        type="button"
                        @click="modalTab = 'new'"
                        class="pb-2 transition-colors cursor-pointer"
                        :class="modalTab === 'new' ? 'border-b-2 border-[var(--teal)] text-[var(--teal)]' : 'text-[var(--muted)] hover:text-[var(--text)]'"
                    >
                        Create New Group
                    </button>
                </div>

                <!-- Existing Group Tab -->
                <div v-if="modalTab === 'existing'" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[var(--text)]">Select Group</label>
                        <select
                            v-model="selectedExistingGroupId"
                            class="w-full h-10 px-3 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                        >
                            <option :value="null" disabled>Choose an attribute group...</option>
                            <option
                                v-for="g in unaddedAttributeGroups"
                                :key="g.id"
                                :value="g.id"
                            >
                                @{{ g.name }} (@{{ g.code }})
                            </option>
                        </select>
                        <p v-if="unaddedAttributeGroups.length === 0" class="text-[11px] text-amber-500 font-medium">
                            All existing attribute groups have already been added to this product type.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="showAddGroupModal = false"
                            class="button button--outline text-xs flex-1 sm:flex-initial text-center justify-center"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="addSelectedExistingGroup"
                            :disabled="!selectedExistingGroupId"
                            class="button button--primary text-xs disabled:opacity-50 flex-1 sm:flex-initial text-center justify-center"
                        >
                            Add to Product Type
                        </button>
                    </div>
                </div>

                <!-- Create New Group Tab -->
                <div v-if="modalTab === 'new'" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[var(--text)]">
                            Group Name <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="newGroupForm.name"
                            @input="onNewGroupNameInput"
                            placeholder="e.g. Technical Specifications"
                            class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)]"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-[var(--text)]">
                                Group Code <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] font-mono text-[var(--muted)]">Unique slug</span>
                        </div>
                        <input
                            type="text"
                            v-model="newGroupForm.code"
                            @input="newGroupCodeTouched = true"
                            placeholder="e.g. technical_specifications"
                            class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] font-mono text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)]"
                        />
                    </div>

                    <p v-if="newGroupError" class="text-[11px] text-rose-500 font-medium">@{{ newGroupError }}</p>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="showAddGroupModal = false"
                            class="button button--outline text-xs flex-1 sm:flex-initial text-center justify-center"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="createNewGroupAndAdd"
                            :disabled="creatingNewGroup"
                            class="button button--primary text-xs disabled:opacity-50 inline-flex items-center justify-center gap-1.5 flex-1 sm:flex-initial"
                        >
                            <svg v-if="creatingNewGroup" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>@{{ creatingNewGroup ? 'Creating...' : 'Create & Add Group' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<script>
(function() {
    const ProductTypeBuilderComponent = {
        name: 'v-product-type-builder',
        template: '#v-product-type-builder-template',
        props: {
            mode: {
                type: String,
                default: 'create' // 'create' | 'edit'
            },
            initialType: {
                type: Object,
                default: () => null
            },
            allGroups: {
                type: Array,
                default: () => []
            },
            allAttributes: {
                type: Array,
                default: () => []
            },
            selectAttributes: {
                type: Array,
                default: () => []
            },
            backUrl: {
                type: String,
                default: '/catalog/product-types'
            }
        },
        data() {
            // Parse initial groups
            const parsedGroups = [];
            if (this.initialType && Array.isArray(this.initialType.groups)) {
                this.initialType.groups.forEach((g) => {
                    parsedGroups.push({
                        attribute_group_id: g.attribute_group_id || g.id,
                        name: g.name,
                        code: g.code,
                        position: g.position || (parsedGroups.length + 1),
                        attributes: Array.isArray(g.attributes) ? [...g.attributes] : []
                    });
                });
            }

            // Parse initial multi-level variants
            const parsedVariants = [];
            if (this.initialType && Array.isArray(this.initialType.variants)) {
                this.initialType.variants.forEach((v) => {
                    parsedVariants.push({
                        id: v.id,
                        name: v.name,
                        code: v.code,
                        level_count: parseInt(v.level_count, 10) === 2 ? 2 : 1,
                        level_1_attributes: Array.isArray(v.level_1_attributes) ? [...v.level_1_attributes] : [],
                        level_2_attributes: Array.isArray(v.level_2_attributes) ? [...v.level_2_attributes] : [],
                        variant_attributes: Array.isArray(v.variant_attributes) ? [...v.variant_attributes] : []
                    });
                });
            }

            return {
                // Active Tab: 'general' | 'variants'
                activeMainTab: 'general',

                form: {
                    name: this.initialType?.name || '',
                    code: this.initialType?.code || '',
                    groups: parsedGroups,
                    variants: parsedVariants
                },
                codeTouched: Boolean(this.initialType?.code),
                availableGroupsList: [...(this.allGroups || [])],
                availableAttributesList: [...(this.allAttributes || [])],
                availableSelectAttributesList: [...(this.selectAttributes || [])],

                // Filtering General Attributes Pool
                unassignedSearch: '',
                selectedTypeFilter: '',

                // Drag State for General Tab
                dragType: null, // 'group' | 'attribute'
                draggedGroupIndex: null,
                draggedAttr: null,
                draggedFromGroupIndex: null,
                draggedFromAttrIndex: null,
                dropTargetGroupIndex: null,

                // Modal for Variant Type (Add / Edit)
                showVariantModal: false,
                editingVariantIndex: null,
                modalAttrPoolTab: 'family', // 'axes' | 'family' | 'all'
                variantModalForm: {
                    name: '',
                    code: '',
                    level_count: 1, // 1 or 2
                    level_1_attributes: [],
                    level_2_attributes: [],
                    variant_attributes: []
                },
                variantModalCodeTouched: false,
                variantModalError: null,
                modalAttrSearch: '',
                dropTargetLevel: null,
                draggedModalAttr: null,

                // Modal for Attribute Groups
                showAddGroupModal: false,
                modalTab: 'existing', // 'existing' | 'new'
                selectedExistingGroupId: null,
                newGroupForm: { name: '', code: '' },
                newGroupCodeTouched: false,
                newGroupError: null,
                creatingNewGroup: false,

                // Status
                errors: {},
                errorMessage: null,
                saving: false,
                saved: false
            };
        },
        computed: {
            totalAssignedAttributesCount() {
                return this.form.groups.reduce((acc, g) => acc + (g.attributes?.length || 0), 0);
            },
            assignedAttributeIds() {
                const ids = new Set();
                this.form.groups.forEach(g => {
                    if (Array.isArray(g.attributes)) {
                        g.attributes.forEach(a => ids.add(a.id));
                    }
                });
                return ids;
            },
            unassignedAttributes() {
                const assigned = this.assignedAttributeIds;
                return this.availableAttributesList.filter(a => !assigned.has(a.id));
            },
            totalUnassignedCount() {
                return this.unassignedAttributes.length;
            },
            attributeTypes() {
                const types = new Set();
                this.availableAttributesList.forEach(a => {
                    if (a.type) types.add(a.type);
                });
                return Array.from(types).sort();
            },
            filteredUnassignedAttributes() {
                let list = this.unassignedAttributes;

                if (this.selectedTypeFilter) {
                    list = list.filter(a => a.type === this.selectedTypeFilter);
                }

                if (this.unassignedSearch.trim()) {
                    const q = this.unassignedSearch.trim().toLowerCase();
                    list = list.filter(a =>
                        (a.name && a.name.toLowerCase().includes(q)) ||
                        (a.code && a.code.toLowerCase().includes(q))
                    );
                }

                return list;
            },
            unaddedAttributeGroups() {
                const addedIds = new Set(this.form.groups.map(g => g.attribute_group_id));
                return this.availableGroupsList.filter(g => !addedIds.has(g.id));
            },

            familyAssignedAttributes() {
                const attrs = [];
                const seen = new Set();
                this.form.groups.forEach(g => {
                    if (Array.isArray(g.attributes)) {
                        g.attributes.forEach(a => {
                            if (!seen.has(a.id)) {
                                seen.add(a.id);
                                attrs.push(a);
                            }
                        });
                    }
                });
                return attrs;
            },

            filteredModalAttributes() {
                let list = [];
                if (this.modalAttrPoolTab === 'axes') {
                    list = this.availableSelectAttributesList;
                } else if (this.modalAttrPoolTab === 'family') {
                    list = this.familyAssignedAttributes;
                    if (list.length === 0) {
                        list = this.availableAttributesList;
                    }
                } else {
                    list = this.availableAttributesList;
                }

                if (this.modalAttrSearch.trim()) {
                    const q = this.modalAttrSearch.trim().toLowerCase();
                    list = list.filter(a =>
                        (a.name && a.name.toLowerCase().includes(q)) ||
                        (a.code && a.code.toLowerCase().includes(q))
                    );
                }
                return list;
            }
        },
        methods: {
            slugify(text) {
                return (text || '')
                    .toString()
                    .toLowerCase()
                    .trim()
                    .replace(/[\s\W-]+/g, '_')
                    .replace(/^_+|_+$/g, '');
            },
            onNameInput() {
                this.errors = {};
                this.errorMessage = null;
                if (this.mode === 'create' && !this.codeTouched) {
                    this.form.code = this.slugify(this.form.name);
                }
            },
            onNewGroupNameInput() {
                this.newGroupError = null;
                if (!this.newGroupCodeTouched) {
                    this.newGroupForm.code = this.slugify(this.newGroupForm.name);
                }
            },

            // --- Group Management ---
            openAddGroupModal() {
                this.selectedExistingGroupId = this.unaddedAttributeGroups[0]?.id || null;
                this.newGroupForm = { name: '', code: '' };
                this.newGroupCodeTouched = false;
                this.newGroupError = null;
                this.modalTab = this.unaddedAttributeGroups.length > 0 ? 'existing' : 'new';
                this.showAddGroupModal = true;
            },
            addSelectedExistingGroup() {
                if (!this.selectedExistingGroupId) return;
                const found = this.availableGroupsList.find(g => g.id === this.selectedExistingGroupId);
                if (found) {
                    this.form.groups.push({
                        attribute_group_id: found.id,
                        name: found.name,
                        code: found.code,
                        position: this.form.groups.length + 1,
                        attributes: []
                    });
                }
                this.showAddGroupModal = false;
            },
            async createNewGroupAndAdd() {
                const name = (this.newGroupForm.name || '').trim();
                const code = (this.newGroupForm.code || '').trim();
                if (!name || !code) {
                    this.newGroupError = 'Both Name and Code are required.';
                    return;
                }

                this.creatingNewGroup = true;
                this.newGroupError = null;

                try {
                    const axiosClient = this.$axios || window.axios;
                    const res = await axiosClient.post('/api/catalog/attribute-groups', { name, code });
                    const newGroupData = res?.data?.data || res?.data;

                    if (newGroupData && newGroupData.id) {
                        this.availableGroupsList.push(newGroupData);
                        this.form.groups.push({
                            attribute_group_id: newGroupData.id,
                            name: newGroupData.name,
                            code: newGroupData.code,
                            position: this.form.groups.length + 1,
                            attributes: []
                        });
                        this.showAddGroupModal = false;
                    }
                } catch (caught) {
                    const res = caught?.response?.data || caught;
                    this.newGroupError = res?.message || 'Failed to create attribute group. Code might already exist.';
                } finally {
                    this.creatingNewGroup = false;
                }
            },
            removeGroup(index) {
                this.form.groups.splice(index, 1);
            },
            moveGroup(index, direction) {
                const targetIndex = index + direction;
                if (targetIndex < 0 || targetIndex >= this.form.groups.length) return;
                const group = this.form.groups.splice(index, 1)[0];
                this.form.groups.splice(targetIndex, 0, group);
            },

            // --- Drag & Drop: Groups ---
            onGroupDragStart(event, gIndex) {
                this.dragType = 'group';
                this.draggedGroupIndex = gIndex;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', 'group:' + gIndex);
            },
            onGroupDragOver(event, targetGIndex) {
                if (this.dragType !== 'group') return;
                event.dataTransfer.dropEffect = 'move';
            },
            onGroupDrop(event, targetGIndex) {
                if (this.dragType !== 'group') return;
                event.preventDefault();
                const sourceIndex = this.draggedGroupIndex;
                if (sourceIndex !== null && sourceIndex !== targetGIndex) {
                    const group = this.form.groups.splice(sourceIndex, 1)[0];
                    this.form.groups.splice(targetGIndex, 0, group);
                }
                this.onDragEnd();
            },

            // --- Drag & Drop: Attributes in Groups ---
            onAttrDragStart(event, attr, gIndex, aIndex) {
                this.dragType = 'attribute';
                this.draggedAttr = attr;
                this.draggedFromGroupIndex = gIndex;
                this.draggedFromAttrIndex = aIndex;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', 'attr:' + attr.id);
            },
            onPoolAttrDragStart(event, attr) {
                this.dragType = 'attribute';
                this.draggedAttr = attr;
                this.draggedFromGroupIndex = null;
                this.draggedFromAttrIndex = null;
                event.dataTransfer.effectAllowed = 'copyMove';
                event.dataTransfer.setData('text/plain', 'pool-attr:' + attr.id);
            },
            onAttrDragOver(event, gIndex) {
                if (this.dragType !== 'attribute') return;
                event.dataTransfer.dropEffect = 'move';
                this.dropTargetGroupIndex = gIndex;
            },
            onAttrDragLeave(event, gIndex) {
                if (this.dropTargetGroupIndex === gIndex) {
                    this.dropTargetGroupIndex = null;
                }
            },
            onAttrDrop(event, targetGroupIndex) {
                if (this.dragType !== 'attribute' || !this.draggedAttr) return;
                event.preventDefault();
                event.stopPropagation();

                const attr = this.draggedAttr;
                const sourceGroupIdx = this.draggedFromGroupIndex;
                const sourceAttrIdx = this.draggedFromAttrIndex;

                if (sourceGroupIdx !== null) {
                    this.form.groups[sourceGroupIdx].attributes.splice(sourceAttrIdx, 1);
                }

                const targetGroup = this.form.groups[targetGroupIndex];
                const existingIdx = targetGroup.attributes.findIndex(a => a.id === attr.id);
                if (existingIdx !== -1) {
                    targetGroup.attributes.splice(existingIdx, 1);
                }

                targetGroup.attributes.push(attr);

                this.onDragEnd();
            },
            onDropToPool(event) {
                if (this.dragType !== 'attribute' || !this.draggedAttr) return;
                event.preventDefault();

                if (this.draggedFromGroupIndex !== null) {
                    this.form.groups[this.draggedFromGroupIndex].attributes.splice(this.draggedFromAttrIndex, 1);
                }

                this.onDragEnd();
            },
            onDragEnd() {
                this.dragType = null;
                this.draggedGroupIndex = null;
                this.draggedAttr = null;
                this.draggedFromGroupIndex = null;
                this.draggedFromAttrIndex = null;
                this.dropTargetGroupIndex = null;
            },
            unassignAttribute(gIndex, aIndex) {
                this.form.groups[gIndex].attributes.splice(aIndex, 1);
            },
            quickAssignAttribute(attr) {
                if (this.form.groups.length === 0) return;
                const targetGroup = this.form.groups[0];
                if (!targetGroup.attributes.some(a => a.id === attr.id)) {
                    targetGroup.attributes.push(attr);
                }
            },

            // --- Multi-Level Variant Types (Tab 2) ---
            openVariantModal(index = null) {
                this.editingVariantIndex = index;
                this.variantModalError = null;
                this.modalAttrSearch = '';
                this.dropTargetLevel = null;
                this.modalAttrPoolTab = 'family';

                if (index !== null && this.form.variants[index]) {
                    const v = this.form.variants[index];
                    this.variantModalForm = {
                        name: v.name,
                        code: v.code,
                        level_count: v.level_count || 1,
                        level_1_attributes: [...(v.level_1_attributes || [])],
                        level_2_attributes: [...(v.level_2_attributes || [])],
                        variant_attributes: [...(v.variant_attributes || [])]
                    };
                    this.variantModalCodeTouched = true;
                } else {
                    this.variantModalForm = {
                        name: '',
                        code: '',
                        level_count: 1,
                        level_1_attributes: [],
                        level_2_attributes: [],
                        variant_attributes: []
                    };
                    this.variantModalCodeTouched = false;
                }

                this.showVariantModal = true;
            },
            onVariantModalNameInput() {
                this.variantModalError = null;
                if (!this.variantModalCodeTouched) {
                    this.variantModalForm.code = this.slugify(this.variantModalForm.name);
                }
            },
            addAttrToModalLevel(attr, level) {
                this.variantModalError = null;
                const list = level === 1 ? this.variantModalForm.level_1_attributes : this.variantModalForm.level_2_attributes;
                const otherList = level === 1 ? this.variantModalForm.level_2_attributes : this.variantModalForm.level_1_attributes;

                // Remove from the other level if already there
                const otherIdx = otherList.findIndex(a => a.id === attr.id);
                if (otherIdx !== -1) {
                    otherList.splice(otherIdx, 1);
                }

                // Also remove from variant_attributes if present there
                const vIdx = this.variantModalForm.variant_attributes.findIndex(a => a.id === attr.id);
                if (vIdx !== -1) {
                    this.variantModalForm.variant_attributes.splice(vIdx, 1);
                }

                // Add to current level if not present
                if (!list.some(a => a.id === attr.id)) {
                    list.push({ ...attr });
                }
            },
            addAttrToModalVariant(attr) {
                this.variantModalError = null;
                // If already in L1 or L2 axes, cannot be added as variant-level attribute
                if (this.variantModalForm.level_1_attributes.some(a => a.id === attr.id) ||
                    this.variantModalForm.level_2_attributes.some(a => a.id === attr.id)) {
                    this.variantModalError = `Attribute "${attr.name}" is already used as a variation axis.`;
                    return;
                }

                if (!this.variantModalForm.variant_attributes.some(a => a.id === attr.id)) {
                    this.variantModalForm.variant_attributes.push({ ...attr });
                }
            },
            removeModalAttr(level, idx) {
                if (level === 1) {
                    this.variantModalForm.level_1_attributes.splice(idx, 1);
                } else if (level === 2) {
                    this.variantModalForm.level_2_attributes.splice(idx, 1);
                } else if (level === 'variant') {
                    this.variantModalForm.variant_attributes.splice(idx, 1);
                }
            },
            onModalDragStart(event, attr) {
                this.draggedModalAttr = attr;
                event.dataTransfer.effectAllowed = 'copyMove';
                event.dataTransfer.setData('text/plain', 'modal-attr:' + attr.id);
            },
            onModalAttrDrop(event, level) {
                event.preventDefault();
                if (!this.draggedModalAttr) return;
                if (level === 'variant') {
                    this.addAttrToModalVariant(this.draggedModalAttr);
                } else {
                    this.addAttrToModalLevel(this.draggedModalAttr, level);
                }
                this.draggedModalAttr = null;
                this.dropTargetLevel = null;
            },
            saveVariantModal() {
                const name = (this.variantModalForm.name || '').trim();
                const code = (this.variantModalForm.code || '').trim();

                if (!name) {
                    this.variantModalError = 'Variant type name is required.';
                    return;
                }
                if (!code) {
                    this.variantModalError = 'Variant type code is required.';
                    return;
                }

                // Check duplicate code
                const isDuplicate = this.form.variants.some((v, idx) =>
                    idx !== this.editingVariantIndex && v.code.toLowerCase() === code.toLowerCase()
                );
                if (isDuplicate) {
                    this.variantModalError = 'A variant type with this code already exists in this Product Type.';
                    return;
                }

                if (this.variantModalForm.level_1_attributes.length === 0) {
                    this.variantModalError = 'At least one attribute must be assigned to Level 1.';
                    return;
                }

                if (this.variantModalForm.level_count === 2 && this.variantModalForm.level_2_attributes.length === 0) {
                    this.variantModalError = 'For a 2-level variant, at least one attribute must be assigned to Level 2 (Final Variant).';
                    return;
                }

                const item = {
                    name,
                    code,
                    level_count: this.variantModalForm.level_count,
                    level_1_attributes: [...this.variantModalForm.level_1_attributes],
                    level_2_attributes: this.variantModalForm.level_count === 2 ? [...this.variantModalForm.level_2_attributes] : [],
                    variant_attributes: [...this.variantModalForm.variant_attributes]
                };

                if (this.editingVariantIndex !== null) {
                    this.form.variants.splice(this.editingVariantIndex, 1, item);
                } else {
                    this.form.variants.push(item);
                }

                this.showVariantModal = false;
            },
            getParentAttributesForVariant(variant) {
                const excludedIds = new Set([
                    ...(variant.level_1_attributes || []).map(a => a.id),
                    ...(variant.level_2_attributes || []).map(a => a.id),
                    ...(variant.variant_attributes || []).map(a => a.id)
                ]);
                return this.familyAssignedAttributes.filter(a => !excludedIds.has(a.id));
            },
            removeVariantType(index) {
                this.form.variants.splice(index, 1);
            },

            // --- Form Submit ---
            async submitForm() {
                if (this.saving) return;

                const trimmedName = (this.form.name || '').trim();
                const trimmedCode = (this.form.code || '').trim();

                this.errors = {};
                this.errorMessage = null;

                if (!trimmedName) {
                    this.errors = { name: 'Product type name is required.' };
                    this.activeMainTab = 'general';
                    return;
                }
                if (!trimmedCode) {
                    this.errors = { code: 'Product type code is required.' };
                    this.activeMainTab = 'general';
                    return;
                }

                this.saving = true;

                try {
                    const axiosClient = this.$axios || window.axios;
                    const payload = {
                        name: trimmedName,
                        code: trimmedCode,
                        groups: this.form.groups.map((group, gIdx) => ({
                            attribute_group_id: group.attribute_group_id,
                            position: gIdx + 1,
                            attributes: (group.attributes || []).map((attr, aIdx) => ({
                                id: attr.id,
                                position: aIdx + 1
                            }))
                        })),
                        variants: this.form.variants.map((v) => ({
                            name: v.name,
                            code: v.code,
                            level_count: v.level_count,
                            level_1_attributes: (v.level_1_attributes || []).map((attr, aIdx) => ({
                                id: attr.id,
                                position: aIdx + 1
                            })),
                            level_2_attributes: v.level_count === 2
                                ? (v.level_2_attributes || []).map((attr, aIdx) => ({
                                    id: attr.id,
                                    position: aIdx + 1
                                }))
                                : []
                        }))
                    };

                    if (this.mode === 'create') {
                        await axiosClient.post('/api/catalog/product-types', payload);
                    } else {
                        const typeId = this.initialType?.id;
                        await axiosClient.put(`/api/catalog/product-types/${typeId}`, payload);
                    }

                    this.saved = true;

                    setTimeout(() => {
                        window.location.href = this.backUrl;
                    }, 600);
                } catch (caught) {
                    const res = caught?.response?.data || caught;
                    this.errorMessage = res?.message || 'Failed to save product type. Please check inputs.';
                    if (res?.errors) {
                        this.errors = Object.fromEntries(
                            Object.entries(res.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                        );
                    }
                } finally {
                    this.saving = false;
                }
            }
        }
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-product-type-builder', ProductTypeBuilderComponent);
        window.registerVueComponent('product-type-builder', ProductTypeBuilderComponent);
    } else if (window.app && typeof window.app.component === 'function') {
        window.app.component('v-product-type-builder', ProductTypeBuilderComponent);
        window.app.component('product-type-builder', ProductTypeBuilderComponent);
    }
})();
</script>
@endpushOnce
