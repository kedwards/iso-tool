<?php
/*
 * This file is part of ieso-tool - the ieso query and download tool.
 *
 * (c) LivITy Consultinbg Ltd, Enbridge Inc., Kevin Edwards
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace LivITy\IESO;

use LivITy\Logger;
use LivITy\IESO\Config;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\Style\Color;

Class MergeXlsx
{
    protected $logger;

    public function __construct()
    {
        $root = dirname(__DIR__, 4) . '\\src\\';
        $config = new Config($root);
        $this->logger = new Logger('IESO_Merge', $root . '/log/merge.log');
        $crawler = new Crawler($root . '/config');
    }

    public function merge()
    {
        $this->logger->getLogger('IESO_Merge')->info(' ===== Starting mergeXlsx on ' .  date(DATE_RFC2822) . ' =====');
        $paths = ['DT-P-F', 'DT-P-P', 'ST-F-F', 'ST-F-P', 'ST-P-F', 'ST-P-P'];

        $style = (new StyleBuilder())
           ->setFontBold()
           ->setFontSize(15)
           ->setFontColor(Color::BLUE)
           ->build();

        $month = (new \DateTime)->format("m");
        $year = (new \DateTime)->format("Y");
        $monthName = (new \DateTime)->createFromFormat('!m', $month)->format('F');

        foreach ($paths as $path) {
            $newFilePath = realpath(\Env::get('IESO_ENBRIDGE_PATH')) . "/{$path}/CNF-TIDAL_{$path}_{$monthName}_{$year}.xlsx";
            $writer = WriterFactory::create(Type::XLSX);
            $writer->openToFile($newFilePath);

            $glob = glob(realpath(\Env::get('IESO_ENBRIDGE_PATH')) . '/' . $path . '/*.xlsx');
            foreach($glob as $file) {
                if (preg_match("/[^_]*_{$year}{$month}.*/", $file)) {
                    $reader = ReaderFactory::create(Type::XLSX);
                    $reader->open($file);
                    $reader->setShouldFormatDates(true); // this is to be able to copy dates

                    foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
                        if ($sheetIndex !== 1) {
                            $writer->addNewSheetAndMakeItCurrent();
                        }

                        foreach ($sheet->getRowIterator() as $row) {
                            $writer->addRow($row);
                        }
                    }
                    $reader->close();
                    $writer->addRowWithStyle([$file], $style);
                }
            }
            $writer->close();
        }
        $this->logger->getLogger('IESO_Merge')->info(' ===== Completed mergeXlsx on ' .  date(DATE_RFC2822) . ' =====');
    }
}
