<?php

namespace LucaCermenati\NovaGlobalFilters\Storage;

use Illuminate\Contracts\Session\Session;

/**
 * Session-backed storage of the validated raw filter values.
 */
class FilterStore
{
    public function __construct(
        protected Session $session,
        protected string $prefix,
        protected string $defaultScope,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $key, ?string $scope = null): ?array
    {
        $value = $this->session->get($this->sessionKey($key, $scope));

        return is_array($value) ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    public function put(string $key, array $raw, ?string $scope = null): void
    {
        $this->session->put($this->sessionKey($key, $scope), $raw);
    }

    public function forget(string $key, ?string $scope = null): void
    {
        $this->session->forget($this->sessionKey($key, $scope));
    }

    /**
     * Forget every filter of a scope (or of the default scope).
     */
    public function flush(?string $scope = null): void
    {
        $this->session->forget($this->prefix.'.'.$this->scope($scope));
    }

    public function scope(?string $scope): string
    {
        return $scope ?: $this->defaultScope;
    }

    public function sessionKey(string $key, ?string $scope = null): string
    {
        return "{$this->prefix}.{$this->scope($scope)}.{$key}";
    }
}
