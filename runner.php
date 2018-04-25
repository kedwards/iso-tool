<?php
/*
 * This file is part of ieso-tool - the ieso query and download tool.
 *
 * (c) LivITy Consultinbg Ltd, Enbridge Inc., Kevin Edwards
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
use Commando\Command;
use GuzzleHttp\Client;
use LivITy\Logger;
use LivITy\IESO\Crawler as IESOCrawler;
use LivITy\IESO\MergeXlsx;
use LivITy\PJM\Config;
use LivITy\PJM\Crawler as PJMCrawler;
use LivITy\PJM\CreateConfig;
use LivITy\NYISO\Crawler as NYISOCrawler;

$root = realpath(dirname(__FILE__));
if (! file_exists($composer = $root . '/vendor/autoload.php')) {
    throw new RuntimeException("Please run 'composer install' first to set up autoloading. $composer");
}
$autoloader = include $composer;

$cli = new Command();
$cli->option()
    ->aka('iso')
    ->require()
    ->describedAs('The ISO to run [ieso|miso|nyiso|pjm]');

$cli->option('e')
    ->aka('extras')
    ->describedAs('Additional flag for ISO');

switch ($cli['iso']) {
    case 'ieso':
        if(is_null($cli['e'])) {
            $crawler = new IESOCrawler($root . '\src\config');
            $crawler->log->info(' ===== Starting Recurse on ' .  date(DATE_RFC2822) . ' =====');
            $path = is_null($cli['e']) ? $crawler->config['IESO_ROOT_PATH'] : $crawler->config['IESO_ROOT_PATH'] . $cli['e'];
            $results = $crawler->recurse($path, '');
            $crawler->log->info(' ===== Completed Recurse on ' .  date(DATE_RFC2822) . ' =====');
            // Stats::create($results);
        } else {
            $merger = new MergeXlsx();
            $merger->merge();
        }
        break;
    case 'pjm':
        $config = new Config($root . '\src\config', 'pjm.ini');
        $logger = new Logger('pjm_logger', $config->getConfig()['save'] . 'pjm_' . date(DATE_RFC2822) . '_log');
        if(is_null($cli['e'])) {
            $crawler = new PJMCrawler($config, $logger);
            $reports = $crawler->config->getReports($root . '\src\config\pjmConfig.xlsx');
            $result = $crawler->run($reports);
        } else {
            $creator = new CreateConfig($root, $config, $logger);
            $creator->writeConfigFile();
        }
        break;
    case 'nyiso':
        $crawler = new NYISOCrawler($root);
        $list = $crawler->getAddDownloadsList();

        if(!empty($list[0])) {
            for($i=0; $i< count($list); $i++) {
                list($fileId, $fileName) = explode(',', $list[$i]);

                $fileSave = $root . '/' . $fileName . ".csv";

                $query = [
                    'query' => [
                        'RepoType' => 'I',
                        'ID' => $fileId,
                        'DocName' => $fileName,
                        'DocType' => 'csv',
                        'user' => 'louckda2',
                        'pass' => 'NYFiles2018',
                        'delete' => 1
                    ],
                    'verify' => $root . '\keys\cacert-2018-03-07.pem',
                    'cert'  => [$root . '\keys\mrm-oati-cert.pem', 'MRMiso2018'],
                    'sink' => $fileSave
                ];
            }
            $this->crawler->getAddDownload($query);
        }
        break;
    case 'miso':
        echo "MISO Crawler";
        $queryParams = [
            'verify' => $root . '\src\keys\miso-mrm.pem',
            'cert'  => [$root . '\src\keys\mrm-oati-cert.pem', 'MRMiso2018']
        ];
        $client = new Client(['cookies' => true]);
        $response = $client->request('GET', 'https://markets.midwestiso.org/MISO/', $queryParams);
        break;
}
exit(0);
