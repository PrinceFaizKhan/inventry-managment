# Haseen Telecom foundation

## Registering a module

Create a module directory such as `Modules/Catalog`. Keep its configuration in `Config`, its Laravel routes in `Routes`, and its business services, actions, and models inside the module. Modules communicate through explicit contracts or events; they do not write to another module's tables directly.

The shared application infrastructure lives in `app/Support/Menu`, `app/Http/Controllers`, and `resources/js/components`. Catalog, Inventory, Sales, and Settings do not own or alter the shared layout.

## Menu configuration

Add `Modules/<Module>/Config/menu.php` that returns an array of entries. `MenuRegistry` discovers these files automatically.

```php
return [[
    'key' => 'catalog.products',
    'label' => 'Products',
    'icon' => 'package',
    'route' => 'catalog.products',
    'sort' => 30,
    'permission' => 'catalog.products.view',
    'enabled' => true,
    'children' => [],
]];
```

`key`, `label`, `icon`, `route`, and integer `sort` are required. `children` uses the same schema. Keys are unique across all registered module files. Invalid schemas, duplicate keys, and unsupported icons raise `MenuConfigurationException` with the configuration path. Entries are sorted recursively.

Set `enabled` to `false` and `coming_soon` to `true` to display a non-clickable item. Disabled items without `coming_soon` are omitted. Menu configuration registers navigation only; it never creates a page or an API route.

## Routes and icons

`route` is a named Vue route. Add the route and page only when that module is implemented, then reuse `AppHeader`, `AppSidebar`, `RecursiveMenuItem`, `BaseButton`, `FormField`, `AppDropdown`, and `Notifications` rather than reproducing layout markup. The sidebar only renders API data and contains no module-specific branches.

Icons are resolved in `RecursiveMenuItem.vue` from an allowlisted backend identifier. Current values are `layout-dashboard`, `sliders-horizontal`, `tags`, `package`, `boxes`, `clipboard-list`, `settings`, `users`, and `shield-check`. Add an identifier to both `MenuRegistry::ICONS` and the frontend mapping only when an approved icon is needed.

## Authorization

Every current entry declares `permission: authenticated`. `MenuAuthorizer` provides the future role/permission extension point and denies any unresolved permission requirement by default. The navigation filter is presentation only. Each future module API endpoint must enforce its own authorization middleware, policy, or gate independently.

## Registered permissions and roles

Each module can register a permission hierarchy in `Modules/<Module>/Config/permissions.php`. A node needs a globally unique `key` and a `label`; nested `children` use the same schema. `PermissionRegistry` discovers and validates every file. Only leaf keys are persisted for a Custom role, so a future permission added to configuration remains ungranted until explicitly selected.

`GET /api/permissions` supplies this tree to the shared recursive Vue permission component. `POST /api/roles` validates submitted keys against the registry and persists `name`, `access`, and the selected permissions in the `roles` table. Administration roles deliberately store no snapshot: they resolve to every current and future registered permission when authorization is added to user-role assignment.

## Current API boundaries

Session login and logout are provided by `AuthController`. `GET /api/navigation` and `GET /api/grids/{grid}` require Laravel authentication. A future mobile client can consume separately versioned Laravel API endpoints; its authentication strategy can be added without changing menu registration or the Vue layout.

## Shared DataGrid

All listings use `resources/js/components/grid/DataGrid.vue`. It owns searching, filter controls, active-filter chips, sorting, loading/empty/error states, pagination, URL state, stale-request cancellation, and row-action confirmation. Pages provide only a typed configuration and named cell slots for presentation that cannot be expressed as plain data, such as an avatar or status badge.

The Users configuration is in `resources/js/modules/settings/usersGrid.ts`. A future grid follows the same shape:

```ts
export const productsGrid: GridConfig = {
  key: 'products', rowKey: 'id', endpoint: '/api/grids/products',
  searchableFields: ['name', 'sku'],
  searchPlaceholder: 'Search products…',
  columns: [{ key: 'name', label: 'Product', sortable: true }],
  filters: [{ key: 'status', label: 'Status', type: 'select' }],
  defaultSort: 'name', defaultDirection: 'asc', defaultPageSize: 10,
  pageSizes: [10, 25, 50], actions: [],
}
```

The matching Laravel module registers an allowlisted provider in `Modules/<Module>/Config/grids.php`. That provider implements `GridDataProvider`, selects only the fields it returns, eagerly loads any displayed relationship, and owns its module-specific query and filter options. `GridController` and `IndexGridRequest` validate the common contract: `search`, `filter`, `sort`, `direction`, `page`, and `per_page`. Unknown sort fields or filters are rejected before a query is built.

The shared endpoint is `GET /api/grids/{grid}`. It returns `data` plus pagination, filter-option, and action-visibility metadata. Create, edit, and delete actions remain hidden until both an authorized backend flow and the matching permission are registered.

## DataGrid selection and bulk actions

Grid configuration can opt in to multi-selection with `selectable => true`; the default is `false`. Users remains disabled in `Modules/Settings/Config/grids.php` and `resources/js/modules/settings/usersGrid.ts`. `resources/js/modules/settings/usersGrid.selection-example.ts` shows the frontend opt-in shape; the test-only `SelectableUsersGridRegistry` exercises the enabled backend contract without adding a production switch or another module screen.

An enabled module lists action names and permission keys in `bulk_actions`, and safe, explicitly editable fields in `bulk_edit_fields`. Do not configure credentials, unique identifiers, or fields that cannot be safely mass changed. Each field declares `label`, `type`, `permission`, and optional `nullable`; nullable fields get an explicit clear checkbox. Select choices can be static configuration options or supplied by the module provider.

Configure a `bulk_handler` implementing `GridBulkActionHandler`. That module-owned handler must load and lock the exact submitted IDs, reject missing or protected records, authorize the complete selection, enforce business invariants, and apply changes inside a transaction only after every check passes. The shared endpoint at `POST /api/grids/{grid}/bulk/{action}` bounds IDs to 100, rejects duplicates and unknown fields/actions, checks the action and field permission keys, and never trusts frontend configuration as authorization. Each future module supplies its own handler; the shared Vue DataGrid only sends the selected IDs and explicitly enabled changes.
