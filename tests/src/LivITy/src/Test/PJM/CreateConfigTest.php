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
use LivITy\PJM\CreateConfig;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use PHPUnit\Framework\TestCase;

class CrawlerTest extends TestCase
{
    protected $creator;
    protected $root;
    protected $savePath;

    public function setUp()
    {
        $this->root = realpath(dirname(__DIR__, 4));
        $this->config = new Config($this->root . '/config/', 'pjm.ini');
        $logger = new Logger('PJM_CreateConfig_Test', $this->root . '/log/pjm_create_config.log');

        $this->creator = new CreateConfig($this->root, $this->config, $logger);
    }

    /**
     * @test
     * @group iso
     */
    public function testPJMCrawlerIsInitialized()
    {
        $this->assertEquals('LivITy\PJM\CreateConfig', get_class($this->creator));
    }

    /** @test */
    public function testConfigFileIsCreated()
    {
        $exists = file_exists($this->root . '/config/pjmConfig.xlsx');
        $this->assertTrue($exists);
    }

    public function testRetrieveReportList()
    {
        $reportList = $this->creator->getReportList();
        $this->assertNotEmpty($reportList);
    }

    public function testWriteConfigFile()
    {
        $row_count = 15;
        $header = 1;
        $xlsxConfigFile = $this->root . '/config/pjmConfig.xlsx';

        $this->creator->writeConfigFile($row_count);
        $exists = file_exists($xlsxConfigFile);
        $this->assertTrue($exists);

        $this->assertEquals($row_count - $header, $this->getNumRows($xlsxConfigFile));
    }

    private function getNumRows($xlsxPath)
    {
        $numRows = 0;

        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($xlsxPath);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $numRows++;
            }
        }
        $reader->close();

        return $numRows;
    }
}
