<?php

namespace Modules\Core\DataGrid;

use Closure;

class Filter
{
    protected string $index;

    protected string $label;

    protected string $type = 'select';

    /** @var array<int|string, mixed>|Closure */
    protected array|Closure $options = [];

    protected ?Closure $queryCallback = null;

    public function __construct(string $index)
    {
        $this->index = $index;
        $this->label = ucwords(str_replace(['_', '.'], ' ', $index));
    }

    public static function make(string $index): static
    {
        return new static($index);
    }

    public static function select(string $index): static
    {
        return (new static($index))->type('select');
    }

    public static function boolean(string $index): static
    {
        return (new static($index))
            ->type('select')
            ->options([
                ['value' => '1', 'label' => 'Yes'],
                ['value' => '0', 'label' => 'No'],
            ]);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /** @param array<int|string, mixed>|Closure $options */
    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function query(Closure $callback): static
    {
        $this->queryCallback = $callback;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function getQueryCallback(): ?Closure
    {
        return $this->queryCallback;
    }

    /** @return array<int, array{value: mixed, label: string}> */
    public function getOptions(): array
    {
        $raw = is_callable($this->options) ? ($this->options)() : $this->options;
        if (! is_array($raw)) {
            return [];
        }

        $formatted = [];
        foreach ($raw as $key => $val) {
            if (is_array($val) && isset($val['value'], $val['label'])) {
                $formatted[] = $val;
            } else {
                $formatted[] = ['value' => (string) $key, 'label' => (string) $val];
            }
        }

        return $formatted;
    }
}
