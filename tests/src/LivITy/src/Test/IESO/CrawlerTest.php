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

namespace LivITy\Test\IESO;

use LivITy\Logger;
use LivITy\IESO\Crawler;
use PHPUnit\Framework\TestCase;

class CrawlerTest extends TestCase
{
    static $prefix = 'IESO_';
    static $testFolder = 'DT-P-F';
    protected $crawler;

    public function setUp()
    {
        $root = realpath(dirname(__DIR__, 4));
        // $logFile = $root . '\data\\' . CrawlerTest::$prefix . 'test.log';
        // $logger = new Logger(CrawlerTest::$prefix . 'Logger_Test', $logFile);
        $this->crawler = new Crawler($root . '\config');
    }

    /**
     * @test
     * @group iso
     */
    public function testIESOCrawlerIsInitialized()
    {
        $this->assertEquals('LivITy\IESO\Crawler', get_class($this->crawler));
    }

    /** @test */
    public function testIESOCrawlerFileIsRetrieved()
    {
        $data = $this->crawler->recurse($this->crawler->config['IESO_ROOT_PATH'] . 'DT-P-F', '', true);
        $this->assertCount(1, $data);
        $file = current($data)[mt_rand(0, count(current($data)) - 1)];
        $this->assertEquals(true, $file['isRegularFile']);
    }

    // /** @test */
    // public function testIESOCrawlerDirIsRetrieved()
    // {
    //     $data = $this->crawler->getDirData($this->crawler->recurse($this->crawler->config['IESO_ROOT_PATH'], ''));
    //     $this->assertNotEmpty($data);
    //     $this->assertArrayHasKey('DT-P-F', $data);
    // }

    // protected function delete_files($target)
    // {
    //     $it = new \RecursiveDirectoryIterator($target, \RecursiveDirectoryIterator::SKIP_DOTS);
    //     $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
    //     foreach($files as $file) {
    //         if ($file->isDir()){
    //             rmdir($file->getRealPath());
    //         } else {
    //             unlink($file->getRealPath());
    //         }
    //     }
    // }
    //




    //
    // /** @test */
    // public function testCrawlerFileCountIsRetrieved()
    // {
    //     $fileCount = rand(1, 9);
    //     $data = $this->crawler->request(\Env::get('IESO_ROOT_PATH') . 'DT-P-F', 0, $fileCount);
    //     $this->assertCount($fileCount, $data);
    // }
    //
    // /** @test */
    // public function testCrawlerFileWrittenToStorage()
    // {
    //     $this->delete_files(realpath(\Env::get('IESO_ENBRIDGE_PATH')));
    //
    //     $fileCount = 1;
    //     $data = $this->crawler->request(\Env::get('IESO_ROOT_PATH') . 'DT-P-F', 0, $fileCount, true);
    //     foreach($data as $k => $v) {
    //         $fi = new \FilesystemIterator($v['storage'], \FilesystemIterator::SKIP_DOTS);
    //         $this->assertEquals($fileCount, iterator_count($fi));
    //     }
    // }
    //
    // /** @test */
    // public function testCrawlerMultipleFilesWrittenToStorage()
    // {
    //     $this->delete_files(realpath(\Env::get('IESO_ENBRIDGE_PATH')));
    //
    //     $fileCount = 4;
    //     $data = $this->crawler->request(\Env::get('IESO_ROOT_PATH') . 'DT-P-F', 0, $fileCount, true);
    //     foreach($data as $k => $v) {
    //         $fi = new \FilesystemIterator($v['storage'], \FilesystemIterator::SKIP_DOTS);
    //         $this->assertEquals($fileCount, iterator_count($fi));
    //     }
    // }
    //
    // /**
    //  * @test
    //  * @expectedException \Exception
    //  */
    // public function testCrawlerException()
    // {
    //     $this->crawler->request(\Env::get('IESO_ROOT_PATH') . 'FAKE-PATH');
    // }
    //
    // /** @test */
    // public function testFiletime()
    // {
    //     $fileCount = 5;
    //     $data = $this->crawler->request(\Env::get('IESO_ROOT_PATH') . 'DT-P-F', 0, $fileCount, true);
    //     foreach($data as $k => $v) {
    //         $fi = new \FilesystemIterator($v['storage'], \FilesystemIterator::SKIP_DOTS);
    //         $this->assertEquals($fileCount, iterator_count($fi));
    //     }
    // }
    //
    // /** @test */
    // public function testXlsxIsCreated()
    // {
    //     $this->delete_files(realpath(\Env::get('IESO_ENBRIDGE_PATH')));
    //     $fileCount = 5;
    //     $data = $this->crawler->request(\Env::get('IESO_ROOT_PATH') . 'DT-P-F', 0, $fileCount, true);
    //     foreach($data as $k => $v) {
    //         $fi = new \FilesystemIterator($v['storage'], \FilesystemIterator::SKIP_DOTS);
    //         $this->assertEquals($fileCount * 2, iterator_count($fi));
    //     }
    // }
}
