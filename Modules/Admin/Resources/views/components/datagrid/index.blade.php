@props([
    'src' => null,
    'config' => null,
    'columns' => null,
    'actions' => null,
    'massActions' => null,
    'selectable' => null,
    'pageSizes' => [10, 25, 50, 100],
    'defaultPerPage' => 10,
    'searchPlaceholder' => 'Search records...',
    'rowKey' => 'id',
])

<x-core::datagrid
    :src="$src"
    :config="$config"
    :columns="$columns"
    :actions="$actions"
    :mass-actions="$massActions"
    :selectable="$selectable"
    :page-sizes="$pageSizes"
    :default-per-page="$defaultPerPage"
    :search-placeholder="$searchPlaceholder"
    :row-key="$rowKey"
    {{ $attributes }}
>
    {{ $slot }}
</x-core::datagrid>
