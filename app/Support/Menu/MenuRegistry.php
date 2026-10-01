<?php

namespace App\Support\Menu;

use App\Models\User;
use Illuminate\Support\Facades\File;

class MenuRegistry
{
    private const ICONS = ['layout-dashboard', 'sliders-horizontal', 'tags', 'package', 'boxes', 'clipboard-list', 'settings', 'users', 'shield-check'];

    public function __construct(private readonly MenuAuthorizer $authorizer) {}

    public function for(User $user): array
    {
        $keys = [];
        $entries = [];

        foreach (File::glob(base_path('Modules/*/Config/menu.php')) as $path) {
            $module = require $path;
            if (! is_array($module)) {
                throw new MenuConfigurationException("Menu config [$path] must return an array.");
            }

            foreach ($module as $entry) {
                $normalised = $this->normalise($entry, $keys, $path);
                if ($normalised = $this->filterFor($normalised, $user)) {
                    $entries[] = $normalised;
                }
            }
        }

        return $this->sort($entries);
    }

    private function normalise(mixed $entry, array &$keys, string $path): array
    {
        if (! is_array($entry)) {
            throw new MenuConfigurationException("Menu entry in [$path] must be an array.");
        }

        foreach (['key', 'label', 'icon', 'route', 'sort'] as $field) {
            if (! array_key_exists($field, $entry)) {
                throw new MenuConfigurationException("Menu entry in [$path] is missing [$field].");
            }
        }
        if (! is_string($entry['key']) || isset($keys[$entry['key']])) {
            throw new MenuConfigurationException("Menu key [{$entry['key']}] in [$path] must be unique.");
        }
        if (! is_string($entry['label']) || ! is_string($entry['route']) || ! is_int($entry['sort']) || ! in_array($entry['icon'], self::ICONS, true)) {
            throw new MenuConfigurationException("Menu entry [{$entry['key']}] in [$path] has an invalid schema or icon.");
        }
        $keys[$entry['key']] = true;
        $entry['permission'] = $entry['permission'] ?? null;
        $entry['enabled'] = $entry['enabled'] ?? true;
        $entry['coming_soon'] = $entry['coming_soon'] ?? false;
        if ((! is_null($entry['permission']) && ! is_string($entry['permission'])) || ! is_bool($entry['enabled']) || ! is_bool($entry['coming_soon']) || ! is_array($entry['children'] ?? [])) {
            throw new MenuConfigurationException("Menu entry [{$entry['key']}] in [$path] has invalid optional fields.");
        }
        $entry['children'] = array_values(array_filter(array_map(fn ($child) => $this->normalise($child, $keys, $path), $entry['children'] ?? [])));

        return $entry;
    }

    private function filterFor(array $entry, User $user): ?array
    {
        if (! $this->authorizer->allows($user, $entry['permission'])) {
            return null;
        }
        $entry['children'] = array_values(array_filter(array_map(fn ($child) => $this->filterFor($child, $user), $entry['children'])));

        return $entry['enabled'] || $entry['coming_soon'] || $entry['children'] !== [] ? $entry : null;
    }

    private function sort(array $entries): array
    {
        foreach ($entries as &$entry) {
            $entry['children'] = $this->sort($entry['children']);
        }
        unset($entry);
        usort($entries, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        return $entries;
    }
}
