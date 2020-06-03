<?php

namespace App\Service;

use League\Csv\AbstractCsv;
use League\Csv\Reader;

class FileReaderFactory
{
    /**
     * @param string $fileName
     * @param int $offset
     * @return AbstractCsv
     * @throws \Exception
     */
    public static function createFileReader(string $fileName, int $offset = 0): AbstractCsv
    {
        try {
            $reader = Reader::createFromPath($fileName);

            $reader->setHeaderOffset($offset)->getHeader();
        } catch (\Exception $e) {
            throw new \Exception('File is not found or corrupted');
        }

        return $reader;
    }
}
