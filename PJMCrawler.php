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

$ini_array = parse_ini_file(realpath(dirname(__FILE__) . '/src/config/pjm.ini'), true);
$common = array_intersect_key($ini_array, array_flip(['Common']));
// $reports =  array_diff_key($ini_array, array_flip(['Common']));
$common = $common['Common'];

$filePath = realpath(dirname(__FILE__) . '/tests/config/pjm.xlsx');
$reader = ReaderFactory::create(Type::XLSX);
$reader->open($filePath);
foreach ($reader->getSheetIterator() as $sheet) {
    foreach ($sheet->getRowIterator() as $row) {
        if ($row[2] == 'TRUE') {
            $reports[] = [$row[3], ['name' => $row[5], 'id' => $row[0], 'frequency' => $row[1], 'enabled' => $row[2], 'reportName' => $row[3], 'apiName' => $row[4], 'fileName' => $row[5], 'url' =>$row[6]]];
        }
    }
}
$reader->close();

$n = 1;
$logme = 0;

if ($logme === 1) {
    $writer = WriterFactory::create(Type::XLSX);
    $writer->openToFile($filePath);
}

foreach ($reports as $report => $meta) {
    $data = array_merge($common, $meta[1]);

    if (! $data['enabled']) { continue; }

    if ($data['frequency'] == 'weekly') {
        $data['startDate'] = (new DateTime('Thursday 1 week ago'))->format('m/d/Y');
        $data['endDate'] = (new DateTime('Wednesday last week'))->format('m/d/Y');
        $data['savePath'] = dirname(__FILE__) . '/tests/data/pjm/' . $data['frequency'] . '/' . (new DateTime('last week Thursday'))->format('W-Y');
    } else if ($data['frequency'] == 'monthly') {
        $data['startDate'] = (new DateTime("first day of last month"))->format('m/d/Y');
        $data['endDate'] = (new DateTime("last day of last month"))->format('m/d/Y');
        $data['savePath'] = dirname(__FILE__) . '/tests/data/pjm/' . $data['frequency'] . '/' . (new DateTime('last month'))->format('m');
    }

    $reportName = strtolower(preg_replace('/\s*/', '', $report));
    $data['uri'] = "{$data['url']}report={$data['apiName']}&version={$data['version']}&format={$data['format']}&start={$data['startDate']}&stop={$data['endDate']}&username={$data['user']}&password={$data['pass']}";
    $data['reportDate'] = str_replace('/', '', $data['startDate']) . '_' . str_replace('/', '', $data['endDate']);

    if (! file_exists($data['savePath']) && !is_dir($data['savePath'])) {
       mkdir($data['savePath'], 0777, true);
    }

    $data['save'] = $data['savePath'] . '/TIDALE_' . $data['reportDate'] . '_' . $data['name'] . '_' . $data['version'] . '.csv';

    try {
        $client = new Client();
        $res = $client->request('GET', $data['uri'], [
            'sink' => $data['save']
        ]);
    } catch(TransferException $e) {
        throw new \Exception($e->getMessage());
    }

    if ($logme === 1) {
        if ($res->hasHeader('Content-Disposition')) {
            $fileName = substr(trim(substr($res->getHeader('Content-Disposition')[0], 43), '"'), 0, -6);
         } else {
            $fileName = "ERROR_REPORT_$n";
        }
        echo "[$report]\nid = $n\nname = $fileName\n\n";
        $row = [$n, $report, $reportName, $fileName, $uri];
        $writer->addRow($row);
    }
    echo $n . ' - ' . $data['reportName'] . "\n";
    $n = $n + 1;
}
if ($logme === 1) {
    $writer->close();
}
