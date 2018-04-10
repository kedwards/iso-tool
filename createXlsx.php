<?php
/*
 * This file is part of ieso-tool - the ieso query and download tool.
 *
 * (c) LivITy Consultinbg Ltd, Enbridge Inc., Kevin Edwards
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

if (! file_exists($composer = dirname(__FILE__) . '/vendor/autoload.php')) {
    throw new RuntimeException("Please run 'composer install' first to set up autoloading. $composer");
}

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = include $composer;

$root = __DIR__;
$config = new Config($root . '\src\config');
$logger = new Logger('ieso_xlsx', $root . '\src\log\xlsx.log');
$crawler = new Crawler($root . '\src\config');

$logger->getLogger('ieso_xlsx')->info(' ===== Starting createXlsx on ' .  date(DATE_RFC2822) . ' =====');
$paths = ['DT-P-F', 'DT-P-P', 'ST-F-F', 'ST-F-P', 'ST-P-F', 'ST-P-P'];

foreach ($paths as $path) {
    foreach (glob(\Env::get('IESO_ENBRIDGE_PATH') . $path . '/*.txt') as $src) {
        $info = pathinfo($src);
        $dest = $info['dirname'] . "/" . $info['filename'] . '.xlsx';
        if (!file_exists($dest)) {
            $writeXlsx = function() use ($src, $dest, $logger) {
                $reader = ReaderFactory::create(Type::CSV);
                $reader->setFieldDelimiter('|');
                $reader->open($src);
                $sheet = $reader->getSheetIterator()->current();

                $writer = WriterFactory::create(Type::XLSX);
                $writer->openToFile($dest);

                foreach ($sheet->getRowIterator() as $row) {
                    $writer->addRow($row);
                }
                $logger->getLogger('ieso_xlsx')->info('Writing XLSX file ' . $dest);
                $reader->close();
                $writer->close();
            };
            $writeXlsx();
            touch($dest, filemtime($src), filemtime($src));
        }
    }
}
$logger->getLogger('ieso_xlsx')->info(' ===== Completed createXlsx on ' .  date(DATE_RFC2822) . ' =====');
exit(0);
