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

namespace LivITy\Test\NYISO;

use LivITy\Logger;
use LivITy\NYISO\Crawler;
use PHPUnit\Framework\TestCase;

class CrawlerTest extends TestCase
{
    static $prefix = 'IESO_';
    static $testFolder = 'DT-P-F';
    protected $crawler;
    protected $root;
    protected $config;

    public function setUp()
    {
        $this->root = realpath(dirname(__DIR__, 5));
        $this->crawler = new Crawler($this->root);
        $this->config = $this->crawler->config;
    }

    /**
     * @test
     * @group iso
     */
    public function testnyisoCrawlerIsInitialized()
    {
        $this->assertEquals('LivITy\NYISO\Crawler', get_class($this->crawler));
    }

    /**
     * @test
     * @group iso
     */
    public function testAddDownloadListIsRetrieved()
    {
        $list = $this->crawler->getAddDownloadsList();
        $this->assertNotEmpty($list);
    }

    /**
     * @test
     * @group iso
     */
    public function testAddDownloadIsRetrieved()
    {
        $list = $this->crawler->getAddDownloadsList();
        $this->assertNotEmpty($list);

        if(!empty($list[0])) {
            for($i=0; $i< count($list); $i++) {
                list($fileId, $fileName) = explode(',', $list[$i]);

                if (strpos($fileName, 'Daily') !== false) {
                    $folder = '/Daily/';
                } else if (strpos($fileName, 'Hourly') !== false) {
                    $folder = '/Hourly/';
                }

                $fileSave = $this->root . '/src/data/nyiso' . $folder . $fileName . ".csv";

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
                    // 'verify' => 'C:\Users\edwardk3\PortableApps\LivITy\.babun\cygwin\usr\local\etc\php\php-7.2.1-nts-Win32-VC15-x64\cacert-2018-03-07.pem',
                    'cert'  => [$this->root . '\src\keys\mrm-oati-cert.pem', 'MRMiso2018'],
                    'sink' => $fileSave
                ];
            }
            $this->crawler->getAddDownload($query);
        }
    }
}
