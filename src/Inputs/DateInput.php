<?php

namespace LucaCermenati\NovaGlobalFilters\Inputs;

class DateInput extends Input
{
    protected ?string $min = null;

    protected ?string $max = null;

    public function type(): string
    {
        return 'date';
    }

    /**
     * Earliest selectable date (Y-m-d).
     */
    public function min(string $date): static
    {
        $this->min = $date;

        return $this;
    }

    /**
     * Latest selectable date (Y-m-d).
     */
    public function max(string $date): static
    {
        $this->max = $date;

        return $this;
    }

    protected function baseRules(): array
    {
        $rules = ['nullable', 'date_format:Y-m-d'];

        if ($this->min) {
            $rules[] = 'after_or_equal:'.$this->min;
        }

        if ($this->max) {
            $rules[] = 'before_or_equal:'.$this->max;
        }

        return $rules;
    }

    protected function meta(): array
    {
        return ['min' => $this->min, 'max' => $this->max];
    }
}
