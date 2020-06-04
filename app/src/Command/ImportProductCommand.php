<?php

namespace App\Command;

use App\Service\FileReaderFactory;
use App\Service\ProductService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProductCommand extends Command
{
    const DATA_INPUT_PATH = '%kernel.root_dir%/../data/input/';

    private $productService;

    /**
     * ImportProductCommand constructor.
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;

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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csvFilename = $input->getArgument('csvFilename');

        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting to import product');

        try {
            $csv = FileReaderFactory::createFileReader(self::DATA_INPUT_PATH . $csvFilename);
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return 1;
        }

        $this->productService->importCsv($csv);

        if ($this->productService->countProductsWithErrors() > 0) {
            $productsCount = $this->productService->countProductsWithErrors();
            $io->caution(
                sprintf($productsCount . ' invalid %s skipped.', ($productsCount === 1 ? 'product is' : 'products are'))
            );
        }

        $io->note($this->productService->countCreatedProducts() . ' New products created.');
        $io->note($this->productService->countUpdatedProducts() . ' Products updated.');

        $io->success($this->productService->countSuccessfulProducts() . ' records are imported successfully.');

        // TODO: remove processed file from data/input folder

        return 0;
    }
}
