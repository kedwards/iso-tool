<?php
/*
 * This file is part of ieso-tool - the ieso query and download tool.
 *
 * (c) LivITy Consultinbg Ltd, Enbridge Inc., Kevin Edwards
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace LivITy;

use Dotenv\Dotenv;
use Monolog\Logger as Monologger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

Class Logger
{
    protected $log = [];
    protected $stream;

    public function __construct(String $name, String $filePath)
    {
        // if (file_exists($filePath)) {
        // 	$filesize = filesize($filePath);
        // 	if ($filesize > $this->stringSizeToBytes('1 MB')) {
        //         $this->log()->info('Deleting log file that is over 1MB.');
        //         unlink($filePath);
        // 	}
        // }

        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        // the default date format is "Y-m-d H:i:s", popular date format is "Y n j, g:i a";
        $output = "[%datetime%] %level_name% : %message%\n";
        $logFormat = 'Y-m-d H:i:s';
        $logHeader = 'ISO';
        $this->dateFormat = 'Y-m-d';

        $formatter = new LineFormatter($output . "\n", $logFormat);
        //$stream = new StreamHandler(realpath(\Env::get('IESO_ENBRIDGE_PATH')) . '/' . $name . '_' . date($this->dateFormat) . '.log', Monologger::INFO);
        $stream = new StreamHandler($filePath, Monologger::INFO);
        $stream->setFormatter($formatter);
        $l = new Monologger($logHeader);
        $l->pushHandler($stream);

        $this->stream = $stream;
        $this->log[$name] = $l;
    }

    public function getLogger(String $name)
    {
        if ($this->in_assoc($name, $this->log)) {
            return $this->log[$name];
        } else {
            return null; // error, no log
        }
    }

    private function in_assoc($needle, $array)
    {
        $key = array_keys($array);
        $value = array_values($array);
        if (in_array($needle,$key)) {
            return true;
        } elseif (in_array($needle,$value)) {
            return true;
        } else {
            return false;
        }
    }

    public function getUrl()
    {
        return $this->stream->getUrl();
    }

    public function getFileSize($fileSize)
    {
        return $this->stringSizeToBytes($fileSize);
    }

    protected function stringSizeToBytes($size)
    {
		$unit = strtolower($size);
		$unit = preg_replace('/[^a-z]/', '', $unit);

		$value = intval(preg_replace('/[^0-9]/', '', $size));

		$units = array('b'=>0, 'kb'=>1, 'mb'=>2, 'gb'=>3, 'tb'=>4);
		$exponent = isset($units[$unit]) ? $units[$unit] : 0;

		return ($value * pow(1024, $exponent));
	}
}
