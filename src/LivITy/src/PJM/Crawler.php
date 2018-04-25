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

use LivITy\Logger;
use LivITy\PJM\Config;
use LivITy\Crawler as LivITyCrawler;
use GuzzleHttp\Client;

Class Crawler extends LivITyCrawler
{
    /** @var array|null available class props */
    protected $props = [];

    /**
     * Create class, seeds config
     *
     * @param $config configuration options
     * @param $logger logger instance
     * @return Crawler
     */
    public function __construct(Config $config, Logger $logger)
    {
        $this->props['log'] = $logger;
        $this->props['config'] = $config;
        $this->props['client'] = new Client(['base_uri' => 'https://msrs.pjm.com/msrs/browserless.do?']);
        return $this;
    }

    /*
     *
     */
    public function recurse(String $path, Array $options)
    {
        return $this->request($path, $options);
    }

    public function run(Array $reports)
    {
        $config = $this->props['config']->getConfig();

        foreach ($reports as $report) {
            $data = array_merge($config, $report);

            if (! $data['enabled']) { continue; }

            if ($data['frequency'] == 'WEEKLY') {
                $data['startDate'] = (new \DateTime('Thursday 1 week ago'))->format('m/d/Y');
                $data['endDate'] = (new \DateTime('Wednesday last week'))->format('m/d/Y');
                $data['savePath'] = $data['save'] . $data['frequency'] . '/' . (new \DateTime('now'))->format('F') . '/' . str_replace('/', '', $data['startDate']) . '_' . str_replace('/', '', $data['endDate']);
            } else if ($data['frequency'] == 'MONTHLY') {
                $data['startDate'] = (new \DateTime("first day of last month"))->format('m/d/Y');
                $data['endDate'] = (new \DateTime("last day of last month"))->format('m/d/Y');
                $data['savePath'] = $data['save'] . $data['frequency'] . '/' . (new \DateTime('last month'))->format('F');
            }

            $data['uri'] = "{$data['url']}report={$data['apiName']}&version={$data['version']}&format={$data['format']}&start={$data['startDate']}&stop={$data['endDate']}&username={$data['user']}&password={$data['pass']}";
            $data['reportDate'] = str_replace('/', '', $data['startDate']) . '_' . str_replace('/', '', $data['endDate']);

            if (! file_exists($data['savePath']) && !is_dir($data['savePath'])) {
               mkdir($data['savePath'], 0777, true);
            }

            $data['save'] = $data['savePath'] . '/TIDALE_' . $data['reportDate'] . '_' . $data['name'] . '_' . $data['version'] . '.csv';

            $opts = [
                'verify' => 'C:\Users\edwardk3\Dropbox\PortableApps\LivITy\.babun\cygwin\usr\local\etc\php\php-7.2.1-nts-Win32-VC15-x64\cacert-2018-03-07.pem',
                'sink' => $data['save']
            ];

            $result = $this->recurse($data['uri'], $opts);
        }
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

    protected function getMetadata($data)
    {
        $report = $data[mt_rand(0, count($data) - 1)];

        if ($report['frequency'] == 'weekly') {
            $report['startDate'] = (new \DateTime('Thursday 1 week ago'))->format('m/d/Y');
            $report['endDate'] = (new \DateTime('Wednesday last week'))->format('m/d/Y');
            // $report['savePath'] = $this->savePath . $report['frequency'] . '/' . (new \DateTime('last week Thursday'))->format('W-Y');
        } else if ($report['frequency'] == 'monthly') {
            $report['startDate'] = (new \DateTime("first day of last month"))->format('m/d/Y');
            $report['endDate'] = (new \DateTime("last day of last month"))->format('m/d/Y');
            // $report['savePath'] = $this->savePath . $report['frequency'] . '/' . (new \DateTime('last month'))->format('m');
        }

        $report['uri'] = "{$report['url']}report={$report['apiName']}&version={$report['version']}&format={$report['format']}&start={$report['startDate']}&stop={$report['endDate']}&username={$report['user']}&password={$report['pass']}";
        $report['reportDate'] = str_replace('/', '', $report['startDate']) . '_' . str_replace('/', '', $report['endDate']);

        if (! file_exists($report['savePath']) && !is_dir($report['savePath'])) {
           mkdir($report['savePath'], 0777, true);
        }

        $report['reportFile'] = $report['savePath'] . '/TIDALE_' . $report['reportDate'] . '_' . $report['name'] . '_' . $report['version'] . '.csv';

        return $report;
    }
}
