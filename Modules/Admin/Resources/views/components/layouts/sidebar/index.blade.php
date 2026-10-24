@props([
    'items' => [],
    'currentPath' => '',
])

<v-sidebar-nav
    :items='@json($items, JSON_HEX_APOS)'
    current-path="{{ $currentPath ?: request()->path() }}"
></v-sidebar-nav>

@pushOnce('scripts')
<script type="text/x-template" id="v-sidebar-nav-template">
    <nav class="sidebar-nav-list flex flex-col gap-1.5 py-3 w-full px-2" ref="navRef">
        <template v-for="item in items" :key="item.key">
            
            <!-- A. Item Without Children -->
            <div v-if="!item.children || item.children.length === 0" class="nav-item-wrapper relative group/item w-full">
                <a
                    v-if="item.coming_soon"
                    href="javascript:void(0)"
                    class="nav-rail-btn flex items-center gap-3 w-full px-2.5 py-2 rounded-xl text-xs font-medium text-[var(--muted)] opacity-50 cursor-not-allowed select-none"
                    title="Coming Soon"
                >
                    <span class="w-6 h-6 flex-shrink-0 grid place-items-center" v-html="getIconSvg(item.icon)"></span>
                    <span class="nav-label-text truncate">@{{ item.label }}</span>
                    <small class="nav-label-text ml-auto px-1.5 py-0.2 rounded text-[9px] font-mono bg-[var(--border)]">Soon</small>
                </a>

                <a
                    v-else
                    :href="getUrl(item.route)"
                    class="nav-rail-btn relative flex items-center gap-3 w-full px-2.5 py-2 rounded-xl text-xs transition-all duration-200 select-none"
                    :class="[
                        isItemActive(item.key, item.route)
                            ? 'bg-[var(--soft)] text-[var(--teal)] font-bold border border-[var(--teal)]/30 shadow-xs'
                            : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--border)]/30 border border-transparent'
                    ]"
                >
                    <!-- Active Indicator Pip -->
                    <span
                        v-if="isItemActive(item.key, item.route)"
                        class="absolute -left-2 w-1 h-5 rounded-r-full bg-[var(--teal)] shadow-[0_0_8px_var(--teal)]"
                    ></span>

                    <span class="w-6 h-6 flex-shrink-0 grid place-items-center" v-html="getIconSvg(item.icon)"></span>
                    <span class="nav-label-text truncate">@{{ item.label }}</span>
                </a>

                <!-- Hover Tooltip (Only shown in collapsed 68px mode) -->
                <div class="nav-tooltip fixed left-[72px] rtl:left-auto rtl:right-[72px] px-2.5 py-1.5 rounded-lg bg-[var(--card)] border border-[var(--border)] text-xs font-semibold text-[var(--text)] whitespace-nowrap shadow-xl opacity-0 pointer-events-none group-hover/item:opacity-100 transition-opacity z-50">
                    @{{ item.label }}
                </div>
            </div>

            <!-- B. Parent Item With Submenus (Accordion Navigation) -->
            <div
                v-else
                class="nav-group-wrapper relative group/item w-full"
            >
                <button
                    type="button"
                    @click.stop="toggleGroup(item.key)"
                    class="nav-rail-btn relative flex items-center justify-between w-full px-2.5 py-2 rounded-xl text-xs transition-all duration-200 cursor-pointer select-none"
                    :class="[
                        isGroupActive(item)
                            ? 'bg-[var(--soft)] text-[var(--teal)] font-bold border border-[var(--teal)]/30 shadow-xs'
                            : isGroupOpen(item.key)
                                ? 'text-[var(--text)] bg-[var(--border)]/20 border border-[var(--border)]/40 font-medium'
                                : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--border)]/30 border border-transparent'
                    ]"
                    :aria-expanded="isGroupOpen(item.key) ? 'true' : 'false'"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Active Indicator Pip -->
                        <span
                            v-if="isGroupActive(item)"
                            class="absolute -left-2 w-1 h-5 rounded-r-full bg-[var(--teal)] shadow-[0_0_8px_var(--teal)]"
                        ></span>

                        <span class="w-6 h-6 flex-shrink-0 grid place-items-center" v-html="getIconSvg(item.icon)"></span>
                        <span class="nav-label-text truncate">@{{ item.label }}</span>
                    </div>

                    <!-- Accordion Chevron (Shown when expanded) -->
                    <svg
                        class="nav-chevron w-3.5 h-3.5 text-[var(--muted)] transition-transform duration-200"
                        :class="{
                            'rotate-180': isGroupOpen(item.key),
                            'text-[var(--teal)]': isGroupActive(item),
                            'text-[var(--text)]': !isGroupActive(item) && isGroupOpen(item.key)
                        }"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>

                <!-- Hover Tooltip (Only shown in collapsed 68px mode) -->
                <div class="nav-tooltip fixed left-[72px] rtl:left-auto rtl:right-[72px] px-2.5 py-1.5 rounded-lg bg-[var(--card)] border border-[var(--border)] text-xs font-semibold text-[var(--text)] whitespace-nowrap shadow-xl opacity-0 pointer-events-none group-hover/item:opacity-100 transition-opacity z-50">
                    @{{ item.label }}
                </div>

                <!-- Submenu Accordion Children -->
                <div
                    v-show="isGroupOpen(item.key)"
                    class="nav-accordion-children pl-8 pr-1 py-1 space-y-1 relative before:content-[''] before:absolute before:left-5 before:top-2 before:bottom-2 before:w-px before:bg-[var(--border)]"
                >
                    <template v-for="child in item.children" :key="child.key">
                        <a
                            v-if="child.coming_soon"
                            href="javascript:void(0)"
                            class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-[var(--muted)] opacity-50 cursor-not-allowed select-none"
                        >
                            <span class="truncate">@{{ child.label }}</span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-[var(--border)]">Soon</span>
                        </a>

                        <a
                            v-else
                            :href="getUrl(child.route)"
                            class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors select-none"
                            :class="[
                                isItemActive(child.key, child.route)
                                    ? 'bg-[var(--soft)] text-[var(--teal)] font-bold'
                                    : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--border)]/30'
                            ]"
                        >
                            <span class="truncate">@{{ child.label }}</span>
                            <svg v-if="isItemActive(child.key, child.route)" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-[var(--teal)] flex-shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                        </a>
                    </template>
                </div>

            </div>

        </template>
    </nav>
</script>

<script>
(function() {
    const ICONS = {
        'layout-dashboard': '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>',
        'package': '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
        'sliders-horizontal': '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/></svg>',
        'tags': '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L17 19"/><path d="M9.586 5.586A2 2 0 0 0 8.172 5H3a2 2 0 0 0-2 2v5.172a2 2 0 0 0 .586 1.414l7.714 7.714a2.4 2.4 0 0 0 3.4 0l4.286-4.286a2.4 2.4 0 0 0 0-3.4L10.414 4.586A2 2 0 0 0 9 4.172V3"/><circle cx="6.5" cy="8.5" r="1.5"/></svg>',
        'boxes': '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l6 3.43a2 2 0 0 0 2.06 0l6-3.43a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71l-6-3.43a2 2 0 0 0-2.06 0l-6 3.43Z"/><path d="m12 21.08V12"/><path d="M7 3.34a2 2 0 0 1 2.06 0l6 3.43a2 2 0 0 1 .97 1.71v3.24a2 2 0 0 1-.97 1.71l-6 3.43a2 2 0 0 1-2.06 0l-6-3.43a2 2 0 0 1-.97-1.71V8.48a2 2 0 0 1 .97-1.71l6-3.43Z"/></svg>',
        'clipboard-list': '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>',
        'settings': '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
        'users': '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'shield-check': '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
    };

    const sidebarNavComponent = {
        template: '#v-sidebar-nav-template',
        props: {
            items: {
                type: Array,
                default: () => []
            },
            currentPath: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                openGroups: {},
                isExpanded: true
            };
        },
        created() {
            this.updateExpandedState();
            if (Array.isArray(this.items)) {
                this.items.forEach(item => {
                    if (item && item.children && item.children.length > 0) {
                        this.openGroups[item.key] = this.isGroupActive(item);
                    }
                });
            }
        },
        mounted() {
            this.updateExpandedState();
            window.addEventListener('ht-sidebar-toggled', this.handleSidebarToggled);
            
            const observer = new MutationObserver(() => this.updateExpandedState());
            if (document.body) observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
            if (document.documentElement) observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            this._observer = observer;
        },
        beforeUnmount() {
            window.removeEventListener('ht-sidebar-toggled', this.handleSidebarToggled);
            if (this._observer) this._observer.disconnect();
        },
        methods: {
            updateExpandedState() {
                if (typeof document === 'undefined') return;
                this.isExpanded = document.body.classList.contains('sidebar-expanded') || document.documentElement.classList.contains('sidebar-expanded');
            },
            handleSidebarToggled(e) {
                if (e.detail && typeof e.detail.expanded !== 'undefined') {
                    this.isExpanded = Boolean(e.detail.expanded);
                } else {
                    this.updateExpandedState();
                }
            },
            isGroupOpen(key) {
                return Boolean(this.openGroups[key]);
            },
            toggleGroup(key) {
                if (!this.isExpanded && typeof window.toggleSidebar === 'function') {
                    window.toggleSidebar();
                    this.openGroups = {
                        ...this.openGroups,
                        [key]: true
                    };
                    return;
                }

                this.openGroups = {
                    ...this.openGroups,
                    [key]: !this.isGroupOpen(key)
                };
            },
            getIconSvg(icon) {
                return ICONS[icon] || '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/></svg>';
            },
            normalize(path) {
                return (path || '').replace(/^\/+|\/+$/g, '');
            },
            isItemActive(key, route) {
                const current = this.normalize(this.currentPath);
                const target = this.normalize(route || '');

                if (key === 'dashboard') {
                    return current === 'dashboard' || current === 'admin/dashboard';
                }
                if (key === 'catalog.attributes') {
                    return current.startsWith('catalog/attributes') || current.startsWith('attributes');
                }
                if (key === 'catalog.attribute-groups') {
                    return current.startsWith('catalog/attribute-groups');
                }
                if (key === 'catalog.product-types') {
                    return current.startsWith('catalog/product-types');
                }
                if (key === 'catalog.products') {
                    return current.startsWith('catalog/products');
                }
                if (key === 'settings.users') {
                    return current.startsWith('settings/users');
                }
                if (key === 'settings.roles') {
                    return current.startsWith('settings/roles');
                }
                if (target !== '') {
                    return current.startsWith(target);
                }
                return false;
            },
            isGroupActive(item) {
                if (item.children && item.children.length > 0) {
                    return item.children.some(child => this.isItemActive(child.key, child.route));
                }
                return this.isItemActive(item.key, item.route);
            },
            getUrl(route) {
                if (!route) return '#';
                return '/' + this.normalize(route);
            }
        }
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-sidebar-nav', sidebarNavComponent);
        window.registerVueComponent('sidebar-nav', sidebarNavComponent);
    } else if (window.app && window.app.component) {
        window.app.component('v-sidebar-nav', sidebarNavComponent);
        window.app.component('sidebar-nav', sidebarNavComponent);
    }
})();
</script>
@endpushOnce
