<?php

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute] class AllowedDomain extends Constraint
{
    public string $message = 'The domain "{{ domain }}" is not allowed. Allowed domains are: {{ allowed_domains }}.';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
