<?php
/*
 * This file is part of ieso-tool - the ieso query and download tool.
 *
 * (c) LivITy Consultinbg Ltd, Enbridge Inc., Kevin Edwards
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
declare(strict_types=1);

use LivITy\Logger,
    LivITy\IESO\Config;
use PHPUnit\Framework\TestCase;
use Box\Spout\Reader\ReaderFactory,
    Box\Spout\Writer\WriterFactory,
    Box\Spout\Common\Type;

class MergeTest extends TestCase
{
    private $logger;
    private $file;

    public function setUp()
    {
        $root = realpath(dirname(__DIR__, 4) . '\tests');

        $config = new Config($root . '\tests');
        $this->logger = new Logger('ieso_test', $root . '\log');
        $this->file = realpath(\Env::get('IESO_ENBRIDGE_PATH')) . '/'. 'ieso_test.log';
    }

    /** @test */
    public function testXlsxIsMerged()
    {
        $monthNum = '10';
        $dateObj = DateTime::createFromFormat('!m', $monthNum);
        $monthName = $dateObj->format('F');

        $newFilePath = realpath(dirname(__DIR__, 4)) . '/data/DT-P-F/CNF-TIDAL_DT-P-F_' . $monthName . '.xlsx';
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($newFilePath);

        $glob = glob(realpath(\Env::get('IESO_ENBRIDGE_PATH')) . '/DT-P-F/*.xlsx');
        foreach($glob as $file) {
            if (preg_match('/CNF-TIDAL_DT-P-F_201710.*/', $file)) {
                $reader = ReaderFactory::create(Type::XLSX);
                $reader->open($file);
                $reader->setShouldFormatDates(true); // this is to be able to copy dates

                foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
                    // Add sheets in the new file, as we read new sheets in the existing one
                    if ($sheetIndex !== 1) {
                        $writer->addNewSheetAndMakeItCurrent();
                    }

                    foreach ($sheet->getRowIterator() as $row) {
                        $writer->addRow($row);
                    }
                }
                $reader->close();
                $writer->addRow([$file]);
            }
        }

        $writer->close();
    }
}
