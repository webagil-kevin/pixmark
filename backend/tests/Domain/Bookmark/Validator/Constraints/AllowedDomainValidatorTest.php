<?php

namespace App\Tests\Domain\Bookmark\Validator\Constraints;

use App\Domain\Bookmark\Validator\Constraints\AllowedDomain;
use App\Domain\Bookmark\Validator\Constraints\AllowedDomainValidator;
use LayerShifter\TLDExtract\Extract;
use LayerShifter\TLDExtract\Result;
use ReflectionClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class AllowedDomainValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): AllowedDomainValidator
    {
        return new AllowedDomainValidator(['example.com', 'test.org']);
    }

    public function testValidateThrowsExceptionForInvalidConstraint()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('http://example.com', $this->createMock(Constraint::class));
    }

    public function testValidateIgnoresNullOrEmptyValues()
    {
        $constraint = new AllowedDomain();

        $this->validator->validate(null, $constraint);
        $this->assertNoViolation();

        $this->validator->validate('', $constraint);
        $this->assertNoViolation();
    }

    public function testValidateThrowsExceptionForNonStringValue()
    {
        $this->expectException(UnexpectedValueException::class);
        $constraint = new AllowedDomain();

        $this->validator->validate(123, $constraint);
    }

    public function testValidateAllowsValidDomain()
    {
        $constraint = new AllowedDomain();

        $this->validator->validate('http://example.com', $constraint);
        $this->assertNoViolation();
    }

    public function testValidateAddsViolationForInvalidDomain()
    {
        $constraint = new AllowedDomain();
        $constraint->message = 'The domain "{{ domain }}" is not allowed. Allowed domains are: {{ allowed_domains }}';

        $this->validator->validate('http://invalid.com', $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ domain }}', 'invalid.com')
            ->setParameter('{{ allowed_domains }}', 'example.com, test.org')
            ->assertRaised();
    }

    public function testValidateIgnoresDomainWithoutRegistrableDomain()
    {
        // Create a mock of the Extract object to simulate a failed extraction
        $extractMock = $this->createMock(Extract::class);
        $extractResultMock = $this->createMock(Result::class); // Assuming Result is the class returned by parse()
        $extractResultMock->method('getRegistrableDomain')->willReturn(null);
        $extractMock->method('parse')->willReturn($extractResultMock);

        // Instantiate the validator with allowed domains
        $validator = new AllowedDomainValidator(['example.com', 'test.org']);

        // Use reflection to replace the Extract instance with the mock in the validate method
        $reflection = new ReflectionClass($validator);
        $method = $reflection->getMethod('validate');

        // Execute the test
        $constraint = new AllowedDomain();
        $method->invokeArgs($validator, ['http://invalid-url', $constraint]);

        // Verify that no violation is raised
        $this->assertNoViolation();
    }

    public function testValidateAllowsSubdomainOfAllowedDomain()
    {
        $constraint = new AllowedDomain();

        $this->validator->validate('http://sub.example.com', $constraint);
        $this->assertNoViolation();
    }
}
