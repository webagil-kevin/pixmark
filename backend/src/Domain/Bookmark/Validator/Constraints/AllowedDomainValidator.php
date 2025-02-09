<?php

namespace App\Domain\Bookmark\Validator\Constraints;

use LayerShifter\TLDExtract\Extract;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

use function in_array;
use function is_string;

class AllowedDomainValidator extends ConstraintValidator
{
    /**
     * @param string[] $allowedDomains
     */
    public function __construct(
        private readonly array $allowedDomains,
    ) {
    }

    /**
     * Validates a value against a constraint that checks if the domain is allowed.
     *
     * @param mixed      $value      the value to validate
     * @param Constraint $constraint the constraint being validated
     *
     * @throws UnexpectedTypeException  if the constraint is not an instance of AllowedDomain
     * @throws UnexpectedValueException if the value type is not valid
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AllowedDomain) {
            throw new UnexpectedTypeException($constraint, AllowedDomain::class);
        }

        // Ignore null or empty values; other constraints (NotBlank, Url, etc.) handle them.
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $extract = new Extract();
        $result = $extract->parse($value);
        $registrableDomain = $result->getRegistrableDomain();

        if (!$registrableDomain) {
            return;
        }

        if (!in_array($registrableDomain, $this->allowedDomains, true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ domain }}', $registrableDomain)
                ->setParameter('{{ allowed_domains }}', implode(', ', $this->allowedDomains))
                ->addViolation();
        }
    }
}
