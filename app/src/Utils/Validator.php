<?php

namespace App\Utils;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use function Symfony\Component\String\u;

/**
 * Class Validator validates input data related to Product
 */
class Validator
{
    const REQUIRED_COLUMNS = ['sku', 'description', 'normalPrice', 'specialPrice'];

    /**
     * @param array $record
     * @return array
     */
    public function validate(array $record): array
    {
        if (empty($record)) {
            throw new InvalidArgumentException('The record can not be empty.');
        }

        foreach ($record as $column => $value) {
            if (!in_array($column, self::REQUIRED_COLUMNS)) {
                throw new InvalidArgumentException(sprintf('Column %s does not exist', $column));
            }
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

        foreach (self::REQUIRED_COLUMNS as $ALLOWED_COLUMN) {
            if (!in_array($ALLOWED_COLUMN, $columns)) {
                throw new InvalidArgumentException(sprintf('Required column %s does not exist', $ALLOWED_COLUMN));
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

        // Remove any invalid or hidden characters
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $sku);
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

        // Remove any invalid or hidden characters
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $description);
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

        return floatval($specialPrice);
    }
}
