<?php
namespace App\Service;

use App\Entity\Product;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\AbstractCsv;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class ProductService imports product data from file to database
 */
class ProductService
{
    const MAX_NUMBER_RECORDS_TO_BE_SAVED_AT_ONCE = 100;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var int
     */
    private $updatedProductsCount = 0;

    /**
     * @var int
     */
    private $createdProductsCount = 0;

    /**
     * @var int
     */
    private $successfulProductsCount = 0;

    /**
     * @var int
     */
    private $productsWithErrorsCount = 0;

    /**
     * ProductService constructor.
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param Validator $validator
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, Validator $validator)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->validator = $validator;
    }

    /**
     * @param AbstractCsv $csv
     */
    public function importCsv(AbstractCsv $csv): void
    {
        $this->validator->validateColumns($csv->getHeader());

        $recordsCounter = 0;
        $flushRecordsMessage = 'Batch saving to Database %d records';

        foreach ($csv as $record) {
            try {
                $record = $this->validator->validate($record);
                $this->saveProduct($record);

                if ($recordsCounter === self::MAX_NUMBER_RECORDS_TO_BE_SAVED_AT_ONCE) {
                    $this->em->flush();
                    $recordsCounter = 0;
                    $this->logger->info(sprintf($flushRecordsMessage, $recordsCounter));
                }

                $recordsCounter++;
                $this->successfulProductsCount++;
            } catch (InvalidArgumentException $exception) {
                $this->productsWithErrorsCount++;
                $this->logger->warning('Skip product import: ' . $exception->getMessage());
            }
        }

        $this->logger->info(sprintf($flushRecordsMessage, $recordsCounter));
        $this->em->flush();
    }

    /**
     * @return int
     */
    public function countUpdatedProducts(): int
    {
        return $this->updatedProductsCount;
    }

    /**
     * @return int
     */
    public function countCreatedProducts(): int
    {
        return $this->createdProductsCount;
    }

    /**
     * @return int
     */
    public function countSuccessfulProducts(): int
    {
        return $this->successfulProductsCount;
    }


    /**
     * @return int
     */
    public function countProductsWithErrors(): int
    {
        return $this->productsWithErrorsCount;
    }

    /**
     * @param array $record
     * @return Product
     */
    private function saveProduct(array $record): void
    {
        // Note: Slow but more reliable in case we have big amount of data
        $product = $this->em->getRepository(Product::class)->findOneBySku($record['sku']);

        if (null === $product) {
            $this->createNew($record);
            $this->logger->info('New product is created. Sku: ' . $record['sku']);
        } else {
            $this->updateExisting($record, $product);
            $this->logger->info('Product is updated. Sku: ' . $record['sku']);
        }
    }

    private function isProductDataUpdated(array $record, Product $product): bool
    {
        return !!array_diff(
            $record,
            [$product->getSku(), $product->getDescription(), $product->getNormalPrice(), $product->getSpecialPrice()]
        );
    }

    /**
     * @param array $record
     */
    private function createNew(array $record): void
    {
        $product = (new Product())
            ->setSku($record['sku'])
            ->setDescription($record['description'])
            ->setNormalPrice($record['normalPrice'])
            ->setSpecialPrice($record['specialPrice']);

        // To avoid duplicate entry issue we need to flush new records
        $this->em->persist($product);
        $this->em->flush();

        $this->createdProductsCount++;
    }

    /**
     * @param array $record
     * @param Product $product
     */
    private function updateExisting(array $record, Product $product): void
    {
        if ($this->isProductDataUpdated($record, $product)) {
            $product->update(
                $record['sku'],
                $record['description'],
                $record['normalPrice'],
                $record['specialPrice']
            );

            $this->updatedProductsCount++;
        }
    }
}
