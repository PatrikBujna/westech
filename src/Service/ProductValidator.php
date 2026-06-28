<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ValidationException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;

/**
 * Validates the product payload before it reaches the database.
 */
final readonly class ProductValidator
{
    private ValidatorInterface $validator;

    public function __construct()
    {
        $this->validator = Validation::createValidator();
    }

    /**
     * Validate data for creating a product (all required fields must be present).
     *
     * @param array $data
     * @return void
     * @throws ValidationException When validation fails.
     */
    public function validateForCreate(array $data): void
    {
        $this->validate($data, allowMissingFields: false);
    }

    /**
     * Validate data for updating a product (only present fields are checked).
     *
     * @param array $data
     * @return void
     * @throws ValidationException When validation fails.
     */
    public function validateForUpdate(array $data): void
    {
        $this->validate($data, allowMissingFields: true);
    }

    /**
     * Run the collection constraint and convert violations into an exception.
     *
     * @param array $data
     * @param bool $allowMissingFields
     * @return void
     * @throws ValidationException When validation fails.
     */
    private function validate(array $data, bool $allowMissingFields): void
    {
        $violations = $this->validator->validate($data, $this->constraint($allowMissingFields));

        if (count($violations) === 0) {
            return;
        }

        $errors = [];
        foreach ($violations as $violation) {
            $field = trim($violation->getPropertyPath(), '[]');
            $errors[$field][] = (string) $violation->getMessage();
        }

        throw new ValidationException($errors);
    }

    /**
     * Build the collection constraint describing the product fields.
     *
     * @param bool $allowMissingFields
     * @return Assert\Collection
     */
    private function constraint(bool $allowMissingFields): Assert\Collection
    {
        return new Assert\Collection(
            fields: [
                'name'           => [new Assert\NotBlank(), new Assert\Length(max: 255)],
                'description'    => new Assert\Optional([new Assert\Length(max: 65535)]),
                'brand'          => [new Assert\NotBlank(), new Assert\Length(max: 255)],
                'category'       => [new Assert\NotBlank(), new Assert\Length(max: 255)],
                'price_excl_vat' => [
                    new Assert\NotBlank(),
                    new Assert\Type('string'),
                    new Assert\Regex(
                        pattern: '/^\d{1,8}(\.\d{1,2})?$/',
                        message: 'Price must be a non-negative decimal string with up to 2 decimal places.',
                    ),
                ],
                'vat_rate'       => [
                    new Assert\NotBlank(),
                    new Assert\Type('string'),
                    new Assert\Regex(
                        pattern: '/^\d{1,3}(\.\d{1,2})?$/',
                        message: 'VAT rate must be a decimal string with up to 2 decimal places.',
                    ),
                    new Assert\Range(
                        notInRangeMessage: 'VAT rate must be between {{ min }} and {{ max }}.',
                        min: 0,
                        max: 100,
                    ),
                ],
            ],
            allowExtraFields: false,
            allowMissingFields: $allowMissingFields,
        );
    }
}
