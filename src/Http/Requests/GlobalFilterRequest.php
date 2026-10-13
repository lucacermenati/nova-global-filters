<?php

namespace LucaCermenati\NovaGlobalFilters\Http\Requests;

use Laravel\Nova\Http\Requests\NovaRequest;
use LucaCermenati\NovaGlobalFilters\Exceptions\UnknownFilterException;
use LucaCermenati\NovaGlobalFilters\Filters\GlobalFilter;
use LucaCermenati\NovaGlobalFilters\GlobalFilterManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GlobalFilterRequest extends NovaRequest
{
    protected ?GlobalFilter $resolvedFilter = null;

    public function filter(): GlobalFilter
    {
        if ($this->resolvedFilter) {
            return $this->resolvedFilter;
        }

        try {
            return $this->resolvedFilter = app(GlobalFilterManager::class)->filter((string) $this->route('filter'));
        } catch (UnknownFilterException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }

    public function scope(): ?string
    {
        return $this->input('scope') ?: null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = ['scope' => ['nullable', 'string', 'alpha_dash', 'max:64']];

        if ($this->isMethod('POST')) {
            $rules = array_merge($rules, $this->filter()->rules());
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->isMethod('POST') ? $this->filter()->validationAttributes() : [];
    }

    /**
     * The validated filter value, without the scope.
     *
     * @return array<string, mixed>
     */
    public function filterValue(): array
    {
        return collect($this->validated())->except('scope')->all();
    }
}
