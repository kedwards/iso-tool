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

use GuzzleHttp\Client;
use LivITy\Logger;
use LivITy\Crawler as LivITyCrawler;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

Class Crawler extends LivITyCrawler
{
    static $XLS_FLAG = ['DT-P-F', 'DT-P-P', 'ST-F-F', 'ST-F-P', 'ST-P-F', 'ST-P-P'];
    static $LOG_NAME = 'ieso_logger';

    /** @var array|null available class props */
    protected $props = [];

    public function __construct(String $root, String $file = '.ieso.env')
    {
        $config = Config::init($root, $file);
        $this->props['config'] = $this->getConfig();
        $logFile = preg_replace('/#DT#/', date('Y-m-d'), $this->props['config']['IESO_LOG_PATH']);
        $this->props['log'] = (new Logger(Crawler::$LOG_NAME, $logFile))->getLogger(Crawler::$LOG_NAME);
        $this->props['xlsFlag'] = Crawler::$XLS_FLAG;

        $this->props['client'] = new Client([
            'base_uri' => 'https://reports.ieso.ca/api/v1.1/files/private/',
            'auth' => [$this->props['config']['IESO_AUTH_USER'], $this->props['config']['IESO_AUTH_PASSWORD']]
        ]);
        return $this;
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

    public function recurse(String $path, Array $opts, $returnResponse = false)
    {
        $response = $this->request($path, $opts);

        $ct = $response->getHeader('Content-Type')[0];
        // if ($ct === "application/json;charset=UTF-8") {
        if ($returnResponse === true) {
            return json_decode($response->getBody()->getContents(), true);
        }

        $resp = json_decode($response->getBody()->getContents(), true);

        foreach ($resp['files'] as $k => $v) {
            $storage = preg_replace('#^TIDAL/#', '\\' . $this->props['config']['IESO_ENBRIDGE_PATH'], $path);
            if (!file_exists($storage)) {
                mkdir($storage, 0777, true);
            }

            if ($v['isDirectory']) {
                $dir = $path . $v['fileName'] . '/';
                $this->props['log']->info("Scanning Directory $dir");
                $this->recurse($dir, []);
            } else {
                if (!preg_match('/^(.*)_v[0-9]{1,2}\.[a-z]*$/', $v['fileName'])) {
                    $v['folder'] = preg_replace('#^TIDAL/#', '', rtrim($path, "/"));
                    $v['filePath'] = $storage . '/' . $v['fileName'];

                    if (file_exists($v['filePath'])) {
                        $file_time_disk = filemtime($v['filePath']);
                        if (substr($v['lastModifiedTime'], 0, 10) != $file_time_disk) {
                            $this->props['log']->info('updating file ' . $v['fileName']);
                            $this->update($path, $v);
                        }
                    } else {
                        $this->props['log']->info('Retrieving file  ' . $v['fileName']);
                        $this->retrieve($path, $v);
                    }
                }
            }
        }
        return $resp;
    }

    protected function getConfig()
    {
        return array_filter(
            getenv(),
            function ($key) {
                if (strpos($key, 'IESO_') !== false) {
                   return TRUE;
                }
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function stats()
    {
        // $this->props['stats'][$paths[1]]['updated']['files'][] = [ 'name' => $v['fileName'] ];
        // @$this->props['stats'][$paths[1]]['updated']['count'] += 1;

        // $this->props['stats'][$paths[1]]['created']['files'][] = [ 'name' => $v['fileName'] ];
        // @$this->props['stats'][$paths[1]]['created']['count'] += 1;
    }

    protected function retrieve(String $path, Array $v)
    {
        $response = $this->request($path . '/' . $v['fileName'], ['sink' => $v['filePath']]);

        if (!$response->getStatusCode() === 200) {
            $this->props['log']->info('Error retrieving file ' . $v['filePath']);
        }
        touch($v['filePath'], substr($v['lastModifiedTime'], 0, 10), substr($v['lastModifiedTime'], 0, 10));

        if (in_array($v['folder'], Crawler::$XLS_FLAG) && pathinfo($v['filePath'], PATHINFO_EXTENSION) == 'txt') {
             $this->createXlsx($v);
        }
    }

    private function createXlsx(Array $v)
    {
        $xlsxFile = $this->replace_extension($v['filePath'], 'xlsx');
        $this->props['log']->info('Writing XLSX file ' . $xlsxFile);
        $this->write_xml($v['filePath'], $xlsxFile);
        touch($xlsxFile, substr($v['lastModifiedTime'], 0, 10), substr($v['lastModifiedTime'], 0, 10));
    }

    protected function update($path, $v)
    {
        $response = $this->request($path, ['sink' => $v['filePath']]);

        if (!$response->getStatusCode() === 200) {
            $this->props['log']->info('Error retrieving file ' . $v['filePath']);
        }

        touch($v['filePath'], substr($v['lastModifiedTime'], 0, 10), substr($v['lastModifiedTime'], 0, 10));

        if (in_array($v['folder'], Crawler::$XLS_FLAG) && pathinfo($v['filePath'], PATHINFO_EXTENSION) == '.txt') {
             $this->createXlsx($v);
        }
        $this->stats();
    }

    protected function replace_extension($filename, $new_extension)
    {
        $info = pathinfo($filename);
        return $info['dirname'] . "/" . $info['filename'] . '.' . $new_extension;
    }

    protected function write_xml($src, $dest)
    {
        $reader = ReaderFactory::create(Type::CSV);
        $reader->setFieldDelimiter('|');
        $reader->open($src);
        $sheet = $reader->getSheetIterator()->current();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($dest);

        foreach ($sheet->getRowIterator() as $row) {
            $writer->addRow($row);
        }
        $this->log->info('Writing XLSX file ' . $dest);
        $reader->close();
        $writer->close();
    }
}
