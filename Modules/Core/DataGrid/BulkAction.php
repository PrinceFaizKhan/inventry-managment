<?php

namespace Modules\Core\DataGrid;

use Closure;

class BulkAction
{
    protected string $key;

    protected string $label;

    protected string $method = 'POST';

    protected string|Closure|null $url = null;

    protected string|bool|Closure|null $permission = null;

    protected ?Closure $handler = null;

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->label = ucwords(str_replace(['_', '.'], ' ', $key));
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public static function delete(?string $permission = null, string|Closure|null $url = null): static
    {
        return (new static('delete_selected'))
            ->label('Delete Selected')
            ->method('POST')
            ->authorize($permission ?? true)
            ->url($url);
    }

    public function label(string $label): static
    {
        $this->label = $label;

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

    public function handler(Closure $handler): static
    {
        $this->handler = $handler;

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

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHandler(): ?Closure
    {
        return $this->handler;
    }

    public function isAllowed(mixed $user): bool
    {
        if (is_null($this->permission) || $this->permission === true) {
            return true;
        }

        if ($this->permission === false) {
            return false;
        }

        if (is_callable($this->permission)) {
            return (bool) ($this->permission)($user);
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

    public function getUrl(): ?string
    {
        if (is_callable($this->url)) {
            return ($this->url)();
        }

        return $this->url;
    }
}
