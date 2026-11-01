<?php

namespace Modules\Core\Support\Connector;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class ConnectorRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $connectors = [];

    protected bool $booted = false;

    public function __construct()
    {
        $this->bootDefaults();
    }

    /**
     * Boot connectors by allowing independent installed modules to register dynamically.
     */
    protected function bootDefaults(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        // Dispatch hook so any independent installed module can register connectors dynamically
        Event::dispatch('catalog.connectors.registering', [$this]);
    }

    /**
     * Register a connector module.
     *
     * @param  array{code: string, name: string, description?: string, category?: string, icon?: string, badge?: string, is_default?: bool}  $connector
     */
    public function register(array $connector): static
    {
        $code = (string) ($connector['code'] ?? '');
        if ($code === '') {
            return $this;
        }

        $this->connectors[$code] = array_merge([
            'code' => $code,
            'name' => $connector['name'] ?? ucfirst($code),
            'description' => $connector['description'] ?? '',
            'category' => $connector['category'] ?? 'General',
            'icon' => $connector['icon'] ?? 'plug',
            'badge' => $connector['badge'] ?? strtoupper(substr($code, 0, 3)),
            'is_default' => (bool) ($connector['is_default'] ?? false),
        ], $connector);

        return $this;
    }

    /**
     * Unregister a connector by code.
     */
    public function unregister(string $code): static
    {
        unset($this->connectors[$code]);

        return $this;
    }

    /**
     * Get all registered connectors as array of definitions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return array_values($this->connectors);
    }

    /**
     * Get connector collection.
     *
     * @return Collection<string, array<string, mixed>>
     */
    public function collection(): Collection
    {
        return collect($this->connectors);
    }

    /**
     * Check if a connector exists.
     */
    public function has(string $code): bool
    {
        return isset($this->connectors[$code]);
    }

    /**
     * Get connector by code.
     *
     * @return array<string, mixed>|null
     */
    public function get(string $code): ?array
    {
        return $this->connectors[$code] ?? null;
    }

    /**
     * Return select options for UI dropdowns and checkboxes.
     *
     * @return array<int, array{value: string, label: string, category: string, badge: string, description: string}>
     */
    public function options(): array
    {
        return array_map(fn ($item) => [
            'value' => $item['code'],
            'label' => $item['name'],
            'category' => $item['category'] ?? 'General',
            'badge' => $item['badge'] ?? strtoupper(substr($item['code'], 0, 3)),
            'description' => $item['description'] ?? '',
        ], array_values($this->connectors));
    }
}
