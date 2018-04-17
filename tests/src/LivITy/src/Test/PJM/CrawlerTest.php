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

namespace LivITy\Test\PJM;

use LivITy\Logger;
use LivITy\PJM\Config;
use LivITy\PJM\Crawler as PJMCrawler;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use PHPUnit\Framework\TestCase;

class CrawlerTest extends TestCase
{
    protected $crawler;
    protected $root;
    protected $savePath;

    public function setUp()
    {
        $this->root = realpath(dirname(__DIR__, 4));
        $this->savePath = $this->root . '/data/pjm/';

        $config = new Config($this->root . '/config/', 'pjm.ini');
        $logger = new Logger('PJM_Logger_Test', $this->root . '/log/pjm_test.log');
        $this->crawler = new PJMCrawler($config, $logger);
    }

    /**
     * @test
     * @group iso
     */
    public function testPJMCrawlerIsInitialized()
    {
        $this->assertEquals('LivITy\PJM\Crawler', get_class($this->crawler));
    }

    /** @test */
    public function testPJMCrawlerWeeklyFileIsRetrieved()
    {
        $data = $this->crawler->config->getReports($this->root . '/config/pjm.xlsx');
        $report = $this->getMetadata($data);

        $resp = $this->crawler->recurse($report['uri'], $report['reportFile']);
        $this->assertFileExists($report['reportFile']);
        $this->assertEquals('200', $resp->getStatusCode());
    }

    protected function getMetadata($data)
    {
        $report = $data[mt_rand(0, count($data) - 1)];

        if ($report['frequency'] == 'weekly') {
            $report['startDate'] = (new \DateTime('Thursday 1 week ago'))->format('m/d/Y');
            $report['endDate'] = (new \DateTime('Wednesday last week'))->format('m/d/Y');
            $report['savePath'] = $this->savePath . $report['frequency'] . '/' . (new \DateTime('last week Thursday'))->format('W-Y');
        } else if ($report['frequency'] == 'monthly') {
            $report['startDate'] = (new \DateTime("first day of last month"))->format('m/d/Y');
            $report['endDate'] = (new \DateTime("last day of last month"))->format('m/d/Y');
            $report['savePath'] = $this->savePath . $report['frequency'] . '/' . (new \DateTime('last month'))->format('m');
        }

        $report['uri'] = "{$report['url']}report={$report['apiName']}&version={$report['version']}&format={$report['format']}&start={$report['startDate']}&stop={$report['endDate']}&username={$report['user']}&password={$report['pass']}";
        $report['reportDate'] = str_replace('/', '', $report['startDate']) . '_' . str_replace('/', '', $report['endDate']);

        if (! file_exists($report['savePath']) && !is_dir($report['savePath'])) {
           mkdir($report['savePath'], 0777, true);
        }

        $report['reportFile'] = $report['savePath'] . '/TIDALE_' . $report['reportDate'] . '_' . $report['name'] . '_' . $report['version'] . '.csv';

        return $report;
    }
}
