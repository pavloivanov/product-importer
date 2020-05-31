<?php

namespace App\Command;

use App\Entity\Product;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProductCommand extends Command
{
    private $em;

    private $validator;

    private $logger;

    private $createdProductsCount = 0;

    private $updatedProductsCount = 0;

    private $productsWithErrorsCount = 0;

    private $successfulProductsCount = 0;

    public function __construct(EntityManagerInterface $em, Validator $validator, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:import-product')
            ->setDescription('Imports CSV files to Database.')
            ->setHelp('This command allows to import CSV files from folder data/input to Database.');

        $this->addArgument('csvFilename', InputArgument::REQUIRED, 'Filename of csv you want to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csvFilename = $input->getArgument('csvFilename');

        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting to import product');

        $csv = Reader::createFromPath('%kernel.root_dir%/../data/input/' . $csvFilename);
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            try {
                $record = $this->validator->validate($record);
                $product = $this->fetchProduct($record);
                $this->em->persist($product);

                $this->successfulProductsCount++;
            } catch (InvalidArgumentException $exception) {
                $this->productsWithErrorsCount++;
                $this->logger->warning('Skip product import: ' . $exception->getMessage());
            }
        }

        $this->em->flush();

        if ($this->productsWithErrorsCount > 0) {
            $io->caution($this->productsWithErrorsCount . ' invalid products are skipped');
        }

        $io->note($this->createdProductsCount . ' Created Products.');
        $io->note($this->updatedProductsCount . ' Updated Products.');

        $io->success(
            $this->successfulProductsCount . ' products are imported successfully.'
        );

        // TODO: remove processed file from data/input folder

        return 0;
    }

    private function fetchProduct(array $record): Product
    {
        // Note: Slow but more reliable in case we have big amount of data
        $product = $this->em->getRepository(Product::class)
            ->findOneBy(['sku' => $record['sku']]);

        if (null === $product) {
            $product = (new Product())
                ->setSku($record['sku'])
                ->setDescription($record['description'])
                ->setNormalPrice($record['normalPrice'])
                ->setSpecialPrice($record['specialPrice']);

            $this->createdProductsCount++;

            $this->logger->info('New product is created. Sku: ' . $record['sku']);
        } else {
            $product->update(
                $record['sku'],
                $record['description'],
                $record['normalPrice'],
                $record['specialPrice']
            );

            $this->updatedProductsCount++;

            $this->logger->info('Product is updated. Sku: ' . $record['sku']);
        }

        return $product;
    }
}
