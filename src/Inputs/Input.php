<?php

namespace LucaCermenati\NovaGlobalFilters\Inputs;

use Illuminate\Support\Str;
use JsonSerializable;

/**
 * One form control of a global filter. Inputs describe themselves to the
 * generic Vue card (type, label, options…) and contribute validation rules.
 */
abstract class Input implements JsonSerializable
{
    protected ?string $label = null;

    protected ?string $help = null;

    protected ?string $placeholder = null;

    protected mixed $default = null;

    /** @var array<int, mixed> */
    protected array $extraRules = [];

    final public function __construct(public readonly string $attribute, ?string $label = null)
    {
        $this->label = $label;
    }

    public static function make(string $attribute, ?string $label = null): static
    {
        return new static($attribute, $label);
    }

    /**
     * The component type rendered by the Vue card.
     */
    abstract public function type(): string;

    /**
     * Validation rules intrinsic to the input type.
     *
     * @return array<int, mixed>
     */
    abstract protected function baseRules(): array;

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function help(string $help): static
    {
        $this->help = $help;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Append validation rules to the intrinsic ones.
     */
    public function rules(mixed ...$rules): static
    {
        $this->extraRules = array_merge($this->extraRules, $this->flattenRules($rules));

        return $this;
    }

    /**
     * Validation rules keyed by attribute, ready for the validator.
     *
     * @return array<string, array<int, mixed>>
     */
    public function validationRules(): array
    {
        return [$this->attribute => array_merge($this->baseRules(), $this->extraRules)];
    }

    /**
     * Human readable names for validation messages, keyed by rule attribute.
     *
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [$this->attribute => $this->getLabel()];
    }

    public function getLabel(): string
    {
        return $this->label ?? Str::headline($this->attribute);
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Extra JSON meta of the concrete input.
     *
     * @return array<string, mixed>
     */
    protected function meta(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'type' => $this->type(),
            'attribute' => $this->attribute,
            'label' => $this->getLabel(),
            'help' => $this->help,
            'placeholder' => $this->placeholder,
            'default' => $this->default,
        ], $this->meta());
    }

    /**
     * @param  array<int, mixed>  $rules
     * @return array<int, mixed>
     */
    protected function flattenRules(array $rules): array
    {
        $flat = [];

        foreach ($rules as $rule) {
            if (is_array($rule)) {
                $flat = array_merge($flat, $this->flattenRules($rule));
            } elseif (is_string($rule) && str_contains($rule, '|')) {
                $flat = array_merge($flat, explode('|', $rule));
            } else {
                $flat[] = $rule;
            }
        }

        return $flat;
    }
}
