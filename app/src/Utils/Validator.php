<?php

namespace App\Utils;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use function Symfony\Component\String\u;

/**
 * Class Validator
 */
class Validator
{
    const ALLOWED_COLUMNS = ['sku', 'description', 'normalPrice', 'specialPrice'];
    /**
     * @param array $record
     * @return array
     */
    public function validate(array $record): array
    {
        if (empty($record)) {
            throw new InvalidArgumentException('The record can not be empty.');
        }

//        var_dump($record);

        foreach ($record as $column => $value) {
            if (!in_array($column, self::ALLOWED_COLUMNS)) {
                throw new InvalidArgumentException(sprintf('Column %s does not exist', $column));
            }
        }

        $record['sku'] = $this->validateSku($record['sku']);
        $record['description'] = $this->validateDescription($record['description']);
        $record['normalPrice'] = $this->validateNormalPrice($record['normalPrice']);
        $record['specialPrice'] = $this->validateSpecialPrice($record['normalPrice'], $record['specialPrice']);

        return $record;
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

        if (!is_numeric($normalPrice)) {
            throw new InvalidArgumentException('The normal price must be numeric.');
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

        if ($normalPrice <= $specialPrice) {
            throw new InvalidArgumentException('The special price must be less than normal price.');
        }

        return floatval($specialPrice);
    }
}
