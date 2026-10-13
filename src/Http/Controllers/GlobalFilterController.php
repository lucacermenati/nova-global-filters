<?php

namespace LucaCermenati\NovaGlobalFilters\Http\Controllers;

use Illuminate\Http\JsonResponse;
use LucaCermenati\NovaGlobalFilters\GlobalFilterManager;
use LucaCermenati\NovaGlobalFilters\Http\Requests\GlobalFilterRequest;

class GlobalFilterController
{
    public function __construct(protected GlobalFilterManager $manager) {}

    public function show(GlobalFilterRequest $request): JsonResponse
    {
        return $this->state($request);
    }

    public function update(GlobalFilterRequest $request): JsonResponse
    {
        $this->manager->set($request->filter()->key(), $request->filterValue(), $request->scope());

        return $this->state($request);
    }

    public function destroy(GlobalFilterRequest $request): JsonResponse
    {
        $this->manager->clear($request->filter()->key(), $request->scope());

        return $this->state($request);
    }

    protected function state(GlobalFilterRequest $request): JsonResponse
    {
        $filter = $request->filter();
        $scope = $request->scope();
        $value = $this->manager->value($filter->key(), $scope);

        return response()->json([
            'filter' => $filter->key(),
            'scope' => $this->manager->store()->scope($scope),
            'value' => $value === null ? $filter->defaults() : $filter->serialize($value),
            'summary' => $value === null ? null : $filter->summary($value),
            'hasValue' => $value !== null,
        ]);
    }
}
