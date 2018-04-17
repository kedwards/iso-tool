<?php
/*
 * This file is part of ieso-tool - the ieso query and download tool.
 *
 * (c) LivITy Consulting Ltd, Enbridge Inc., Kevin Edwards
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
use LivITy\IESO\Crawler;
use LivITy\Logger;
use LivITy\IESO\Config;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\Style\Color;

if (! file_exists($composer = dirname(__FILE__) . '/vendor/autoload.php')) {
    throw new RuntimeException("Please run 'composer install' first to set up autoloading. $composer");
}

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = include $composer;

$root = __DIR__ . '\\src\\';
$config = new Config($root);
$logger = new Logger('IESO_Merge', $root . '/log/merge.log');
$crawler = new Crawler($root . '/config');

$style = (new StyleBuilder())
   ->setFontBold()
   ->setFontSize(15)
   ->setFontColor(Color::BLUE)
   // ->setShouldWrapText()
   // ->setBackgroundColor(Color::YELLOW)
   ->build();

$logger->getLogger('IESO_Merge')->info(' ===== Starting mergeXlsx on ' .  date(DATE_RFC2822) . ' =====');
$paths = ['DT-P-F', 'DT-P-P', 'ST-F-F', 'ST-F-P', 'ST-P-F', 'ST-P-P'];

$month = (new DateTime)->format("m");
$month = '03';
$year = (new DateTime)->format("Y");
$monthName = (new DateTime)->createFromFormat('!m', $month)->format('F');

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
$logger->getLogger('IESO_Merge')->info(' ===== Completed mergeXlsx on ' .  date(DATE_RFC2822) . ' =====');

exit(0);
