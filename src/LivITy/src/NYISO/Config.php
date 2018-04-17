<?php
/*
 * This file is part of ieso-tool - the ieso query and download tool.
 *
 * (c) LivITy Consultinbg Ltd, Enbridge Inc., Kevin Edwards
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace LivITy\NYISO;
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
        // $dotEnv->required([
        //     'NYISO_AUTH_USER', 'NYISO_AUTH_PASSWORD', 'NYISO_APP_ENV',
        //     'NYISO_BASE_URI', 'NYISO_DL_URI, NYISO_ENBRIDGE_PATH'
        // ]);
    }
}
