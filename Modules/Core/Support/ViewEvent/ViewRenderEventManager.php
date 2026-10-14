<?php

namespace Modules\Core\Support\ViewEvent;

use Closure;
use Illuminate\Support\HtmlString;

class ViewRenderEventManager
{
    /**
     * @var array<string, array<int, array{callback: Closure|string, priority: int}>>
     */
    protected array $listeners = [];

    /**
     * Register a listener or view snippet for a specific view event.
     *
     * @param  int  $priority  Higher runs first (default 10)
     */
    public function listen(string $event, Closure|string $callback, int $priority = 10): void
    {
        $this->listeners[$event][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority descending
        usort($this->listeners[$event], fn ($a, $b) => $b['priority'] <=> $a['priority']);
    }

    /**
     * Render and concatenate all registered view fragments for an event.
     *
     * @param  array<string, mixed>  $params
     */
    public function render(string $event, array $params = []): HtmlString
    {
        if (! isset($this->listeners[$event]) || empty($this->listeners[$event])) {
            return new HtmlString('');
        }

        $output = '';

        foreach ($this->listeners[$event] as $item) {
            $callback = $item['callback'];

            if ($callback instanceof Closure) {
                $result = $callback($params);
            } else {
                $result = $callback;
            }

            if ($result !== null && $result !== false) {
                $output .= (string) $result;
            }
        }

        return new HtmlString($output);
    }

    /**
     * Retrieve all extension payloads for an event as an array (useful for API responses).
     *
     * @param  array<string, mixed>  $params
     * @return array<int, mixed>
     */
    public function collect(string $event, array $params = []): array
    {
        if (! isset($this->listeners[$event])) {
            return [];
        }

        $items = [];

        foreach ($this->listeners[$event] as $item) {
            $callback = $item['callback'];
            $result = $callback instanceof Closure ? $callback($params) : $callback;
            if ($result !== null) {
                $items[] = $result;
            }
        }

        return $items;
    }

    /**
     * Check if any listeners are registered for an event.
     */
    public function has(string $event): bool
    {
        return ! empty($this->listeners[$event]);
    }

    /**
     * Clear all registered listeners (primarily for testing).
     */
    public function clear(): void
    {
        $this->listeners = [];
    }
}
