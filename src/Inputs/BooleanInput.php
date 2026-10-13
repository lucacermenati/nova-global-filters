<?php

namespace LucaCermenati\NovaGlobalFilters\Inputs;

/**
 * A checkbox. Unchecked means "not filtering" (null), so use a SelectInput
 * when you need a real true/false/any choice.
 */
class BooleanInput extends Input
{
    public function type(): string
    {
        return 'boolean';
    }

    protected function baseRules(): array
    {
        return ['nullable', 'boolean'];
    }
}
