<?php

namespace LucaCermenati\NovaGlobalFilters\Filters;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use JsonSerializable;
use LucaCermenati\NovaGlobalFilters\Cards\GlobalFilterCard;
use LucaCermenati\NovaGlobalFilters\Inputs\Input;

/**
 * A filter whose value lives in the session and that metrics can respond to.
 *
 * Use it fluently (GlobalFilter::make('country')->inputs([...])->applyUsing(...))
 * or subclass it and override the hooks.
 */
class GlobalFilter implements JsonSerializable
{
    protected ?string $key = null;

    protected ?string $name = null;

    /** @var array<int, Input>|null */
    protected ?array $inputs = null;

    protected ?Closure $applyCallback = null;

    /** @var array<string, mixed>|null */
    protected ?array $defaultOptions = null;

    public function __construct(?string $key = null, ?string $name = null)
    {
        $this->key = $key;
        $this->name = $name;
    }

    public static function make(?string $key = null, ?string $name = null): static
    {
        return new static($key, $name);
    }

    /**
     * Unique identifier, used in session keys, routes and metric declarations.
     */
    public function key(): string
    {
        return $this->key ?? Str::snake(Str::replaceLast('Filter', '', class_basename(static::class)));
    }

    /**
     * Human readable name shown on the card.
     */
    public function name(): string
    {
        return $this->name ?? Str::headline($this->key());
    }

    public function withName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * The form controls of this filter. Override in a subclass or set fluently.
     *
     * @return array<int, Input>
     */
    public function inputs(): array
    {
        return $this->inputs ?? [];
    }

    /**
     * @param  array<int, Input>  $inputs
     */
    public function withInputs(array $inputs): static
    {
        $this->inputs = $inputs;

        return $this;
    }

    /**
     * Validation rules for the posted value, merged from the inputs.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return collect($this->inputs())
            ->reduce(fn (array $rules, Input $input) => array_merge($rules, $input->validationRules()), []);
    }

    /**
     * Attribute names used in validation messages.
     *
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return collect($this->inputs())
            ->reduce(fn (array $names, Input $input) => array_merge($names, $input->validationAttributes()), []);
    }

    /**
     * Turn the validated raw array into the value handed to apply(). The
     * default drops empty entries so an all-empty submission counts as "no filter".
     */
    public function parse(array $raw): mixed
    {
        return $this->stripEmpty($raw);
    }

    /**
     * Whether a parsed value means "do not filter".
     */
    public function isEmpty(mixed $value): bool
    {
        return blank($value);
    }

    /**
     * The form-shaped array of a parsed value (what the card and session hold).
     *
     * @return array<string, mixed>
     */
    public function serialize(mixed $value): array
    {
        return is_array($value) ? $value : (array) $value;
    }

    /**
     * Short description of the active value, shown on the card.
     */
    public function summary(mixed $value): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        $serialized = $this->serialize($value);

        return collect($this->inputs())
            ->filter(fn (Input $input) => filled($serialized[$input->attribute] ?? null))
            ->map(fn (Input $input) => $input->getLabel().': '.$this->describe($serialized[$input->attribute]))
            ->implode(', ');
    }

    /**
     * Constrain the query. Override in a subclass or set with applyUsing().
     *
     * @param  array<string, mixed>  $options  per-metric options merged over defaultOptions()
     */
    public function apply(Builder $query, mixed $value, array $options = []): void
    {
        if ($this->applyCallback) {
            ($this->applyCallback)($query, $value, $options);
        }
    }

    /**
     * @param  Closure(Builder, mixed, array<string, mixed>): void  $callback
     */
    public function applyUsing(Closure $callback): static
    {
        $this->applyCallback = $callback;

        return $this;
    }

    /**
     * Options a metric may override per declaration, e.g. ['column' => 'created_at'].
     *
     * @return array<string, mixed>
     */
    public function defaultOptions(): array
    {
        return $this->defaultOptions ?? [];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function withDefaultOptions(array $options): static
    {
        $this->defaultOptions = $options;

        return $this;
    }

    /**
     * Default form values when nothing is stored.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return collect($this->inputs())
            ->mapWithKeys(fn (Input $input) => [$input->attribute => $input->getDefault()])
            ->all();
    }

    public function card(): GlobalFilterCard
    {
        return GlobalFilterCard::make($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'key' => $this->key(),
            'name' => $this->name(),
            'inputs' => array_map(fn (Input $input) => $input->jsonSerialize(), $this->inputs()),
        ];
    }

    protected function stripEmpty(array $raw): array
    {
        return collect($raw)
            ->map(fn ($value) => is_array($value) ? $this->stripEmpty($value) : $value)
            ->reject(fn ($value) => $value === null || $value === '' || $value === [])
            ->all();
    }

    protected function describe(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if (is_array($value)) {
            return implode(' → ', array_filter($value, 'filled'));
        }

        return (string) $value;
    }
}
