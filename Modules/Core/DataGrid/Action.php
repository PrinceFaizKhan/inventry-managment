<?php

namespace Modules\Core\DataGrid;

use Closure;

class Action
{
    protected string $key;

    protected string $label;

    protected string $icon = '';

    protected string $method = 'GET';

    protected string|Closure|null $url = null;

    protected string|bool|Closure|null $permission = null;

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->label = ucwords(str_replace(['_', '.'], ' ', $key));
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public static function edit(?string $permission = null, string|Closure|null $url = null): static
    {
        return (new static('edit'))
            ->label('Edit')
            ->icon('pencil')
            ->method('GET')
            ->authorize($permission ?? true)
            ->url($url);
    }

    public static function delete(?string $permission = null, string|Closure|null $url = null): static
    {
        return (new static('delete'))
            ->label('Delete')
            ->icon('trash')
            ->method('DELETE')
            ->authorize($permission ?? true)
            ->url($url);
    }

    public static function activate(?string $permission = null, string|Closure|null $url = null): static
    {
        return (new static('activate'))
            ->label('Activate')
            ->icon('check-circle')
            ->method('POST')
            ->authorize($permission ?? true)
            ->url($url);
    }

    public static function deactivate(?string $permission = null, string|Closure|null $url = null): static
    {
        return (new static('deactivate'))
            ->label('Deactivate')
            ->icon('x-circle')
            ->method('POST')
            ->authorize($permission ?? true)
            ->url($url);
    }

    public static function toggleStatus(?string $permission = null, string|Closure|null $url = null): static
    {
        return (new static('toggle_status'))
            ->label('Toggle Status')
            ->icon('toggle')
            ->method('POST')
            ->authorize($permission ?? true)
            ->url($url);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function method(string $method): static
    {
        $this->method = strtoupper($method);

        return $this;
    }

    public function url(string|Closure|null $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function authorize(string|bool|Closure|null $permission): static
    {
        $this->permission = $permission;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isAllowed(mixed $user, mixed $row = null): bool
    {
        if (is_null($this->permission) || $this->permission === true) {
            return true;
        }

        if ($this->permission === false) {
            return false;
        }

        if (is_callable($this->permission)) {
            return (bool) ($this->permission)($user, $row);
        }

        if (is_string($this->permission)) {
            if ($this->permission === 'authenticated') {
                return $user !== null;
            }
            if (function_exists('bouncer')) {
                return bouncer()->hasPermission($this->permission);
            }
            if ($user && method_exists($user, 'hasPermission')) {
                return $user->hasPermission($this->permission);
            }

            return true;
        }

        return true;
    }

    public function getUrl(mixed $row): ?string
    {
        if (is_callable($this->url)) {
            return ($this->url)($row);
        }

        return $this->url;
    }
}
