<?php
/*
 * This file is part of ieso-tool - the ieso query and download tool.
 *
 * (c) LivITy Consultinbg Ltd, Enbridge Inc., Kevin Edwards
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace LivITy\PJM;

use Livity\Logger;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\TransferException;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

Class CreateConfig
{
    /** @var array|null available class props */
    protected $props = [];

    public function __construct($root, Config $config, Logger $logger)
    {
        $this->props['config'] = $config;
        $this->props['log'] = $logger;
        $this->props['root'] = $root;

        $this->props['client'] = new Client(['base_uri' => $this->props['config']->getConfig()['url']]);
        $this->props['writer'] = WriterFactory::create(Type::XLSX);

        return $this;
    }

    public function getReportList()
    {
        $res = $this->props['client']->request('GET', '', [
            'query' => [
                'list' => 'true',
                'username' => 'loucksd',
                'password' => 'Enbpower3!'
            ]
        ]);
        $this->props['report_list'] = explode("\n", $res->getBody()->getContents());
        return $this->props['report_list'];
    }

    public function writeConfigFile($rowCount = 0)
    {
        if(empty($this->props['report_list'])) {
            $reports = $this->getReportList();
        } else {
            $reports = $this->props['report_list'];
        }

        $row = ["ID", "REPORT NAME", "API NAME", "FILENAME", "URI"];

        $config = $this->props['config']->getConfig();

        $this->props['writer']->openToFile($this->root . $config['configFilePath']);
        $this->props['writer']->addRow($row);

        $n = 1;
        foreach($reports as $report) {
            if ($report != "") {
                $apiName = preg_replace('/\s*/', '', strtolower($report));
                $uri = $config['url'] . '?&report=' . $apiName . '^version=L&Fformat=C&stop=04/01/2018&stop=04/02/2018';
                try {
                    $client = new Client();
                    $res = $this->props['client']->request('GET', '', [
                        'query' => [
                            'start' => '03/01/2018',
                            'stop' => '03/02/2018',
                            'format' => $config['format'],
                            'version' => $config['version'],
                            'username' => $config['user'],
                            'password' => $config['pass'],
                            'report' => $apiName
                        ]
                    ]);

                    if ($res->hasHeader('Content-Disposition')) {
                        $fileName = substr(trim(substr($res->getHeader('Content-Disposition')[0], 43), '"'), 0, -6);
                     } else {
                        $fileName = "Error on file: " . $report . " - " . $apiName;
                    }

                    $row = [$n, $report, $apiName, $fileName, $uri];
                    $this->props['writer']->addRow($row);
                } catch(TransferException $e) {
                    throw new \Exception($e->getMessage());
                }
            }
            $n += 1;

            if($rowCount != 0 && $n >= $rowCount) {
                break;
            }
        }
        $this->props['writer']->close();
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
}
