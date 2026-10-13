<?php

use LucaCermenati\NovaGlobalFilters\Facades\NovaGlobalFilters;

beforeEach(function () {
    $this->actingAs($this->user());
});

it('returns the empty state for a filter without a value', function () {
    $this->getJson('/nova-vendor/nova-global-filters/filters/date_range')
        ->assertOk()
        ->assertExactJson([
            'filter' => 'date_range',
            'scope' => 'default',
            'value' => ['period' => null],
            'summary' => null,
            'hasValue' => false,
        ]);
});

it('stores a valid value and returns the summary', function () {
    $this->postJson('/nova-vendor/nova-global-filters/filters/date_range', [
        'period' => ['start' => '2026-01-10', 'end' => '2026-01-20'],
    ])
        ->assertOk()
        ->assertJson([
            'value' => ['period' => ['start' => '2026-01-10', 'end' => '2026-01-20']],
            'summary' => '10 Jan 2026 → 20 Jan 2026',
            'hasValue' => true,
        ]);

    expect(NovaGlobalFilters::rawValue('date_range'))->toBe(['period' => ['start' => '2026-01-10', 'end' => '2026-01-20']]);
});

it('rejects invalid values with the input error keys', function () {
    $response = $this->postJson('/nova-vendor/nova-global-filters/filters/date_range', [
        'period' => ['start' => '2026-01-20', 'end' => '2026-01-10'],
    ])->assertStatus(422)->assertJsonValidationErrors(['period.end']);

    // Messages use the input labels, not the raw attribute names.
    expect($response->json('errors')['period.end'][0])->toContain('Until')->toContain('From');

    $this->postJson('/nova-vendor/nova-global-filters/filters/country', ['country' => 'XX'])
        ->assertStatus(422)->assertJsonValidationErrors(['country']);

    expect(NovaGlobalFilters::rawValue('date_range'))->toBeNull();
});

it('stores under a scope and clears it independently', function () {
    $this->postJson('/nova-vendor/nova-global-filters/filters/country', ['country' => 'IT', 'scope' => 'sales'])
        ->assertOk()->assertJson(['scope' => 'sales', 'hasValue' => true]);

    expect(NovaGlobalFilters::rawValue('country'))->toBeNull()
        ->and(NovaGlobalFilters::rawValue('country', 'sales'))->toBe(['country' => 'IT']);

    $this->deleteJson('/nova-vendor/nova-global-filters/filters/country', ['scope' => 'sales'])
        ->assertOk()->assertJson(['hasValue' => false, 'value' => ['country' => null]]);

    expect(NovaGlobalFilters::rawValue('country', 'sales'))->toBeNull();
});

it('returns 404 for unknown filters', function () {
    $this->getJson('/nova-vendor/nova-global-filters/filters/nope')->assertNotFound();
    $this->postJson('/nova-vendor/nova-global-filters/filters/nope', [])->assertNotFound();
});
