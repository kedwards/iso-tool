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

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Client;
use LivITy\Logger;
use LivITy\Crawler as LivITyCrawler;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

Class Crawler extends LivITyCrawler
{
    static $LOG_NAME = 'nyiso_logger';

    /** @var array|null available class props */
    protected $props = [];
    protected $queryParams;

    public function __construct(String $root, String $file = '.nyiso.env')
    {
        $config = Config::init($root . '/src/config', $file);
        $this->props['config'] = $this->getConfig();

        $this->queryParams = [
            'query' => [
                'user' => $this->props['config']['NYISO_AUTH_USER'],
                'pass' => $this->props['config']['NYISO_AUTH_PASSWORD'],
                'automated' => 2
            ],
            // 'verify' => 'C:\Users\edwardk3\PortableApps\LivITy\.babun\cygwin\usr\local\etc\php\php-7.2.1-nts-Win32-VC15-x64\cacert-2018-03-07.pem',
            'cert'  => [$root . '\src\keys\mrm-oati-cert.pem', 'MRMiso2018'],
        ];

        $this->props['client'] = new Client(['cookies' => true]);
        $logFile = preg_replace('/#DT#/', date('Y-m-d'), $this->props['config']['NYISO_LOG_PATH']);
        $this->props['log'] = (new Logger(Crawler::$LOG_NAME, $logFile))->getLogger(Crawler::$LOG_NAME);
    }

    public function recurse(String $path, String $sink)
    {
    }

    public function getAddDownloadsList()
    {
        // $response = exec('curl -E src/keys/mrm-oati-cert.pem "https://dss.nyiso.com/dss/login.jsp?user=louckda2&pass=NYFiles2018&automated=2" --pass MRMiso2018');
        $response = $this->props['client']->request('GET', $this->props['config']['NYISO_BASE_URI'], $this->queryParams);
        return explode("\n", trim($response->getBody()->getContents(), "\n"));
    }

    public function getAddDownload(Array $queryParams)
    {
        // $response = exec('curl -E src/keys/mrm-oati-cert.pem https://dss.nyiso.com/dss/docViewAGN.jsp\?RepoType\=I\&ID\=20087365\&fileName\=1071282_2018-04-13_V00_Daily\&type\=csv\&user\=louckda2\&pass\=NYFiles2018 --pass MRMiso2018 -b cookie.txt');
        $response = $this->props['client']->request('POST', $this->props['config']['NYISO_DL_URI'], $queryParams);
    }

    /**
     * Magic set method
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $method = 'set'. ucfirst($key);
        if (is_callable([$this, $method])) {
            $this->$method($value);
        } else {
            $this->props[$key] = $value;
        }
    }

    /**
     * Magic get method
     *
     * @param $key
     * @return  $this->props[$key]
     */
    public function __get($key)
    {
        $method = 'get' .  ucfirst($key);
        if (is_callable([$this, $method])) {
            return $this->$method();
        } else if (array_key_exists($key, $this->props)) {
            return $this->props[$key];
        }
    }

    protected function getConfig()
    {
        return array_filter(
            getenv(),
            function ($key) {
                if (strpos($key, 'NYISO_') !== false) {
                   return TRUE;
                }
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
