<?php
namespace App\Tests\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use App\Utils\Validator;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use League\Csv\Reader;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    /**
     * @var Reader
     */
    private $csv;

    /**
     * @var array
     */
    private $expected = [
        ['sku', 'description', 'normalPrice', 'specialPrice'],
        ['BP063-0001', 'Product 1 description', 44.99, 22.99],
    ];

    protected function setUp(): void
    {
        $tmp = new \SplTempFileObject();
        foreach ($this->expected as $row) {
            $tmp->fputcsv($row);
        }

        $this->csv = Reader::createFromFileObject($tmp);
        $this->csv->setHeaderOffset(0);
    }

    public function tearDown(): void
    {
        unset($this->csv);
    }

    public function testImportCsvWithNewProducts(): void
    {
        $productService = $this->createProductService();
        $productService->importCsv($this->csv);

        $this->assertSame(1, $productService->countCreatedProducts());
        $this->assertSame(1, $productService->countSuccessfulProducts());
        $this->assertSame(0, $productService->countProductsWithErrors());
    }

    public function testImportCsvWithExistingProducts(): void
    {
        $productService = $this->createProductService(new Product());
        $productService->importCsv($this->csv);

        $this->assertSame(1, $productService->countUpdatedProducts());
        $this->assertSame(1, $productService->countSuccessfulProducts());
        $this->assertSame(0, $productService->countProductsWithErrors());
    }

    private function createProductRepositoryMock(?Product $product = null)
    {
        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects($this->any())
            ->method('findOneBySku')
            ->willReturn($product);

        return $productRepository;
    }

    private function createEntityManagerMock($productRepository)
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($productRepository);

        $configuration = $this->createMock(Configuration::class);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $entityManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        return $entityManager;
    }

    private function createValidatorMock()
    {
        $validatedRecord = array_combine($this->expected[0], $this->expected[1]);

        $validator = $this->createMock(Validator::class);
        $validator->expects($this->any())
            ->method('validate')
            ->willReturn($validatedRecord);

        return $validator;
    }

    private function createProductService($product = null): ProductService
    {
        $productRepository = $this->createProductRepositoryMock($product);
        $entityManager = $this->createEntityManagerMock($productRepository);
        $validator = $this->createValidatorMock();

        return new ProductService(
            $entityManager,
            $this->createMock(Logger::class),
            $validator
        );
    }
}
