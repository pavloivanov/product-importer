<?php

namespace App\Tests\Utils;

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
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

    public function testValidateInvalid(): void
    {
        $invalidColumn = 'nonExistentColumn';

        $this->expectException('Exception');
        $this->expectExceptionMessage(sprintf('Column %s does not exist', $invalidColumn));
        $this->validator->validate([$invalidColumn => 'value 1', 'sku' => 'BP063-0001']);
    }

    public function testValidateColumnsInvalid(): void
    {
        $missingColumn = 'description';

        $this->expectException('Exception');
        $this->expectExceptionMessage(sprintf('Required column %s does not exist', $missingColumn));
        $this->validator->validateColumns(['sku', 'normalPrice']);
    }
}
