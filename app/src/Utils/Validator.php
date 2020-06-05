<?php

namespace App\Utils;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use function Symfony\Component\String\u;

/**
 * Class Validator validates input data related to Product
 */
class Validator
{
    const REQUIRED_COLUMNS = ['sku', 'description', 'normalPrice'];

    const ALLOWED_COLUMNS = [...self::REQUIRED_COLUMNS, 'specialPrice'];

    const MAX_NUMBER_OF_DECIMALS_IN_PRICE = 2;

    /**
     * @param array $record
     * @return array
     */
    public function validate(array $record): array
    {
        if (empty($record)) {
            throw new InvalidArgumentException('The record can not be empty.');
        }

        $record['sku'] = $this->validateSku($record['sku']);
        $record['description'] = $this->validateDescription($record['description']);
        $record['normalPrice'] = $this->validateNormalPrice($record['normalPrice']);
        $record['specialPrice'] = $this->validateSpecialPrice(
            $record['normalPrice'],
            $record['specialPrice'] ?? null
        );

        return $record;
    }

    /**
     * @param array $columns
     * @return array
     */
    public function validateColumns(array $columns): array
    {
        if (empty($columns)) {
            throw new InvalidArgumentException(
                sprintf('Columns: %s are not found in file', implode(', ', self::REQUIRED_COLUMNS))
            );
        }

        foreach (self::REQUIRED_COLUMNS as $REQUIRED_COLUMN) {
            if (!in_array($REQUIRED_COLUMN, $columns)) {
                throw new InvalidArgumentException(sprintf('Required column %s does not exist', $REQUIRED_COLUMN));
            }
        }

        foreach ($columns as $column) {
            if (!in_array($column, self::ALLOWED_COLUMNS)) {
                throw new InvalidArgumentException(sprintf('Required column %s does not exist', $column));
            }
        }

        return $columns;
    }

    /**
     * @param string|null $sku
     * @return string
     */
    public function validateSku(?string $sku): string
    {
        if (empty($sku)) {
            throw new InvalidArgumentException('The sku can not be empty.');
        }

        if (u($sku)->trim()->length() < 6) {
            throw new InvalidArgumentException('The sku must be at least 6 characters long.');
        }

        if (u($sku)->length() > 200 ) {
            throw new InvalidArgumentException('The sku must be no longer than 100 characters.');
        }

        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $sku)) {
            throw new InvalidArgumentException('The sku must be alpha numeric with underscore or minus chars.');
        }

        return $sku;
    }

    /**
     * @param string|null $description
     * @return string
     */
    public function validateDescription(?string $description): string
    {
        if (empty($description)) {
            throw new InvalidArgumentException('The description can not be empty.');
        }

        if (u($description)->length() > 255 ) {
            throw new InvalidArgumentException('The description must be no longer than 255 characters.');
        }

        // find any invalid or hidden characters
        if (preg_match('/[\x00-\x1F\x80-\xFF`]/', $description)) {
            throw new InvalidArgumentException('The description must be alpha numeric.');
        }

        return $description;
    }

    /**
     * @param $normalPrice
     * @return float
     */
    public function validateNormalPrice($normalPrice): float
    {
        if (empty($normalPrice)) {
            throw new InvalidArgumentException('The normal price can not be empty.');
        }

        if (!is_numeric($normalPrice) || $normalPrice < 0) {
            throw new InvalidArgumentException('The normal price must be positive numeric.');
        }

        $this->validateDecimalsInPrice($normalPrice, 'normal price');

        return floatval($normalPrice);
    }

    /**
     * @param $normalPrice
     * @param $specialPrice
     * @return float|null
     */
    public function validateSpecialPrice($normalPrice, $specialPrice): ?float
    {
        if (empty($specialPrice)) {
            return null;
        }

        if (!is_numeric($specialPrice) || $specialPrice < 0) {
            throw new InvalidArgumentException('The special price must be positive numeric.');
        }

        if ($normalPrice <= $specialPrice) {
            throw new InvalidArgumentException('The special price must be less than normal price.');
        }

        $this->validateDecimalsInPrice($specialPrice, 'special price');

        return floatval($specialPrice);
    }

    /**
     * @param float $price
     * @param string $field
     */
    private function validateDecimalsInPrice(float $price, string $field): void
    {
        $decimalsCount = (strlen($price) - strrpos($price, '.') - 1);
        if ($decimalsCount > self::MAX_NUMBER_OF_DECIMALS_IN_PRICE) {
            throw new InvalidArgumentException(
                sprintf('The %s must maximum %s decimal.', $field, self::MAX_NUMBER_OF_DECIMALS_IN_PRICE)
            );
        }
    }
}
