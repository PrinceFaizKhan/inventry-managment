<?php

namespace Modules\Core\DataGrid;

use Closure;

class Column
{
    protected string $index;

    protected string $label;

    protected bool $sortable = false;

    protected bool $searchable = false;

    protected bool $filterable = false;

    protected string $type = 'string';

    protected string $align = 'left';

    protected ?string $width = null;

    protected ?Closure $closure = null;

    protected bool $hidden = false;

    public function __construct(string $index)
    {
        $this->index = $index;
        $this->label = ucwords(str_replace(['_', '.'], ' ', $index));
    }

    public static function make(string $index): static
    {
        return new static($index);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function filterable(bool $filterable = true): static
    {
        $this->filterable = $filterable;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function boolean(): static
    {
        $this->type = 'boolean';
        $this->align = 'center';

        return $this;
    }

    public function badge(): static
    {
        $this->type = 'badge';

        return $this;
    }

    public function align(string $align): static
    {
        $this->align = $align;

        return $this;
    }

    public function width(string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function format(Closure $closure): static
    {
        $this->closure = $closure;

        return $this;
    }

    public function hidden(bool $hidden = true): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAlign(): string
    {
        return $this->align;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function getClosure(): ?Closure
    {
        return $this->closure;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function toArray(): array
    {
        return [
            'index' => $this->index,
            'label' => $this->label,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'filterable' => $this->filterable,
            'type' => $this->type,
            'align' => $this->align,
            'width' => $this->width,
            'hidden' => $this->hidden,
        ];
    }
}
