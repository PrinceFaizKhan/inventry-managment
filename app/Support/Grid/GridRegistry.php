<?php

namespace App\Support\Grid;

use Illuminate\Support\Facades\File;

class GridRegistry
{
    /** @return array<string, mixed> */
    public function find(string $key): array
    {
        $grids = [];

        foreach (File::glob(base_path('Modules/*/Config/grids.php')) as $path) {
            $config = require $path;
            if (! is_array($config)) {
                throw new GridConfigurationException("Grid config [$path] must return an array.");
            }

            foreach ($config as $grid) {
                if (is_array($grid)) {
                    $grid['selectable'] ??= false;
                    $grid['bulk_actions'] ??= [];
                    $grid['bulk_edit_fields'] ??= [];
                }
                $this->validate($grid, $grids, $path);
                $grids[$grid['key']] = $grid;
            }
        }

        if (! isset($grids[$key])) {
            throw new GridConfigurationException("Grid [$key] is not registered.");
        }

        return $grids[$key];
    }

    /** @param array<string, mixed> $grid @param array<string, array<string, mixed>> $registered */
    private function validate(mixed $grid, array $registered, string $path): void
    {
        if (! is_array($grid) || ! isset($grid['key'], $grid['endpoint'], $grid['row_identifier'], $grid['provider'], $grid['searchable'], $grid['sorts'], $grid['filters'], $grid['page_sizes'], $grid['default_sort'], $grid['default_direction'], $grid['view_permission'], $grid['selectable'], $grid['bulk_actions'], $grid['bulk_edit_fields'])) {
            throw new GridConfigurationException("Grid config [$path] has a missing required field.");
        }

        if (! is_string($grid['key']) || isset($registered[$grid['key']]) || ! is_string($grid['provider']) || ! is_subclass_of($grid['provider'], GridDataProvider::class)) {
            throw new GridConfigurationException("Grid config [$path] has an invalid key or provider.");
        }

        if (! is_string($grid['endpoint']) || ! is_string($grid['row_identifier']) || ! is_array($grid['searchable']) || ! is_array($grid['sorts']) || ! is_array($grid['filters']) || ! is_array($grid['page_sizes']) || ! array_key_exists($grid['default_sort'], $grid['sorts']) || ! in_array($grid['default_direction'], ['asc', 'desc'], true) || ! is_bool($grid['selectable']) || ! is_array($grid['bulk_actions']) || ! is_array($grid['bulk_edit_fields'])) {
            throw new GridConfigurationException("Grid [{$grid['key']}] in [$path] has invalid query options.");
        }

        $handler = null;
        if ($grid['selectable']) {
            if (! isset($grid['bulk_handler']) || ! is_string($grid['bulk_handler']) || ! is_subclass_of($grid['bulk_handler'], GridBulkActionHandler::class)) {
                throw new GridConfigurationException("Selectable grid [{$grid['key']}] in [$path] requires a valid bulk handler.");
            }

            $handler = app($grid['bulk_handler']);
        }

        foreach ($grid['bulk_actions'] as $action => $permission) {
            if (! is_string($action) || ! preg_match('/^[a-z][a-z0-9_]*$/', $action) || ! is_string($permission) || ($handler !== null && ! in_array($action, $handler->supportedActions(), true))) {
                throw new GridConfigurationException("Grid [{$grid['key']}] in [$path] has an invalid bulk action.");
            }
        }

        foreach ($grid['bulk_edit_fields'] as $field => $definition) {
            if (! is_string($field) || ! is_array($definition) || ! isset($definition['label'], $definition['type'], $definition['permission']) || ! in_array($definition['type'], ['text', 'select'], true) || ! is_string($definition['permission']) || ($handler !== null && ! in_array($field, $handler->editableFields(), true))) {
                throw new GridConfigurationException("Grid [{$grid['key']}] in [$path] has an invalid bulk-edit field.");
            }
            if (($definition['nullable'] ?? false) && ! is_bool($definition['nullable'])) {
                throw new GridConfigurationException("Grid [{$grid['key']}] in [$path] has invalid nullable configuration.");
            }
        }
    }
}
