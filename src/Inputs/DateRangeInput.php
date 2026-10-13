<?php

namespace LucaCermenati\NovaGlobalFilters\Inputs;

/**
 * Two date inputs producing ['start' => 'Y-m-d'|null, 'end' => 'Y-m-d'|null].
 */
class DateRangeInput extends Input
{
    protected ?string $startLabel = null;

    protected ?string $endLabel = null;

    public function type(): string
    {
        return 'date-range';
    }

    public function startLabel(string $label): static
    {
        $this->startLabel = $label;

        return $this;
    }

    public function endLabel(string $label): static
    {
        $this->endLabel = $label;

        return $this;
    }

    protected function baseRules(): array
    {
        return ['nullable', 'array'];
    }

    public function validationRules(): array
    {
        return array_merge(parent::validationRules(), [
            "{$this->attribute}.start" => ['nullable', 'date_format:Y-m-d'],
            "{$this->attribute}.end" => ['nullable', 'date_format:Y-m-d', "after_or_equal:{$this->attribute}.start"],
        ]);
    }

    public function validationAttributes(): array
    {
        return array_merge(parent::validationAttributes(), [
            "{$this->attribute}.start" => $this->getStartLabel(),
            "{$this->attribute}.end" => $this->getEndLabel(),
        ]);
    }

    public function getStartLabel(): string
    {
        return $this->startLabel ?? __('From');
    }

    public function getEndLabel(): string
    {
        return $this->endLabel ?? __('Until');
    }

    protected function meta(): array
    {
        return [
            'startLabel' => $this->getStartLabel(),
            'endLabel' => $this->getEndLabel(),
        ];
    }
}
