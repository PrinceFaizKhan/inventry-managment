<?php

namespace App\Support\Permissions;

use Illuminate\Support\Facades\File;

class PermissionRegistry
{
    public function tree(): array
    {
        $keys = [];
        $tree = [];

        foreach (File::glob(base_path('Modules/*/Config/permissions.php')) as $path) {
            $config = require $path;
            if (! is_array($config)) {
                throw new PermissionConfigurationException("Permission config [$path] must return an array.");
            }
            foreach ($config as $node) {
                $tree[] = $this->normalise($node, $keys, $path);
            }
        }

        return $tree;
    }

    public function keys(): array
    {
        $keys = [];
        $walk = function (array $nodes) use (&$walk, &$keys): void {
            foreach ($nodes as $node) {
                if ($node['children'] === []) {
                    $keys[] = $node['key'];
                } else {
                    $walk($node['children']);
                }
            }
        };
        $walk($this->tree());

        return $keys;
    }

    private function normalise(mixed $node, array &$keys, string $path): array
    {
        if (! is_array($node) || ! isset($node['key'], $node['label']) || ! is_string($node['key']) || ! is_string($node['label'])) {
            throw new PermissionConfigurationException("Permission node in [$path] requires string key and label.");
        }
        if (isset($keys[$node['key']])) {
            throw new PermissionConfigurationException("Permission key [{$node['key']}] in [$path] must be unique.");
        }
        $keys[$node['key']] = true;
        if (isset($node['children']) && ! is_array($node['children'])) {
            throw new PermissionConfigurationException("Permission node [{$node['key']}] in [$path] has invalid children.");
        }
        $node['children'] = array_map(fn ($child) => $this->normalise($child, $keys, $path), $node['children'] ?? []);

        return $node;
    }
}
