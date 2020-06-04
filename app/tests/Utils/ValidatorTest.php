<?php

namespace App\Tests\Utils;

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testValidateSku(): void
    {
        $test = 'username';

        $this->assertSame($test, $this->validator->validateSku($test));
    }

    public function testValidateSkuEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The sku can not be empty.');
        $this->validator->validateSku(null);
    }

    public function testValidateSkuInvalid(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The sku must be at least 6 characters long.');
        $this->validator->validateSku('BP063');
    }


    public function testValidateDescriptionEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The description can not be empty.');
        $this->validator->validateDescription(null);
    }

    public function testValidateNormalPriceEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The normal price can not be empty.');
        $this->validator->validateNormalPrice(null);
    }

    public function testValidateNormalPriceInvalid(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The normal price must be positive numeric.');
        $this->validator->validateNormalPrice('INVALID');
    }

    public function testValidateSpecialPriceInvalid(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The special price must be less than normal price.');
        $this->validator->validateSpecialPrice(11.11, 22.22);
    }

    public function testValidateEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The record can not be empty.');
        $this->validator->validate([]);
    }

    public function testValidateColumnsInvalid(): void
    {
        $missingColumn = 'description';

        $this->expectException('Exception');
        $this->expectExceptionMessage(sprintf('Required column %s does not exist', $missingColumn));
        $this->validator->validateColumns(['sku', 'normalPrice']);
    }

    public function testValidateValid(): void
    {
        $record = ['sku' => 'BP063-0001', 'description' => 'Prod 1', 'normalPrice' => 44.99];
        $expected = ['sku' => 'BP063-0001', 'description' => 'Prod 1', 'normalPrice' => 44.99, 'specialPrice' => null];

        $actual = $this->validator->validate($record);
        $this->assertSame($expected, $actual);
    }
}
