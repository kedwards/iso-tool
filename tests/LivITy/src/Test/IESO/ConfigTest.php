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
use LivITy\IESO\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected $config;

    public function setUp()
    {
        $this->configPath = realpath(dirname(__DIR__, 4) . '\config');
    }

    /**
     * @test
     * @group config
     */
    public function testIESOConfigIsInitialized()
    {
        Config::init($this->configPath, '.ieso.env');
        $config = array_filter(
            getenv(),
            function ($key) {
                if (strpos($key, 'IESO_') !== false) {
                   return TRUE;
                }
            },
            ARRAY_FILTER_USE_KEY
        );

        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('IESO_AUTH_USER', $config);
    }
}
