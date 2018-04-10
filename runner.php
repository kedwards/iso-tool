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
use LivITy\PJM\Config;
use LivITy\PJM\Crawler as PJMCrawler;

$root = dirname(__FILE__);
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
        $crawler = new IESOCrawler($root . '\src\config');
        $crawler->log->info(' ===== Starting Recurse on ' .  date(DATE_RFC2822) . ' =====');
        $path = is_null($cli['e']) ? $crawler->config['IESO_ROOT_PATH'] : $crawler->config['IESO_ROOT_PATH'] . $cli['e'];
        $results = $crawler->recurse($path, '');
        $crawler->log->info(' ===== Completed Recurse on ' .  date(DATE_RFC2822) . ' =====');
        // Stats::create($results);
        break;
    case 'pjm':
        $config = new Config($root . '\src\config', 'pjm.ini');
        $logger = new Logger('pjm_logger', $config->getConfig()['save'] . 'pjm_' . date(DATE_RFC2822) . '_log');
        $crawler = new PJMCrawler($config, $logger);
        $reports = $crawler->config->getReports($root . '\src\config\pjm.xlsx');
        $result = $crawler->run($reports);
        break;
    case 'miso':
        echo "MISO Crawler";
        $client = new Client([
            'base_uri' => 'https://markets.midwestiso.org/MISO/getSettlementStatementFile?',
        ]);
        $response = $client->request('GET', 'entity=TDL_MP&nodeId=key0', [
            'verify' => false,
            'cert'  => [$root . '\src\keys\mrm-oati-cert.pem', 'MRMiso2018'],
            'timeout' => 10
        ]);
        break;
    case 'nyiso':
        echo "NYISO Crawler";
        $client = new Client([
            'base_uri' => 'https://dss.nyiso.com/dss/login.jsp',
        ]);
        $response = $client->request('GET', 'https://dss.nyiso.com/dss/login.jsp', [
            'query' => [
                'user' => 'louckda2',
                'pass' => 'NYFiles2018',
                'automated' => 2
            ],
            'verify' => false,
            'ssl_key' => $root . '\src\keys\cacert',
        ]);

        $response = $client->request('GET', 'https://dss.nyiso.com/dss/docViewAGN.jsp', [
            'query' => [
                'RepoType' => 'I',
                'ID' => '',
                'DocName' => '',
                'DocType' => 'csv',
                'user' => 'louckda2',
                'pass' => 'NYFiles2018'
            ],
            'verify' => false,
            'ssl_key' => $root . '\src\keys\cacert.pem'
        ]);
        break;
}
exit(0);
