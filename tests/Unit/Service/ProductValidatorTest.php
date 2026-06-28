<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Exception\ValidationException;
use App\Service\ProductValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ProductValidator
 */
final class ProductValidatorTest extends TestCase
{
    private ProductValidator $validator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->validator = new ProductValidator();
    }

    /**
     * A fully valid create payload passes validation.
     *
     * @return void
     */
    public function testValidCreateDataPasses(): void
    {
        $this->validator->validateForCreate($this->validData());

        $this->addToAssertionCount(1);
    }

    /**
     * A null description is accepted (the field is optional).
     *
     * @return void
     */
    public function testNullDescriptionIsAllowed(): void
    {
        $data = $this->validData();
        $data['description'] = null;

        $this->validator->validateForCreate($data);

        $this->addToAssertionCount(1);
    }

    /**
     * A missing required field fails create validation.
     *
     * @return void
     */
    public function testMissingRequiredFieldFailsOnCreate(): void
    {
        $data = $this->validData();
        unset($data['brand']);

        $this->assertCreateInvalid($data, 'brand');
    }

    /**
     * A name exceeding the length limit fails validation.
     *
     * @return void
     */
    public function testNameTooLongFails(): void
    {
        $data = $this->validData();
        $data['name'] = str_repeat('x', 256);

        $this->assertCreateInvalid($data, 'name');
    }

    /**
     * A negative net price fails validation.
     *
     * @return void
     */
    public function testNegativePriceFails(): void
    {
        $data = $this->validData();
        $data['price_excl_vat'] = '-1.00';

        $this->assertCreateInvalid($data, 'price_excl_vat');
    }

    /**
     * A price with more than two decimal places fails validation.
     *
     * @return void
     */
    public function testPriceWithThreeDecimalsFails(): void
    {
        $data = $this->validData();
        $data['price_excl_vat'] = '1.234';

        $this->assertCreateInvalid($data, 'price_excl_vat');
    }

    /**
     * A non-string (float) price is rejected; prices are decimal strings.
     *
     * @return void
     */
    public function testFloatPriceIsRejected(): void
    {
        $data = $this->validData();
        $data['price_excl_vat'] = 12.99;

        $this->assertCreateInvalid($data, 'price_excl_vat');
    }

    /**
     * A VAT rate above 100 fails validation.
     *
     * @return void
     */
    public function testVatRateAboveHundredFails(): void
    {
        $data = $this->validData();
        $data['vat_rate'] = '150.00';

        $this->assertCreateInvalid($data, 'vat_rate');
    }

    /**
     * An unexpected (unknown) field fails validation.
     *
     * @return void
     */
    public function testUnexpectedFieldFails(): void
    {
        $data = $this->validData();
        $data['unexpected'] = 'x';

        $this->assertCreateInvalid($data, 'unexpected');
    }

    /**
     * A partial update with a single valid field passes.
     *
     * @return void
     */
    public function testPartialUpdatePassesWithSingleField(): void
    {
        $this->validator->validateForUpdate(['price_excl_vat' => '9.99']);

        $this->addToAssertionCount(1);
    }

    /**
     * A partial update still validates the fields that are present.
     *
     * @return void
     */
    public function testPartialUpdateStillValidatesPresentField(): void
    {
        $this->assertUpdateInvalid(['vat_rate' => '200'], 'vat_rate');
    }

    /**
     * Assert that create validation rejects the data with an error on the given field.
     *
     * @param array $data
     * @param string $field
     * @return void
     */
    private function assertCreateInvalid(array $data, string $field): void
    {
        try {
            $this->validator->validateForCreate($data);
        } catch (ValidationException $e) {
            self::assertArrayHasKey($field, $e->errors());

            return;
        }

        self::fail('Expected ValidationException was not thrown.');
    }

    /**
     * Assert that update validation rejects the data with an error on the given field.
     *
     * @param array $data
     * @param string $field
     * @return void
     */
    private function assertUpdateInvalid(array $data, string $field): void
    {
        try {
            $this->validator->validateForUpdate($data);
        } catch (ValidationException $e) {
            self::assertArrayHasKey($field, $e->errors());

            return;
        }

        self::fail('Expected ValidationException was not thrown.');
    }

    /**
     * A fully valid create payload.
     *
     * @return array
     */
    private function validData(): array
    {
        return [
            'name'           => 'Widget',
            'description'    => 'A useful widget',
            'brand'          => 'Acme',
            'category'       => 'Tools',
            'price_excl_vat' => '10.00',
            'vat_rate'       => '23.00',
        ];
    }
}
