<?php

use LucaCermenati\NovaGlobalFilters\Inputs\BooleanInput;
use LucaCermenati\NovaGlobalFilters\Inputs\DateInput;
use LucaCermenati\NovaGlobalFilters\Inputs\SelectInput;
use LucaCermenati\NovaGlobalFilters\Inputs\TextInput;

it('builds select rules from the option keys and serializes options', function () {
    $input = SelectInput::make('country')->options(['NL' => 'Netherlands', 'IT' => 'Italy'])->anyLabel('Everywhere');

    expect($input->validationRules())->toBe(['country' => ['nullable', 'in:NL,IT']])
        ->and($input->jsonSerialize())->toMatchArray([
            'type' => 'select',
            'attribute' => 'country',
            'label' => 'Country',
            'anyLabel' => 'Everywhere',
            'options' => [['value' => 'NL', 'label' => 'Netherlands'], ['value' => 'IT', 'label' => 'Italy']],
        ]);
});

it('appends extra rules to the intrinsic ones', function () {
    expect(TextInput::make('q')->rules('min:3', ['max:10'])->validationRules())
        ->toBe(['q' => ['nullable', 'string', 'max:255', 'min:3', 'max:10']])
        ->and(TextInput::make('q')->rules('min:3|max:10')->validationRules()['q'])
        ->toContain('min:3', 'max:10');
});

it('describes date and boolean inputs', function () {
    expect(DateInput::make('from', 'Since')->min('2020-01-01')->validationRules())
        ->toBe(['from' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:2020-01-01']])
        ->and(DateInput::make('from')->max('2030-01-01')->jsonSerialize())->toMatchArray(['type' => 'date', 'max' => '2030-01-01', 'min' => null])
        ->and(BooleanInput::make('only_active')->default(true)->jsonSerialize())->toMatchArray(['type' => 'boolean', 'label' => 'Only Active', 'default' => true])
        ->and(BooleanInput::make('x')->validationRules())->toBe(['x' => ['nullable', 'boolean']]);
});
