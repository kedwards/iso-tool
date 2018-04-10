<?php
/*
 * This file is part of ieso-tool - the ieso query and download tool.
 *
 * (c) LivITy Consultinbg Ltd, Enbridge Inc., Kevin Edwards
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace LivITy\IESO;
use LivITy\Config as LivITyConfig;
use Dotenv\Dotenv;

class Config extends LivITyConfig
{
    /**
	 * @param array $container
	 */
	public static function init(String $root, String $file = '')
    {
        $dotEnv = empty($file) ? new Dotenv($root) : new Dotenv($root, $file);
		$dotEnv->load();
        $dotEnv->required([
            'IESO_AUTH_USER', 'IESO_AUTH_PASSWORD', 'IESO_APP_ENV', 'IESO_ROOT_PATH',
            'IESO_BASE_URI', 'IESO_ENBRIDGE_PATH'
        ]);
    }
}
