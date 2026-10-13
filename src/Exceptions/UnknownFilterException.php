<?php

namespace LucaCermenati\NovaGlobalFilters\Exceptions;

use InvalidArgumentException;

class UnknownFilterException extends InvalidArgumentException
{
    public static function forKey(string $key): self
    {
        return new self(sprintf(
            'Global filter [%s] is not registered. Register it with NovaGlobalFilters::register() in your NovaServiceProvider.',
            $key
        ));
    }
}
