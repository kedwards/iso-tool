<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\TransferException;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

if (! file_exists($composer = dirname(__FILE__) . '/vendor/autoload.php')) {
    throw new RuntimeException("Please run 'composer install' first to set up autoloading. $composer");
}

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = include $composer;

$filePath = realpath(dirname(__FILE__)) . '/pjmConfig.xlsx';
$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile($filePath);

try {
    $client = new Client();
    $res = $client->request('GET', 'https://msrs.pjm.com/msrs/browserless.do?list=true&username=loucksd&password=Enbpower3!');
} catch(TransferException $e) {
    throw new \Exception($e->getMessage());
}
$resp = explode("\n", $res->getBody()->getContents());

$row = ["ID", "REPORT NAME", "API NAME", "FILENAME", "URI"];
$writer->addRow($row);

$n = 1;
foreach($resp as $report) {
    if ($report != "") {
        $apiName = preg_replace('/\s*/', '', strtolower($report));
        $uri = 'https://msrs.pjm.com/msrs/browserless.do?&report=' . $apiName . '^version=L&Fformat=C&stop=04/01/2018&stop=04/02/2018';
        try {
            $client = new Client();
            $res = $client->request('GET', 'https://msrs.pjm.com/msrs/browserless.do?start=03/01/2018&stop=03/02/2018&format=C&version=L&username=loucksd&password=Enbpower3!&report=' . $apiName);

            if ($res->hasHeader('Content-Disposition')) {
                $fileName = substr(trim(substr($res->getHeader('Content-Disposition')[0], 43), '"'), 0, -6);
             } else {
                $fileName = "Error on file: " . $report . " - " . $apiName;
            }

            $row = [$n, $report, $apiName, $fileName, $uri];
            $writer->addRow($row);
        } catch(TransferException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    $n += 1;
}
$writer->close();
