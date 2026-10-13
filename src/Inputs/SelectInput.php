<?php

namespace LucaCermenati\NovaGlobalFilters\Inputs;

class SelectInput extends Input
{
    /** @var array<int|string, string> */
    protected array $options = [];

    protected ?string $anyLabel = null;

    public function type(): string
    {
        return 'select';
    }

    /**
     * @param  array<int|string, string>  $options  value => label
     */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Label of the empty option that disables the filter.
     */
    public function anyLabel(string $label): static
    {
        $this->anyLabel = $label;

        return $this;
    }

    protected function baseRules(): array
    {
        return ['nullable', 'in:'.implode(',', array_map('strval', array_keys($this->options)))];
    }

    protected function meta(): array
    {
        return [
            'options' => collect($this->options)
                ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => $label])
                ->values()
                ->all(),
            'anyLabel' => $this->anyLabel ?? __('Any'),
        ];
    }
}
