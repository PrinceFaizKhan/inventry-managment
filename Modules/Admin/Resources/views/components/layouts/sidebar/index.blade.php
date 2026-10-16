@props([
    'items' => [],
    'currentPath' => '',
])

<v-sidebar-nav
    :items='@json($items)'
    current-path="{{ $currentPath ?: request()->path() }}"
></v-sidebar-nav>

@pushOnce('scripts')
<script type="text/x-template" id="v-sidebar-nav-template">
    <nav class="sidebar-nav-container">
        <template v-for="item in items" :key="item.key">
            <!-- Top-Level Item Without Children -->
            <template v-if="!item.children || item.children.length === 0">
                <a
                    v-if="item.coming_soon"
                    href="javascript:void(0)"
                    class="nav-item disabled"
                    title="Coming Soon"
                >
                    <span class="nav-icon-wrap" v-html="getIconSvg(item.icon)"></span>
                    <span>@{{ item.label }}</span>
                    <small class="badge badge-subtle">Soon</small>
                </a>

                <a
                    v-else
                    :href="getUrl(item.route)"
                    class="nav-item"
                    :class="{ active: isItemActive(item.key, item.route) }"
                >
                    <span class="nav-icon-wrap" v-html="getIconSvg(item.icon)"></span>
                    <span>@{{ item.label }}</span>
                </a>
            </template>

            <!-- Parent Item With Submenus (Accordion) -->
            <div v-else class="nav-group">
                <button
                    type="button"
                    class="nav-parent-btn"
                    :class="{
                        'is-active': isGroupActive(item),
                        'is-open': openGroups[item.key],
                    }"
                    :aria-expanded="openGroups[item.key] ? 'true' : 'false'"
                    @click="toggleGroup(item.key)"
                >
                    <span class="nav-icon-wrap" v-html="getIconSvg(item.icon)"></span>
                    <span>@{{ item.label }}</span>
                    <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>

                <div
                    v-show="openGroups[item.key]"
                    class="nav-children"
                >
                    <template v-for="child in item.children" :key="child.key">
                        <a
                            v-if="child.coming_soon"
                            href="javascript:void(0)"
                            class="nav-sub-item disabled"
                            title="Coming Soon"
                        >
                            <span>@{{ child.label }}</span>
                            <small class="badge badge-subtle">Soon</small>
                        </a>

                        <a
                            v-else
                            :href="getUrl(child.route)"
                            class="nav-sub-item"
                            :class="{ active: isItemActive(child.key, child.route) }"
                        >
                            <span>@{{ child.label }}</span>
                        </a>
                    </template>
                </div>
            </div>
        </template>
    </nav>
</script>

<script type="module">
    const ICONS = {
        'layout-dashboard': '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>',
        'package': '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
        'sliders-horizontal': '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/></svg>',
        'tags': '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L17 19"/><path d="M9.586 5.586A2 2 0 0 0 8.172 5H3a2 2 0 0 0-2 2v5.172a2 2 0 0 0 .586 1.414l7.714 7.714a2.4 2.4 0 0 0 3.4 0l4.286-4.286a2.4 2.4 0 0 0 0-3.4L10.414 4.586A2 2 0 0 0 9 4.172V3"/><circle cx="6.5" cy="8.5" r="1.5"/></svg>',
        'boxes': '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l6 3.43a2 2 0 0 0 2.06 0l6-3.43a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71l-6-3.43a2 2 0 0 0-2.06 0l-6 3.43Z"/><path d="m12 21.08V12"/><path d="M7 3.34a2 2 0 0 1 2.06 0l6 3.43a2 2 0 0 1 .97 1.71v3.24a2 2 0 0 1-.97 1.71l-6 3.43a2 2 0 0 1-2.06 0l-6-3.43a2 2 0 0 1-.97-1.71V8.48a2 2 0 0 1 .97-1.71l6-3.43Z"/></svg>',
        'clipboard-list': '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>',
        'settings': '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
        'users': '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'shield-check': '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
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
                openGroups: {}
            };
        },
        created() {
            this.items.forEach(item => {
                if (item.children && item.children.length > 0) {
                    this.openGroups[item.key] = this.isGroupActive(item);
                }
            });
        },
        methods: {
            getIconSvg(icon) {
                return ICONS[icon] || '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/></svg>';
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
                if (key === 'catalog' || key === 'catalog.attributes') {
                    return current.startsWith('catalog/attributes') || current.startsWith('attributes');
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
                if (this.isItemActive(item.key, item.route)) {
                    return true;
                }
                if (item.children && item.children.length > 0) {
                    return item.children.some(child => this.isItemActive(child.key, child.route));
                }
                return false;
            },
            toggleGroup(key) {
                this.openGroups[key] = !this.openGroups[key];
            },
            getUrl(route) {
                if (!route) return '#';
                return '/' + this.normalize(route);
            }
        }
    };

    app.component('v-sidebar-nav', sidebarNavComponent);
    app.component('sidebar-nav', sidebarNavComponent);
</script>
@endpushOnce
