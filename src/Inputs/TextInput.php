<?php

namespace LucaCermenati\NovaGlobalFilters\Inputs;

class TextInput extends Input
{
    public function type(): string
    {
        return 'text';
    }

    protected function baseRules(): array
    {
        return ['nullable', 'string', 'max:255'];
    }
}
