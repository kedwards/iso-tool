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
namespace LivITy\Test;

use LivITy\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    protected $logger;
    protected $logFiles;

    public function setUp()
    {
        $logPath = realpath(dirname(__DIR__, 3) . '\log');
        $this->logFiles = [
            'TEST1' => ['path' => $logPath . '\test1.log'],
            'TEST2' => ['path' => $logPath . '\test2.log'],
        ];

        foreach ($this->logFiles as $k => $v) {
            $this->logger[$k] = new Logger($k, $v['path']);
        }
    }

    /** @test */
    public function testLoggerIsInitialized()
    {
        foreach($this->logger as $log) {
            $this->assertEquals('LivITy\Logger', get_class($log));
        }
    }

    /** @test */
    public function testLogFileIsCreated()
    {
        $text = 'Test event';
        foreach($this->logger as $name => $log) {
            $logger = $log->getLogger($name);
            $logger->info($text);
            $this->assertFileExists($log->getUrl());
        }
    }

    /** @test */
    public function testLocalFileContent()
    {
        $text = 'Second Test event';
        foreach($this->logger as $name => $log) {
            $logger = $log->getLogger($name);
            $logger->info($text);
            $this->assertContains($text, file_get_contents($log->getUrl()));
        }
    }


    /** @test */
    public function testFileSize()
    {
        foreach($this->logger as $name => $log) {
            if (file_exists($logFile = $log->getUrl())) {
                $filesize = filesize($logFile);
                if ($filesize > $log->getFileSize('1 MB')) {
                    unlink($logFile);
                    $filesize = 0;
                }
                $this->assertLessThan(1048576, $filesize);
            }
        }
   }
}
