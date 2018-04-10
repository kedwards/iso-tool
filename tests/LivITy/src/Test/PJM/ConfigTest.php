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
use LivITy\PJM\Config as PJMConfig;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected $config;

    public function setUp()
    {
        $root = realpath(dirname(__DIR__, 4));
        $this->config = new PJMConfig($root . '/config/', 'pjm.ini');
    }

    /**
     * @test
     * @group config
     */
    public function testIESOConfigIsInitialized()
    {
        $this->assertEquals(true, class_exists('LivITy\PJM\Config'));
        $this->assertEquals('LivITy\PJM\Config', get_class($this->config));
    }
}
