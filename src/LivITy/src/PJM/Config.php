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
use LivITy\Config as LivITyConfig;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

class Config extends LivITyConfig
{
    protected $reports;
    protected $config;

    /**
	 * @param array $container
	 */
	public function __construct(String $root, $file = '')
    {
		$ini_array = parse_ini_file($root . '/' . $file, true);
		$config = array_intersect_key($ini_array, array_flip(['Common']));
		// $reports =  array_diff_key($ini_array, array_flip(['Common']));
		$this->config = $config['Common'];

        return $this;
    }

    public function getReports($filePath)
	{
        // $filePath = $root . 'pjm.xlsx';
		$reader = ReaderFactory::create(Type::XLSX);
		$reader->open($filePath);
		foreach ($reader->getSheetIterator() as $sheet) {
			foreach ($sheet->getRowIterator() as $row) {
				if ($row[2] == 'true') {
					$this->reports[] = [$row[3], ['name' => $row[5], 'id' => $row[0], 'frequency' => $row[1], 'enabled' => $row[2], 'reportName' => $row[3], 'apiName' => $row[4], 'fileName' => $row[5], 'url' =>$row[6]]];
				}
			}
		}

        foreach ($this->reports as $report => $meta) {
            $data[] = array_merge($this->config, $meta[1]);
        }
        return $data;
	}

    public function getConfig()
    {
        return $this->config;
    }

    public static function init(String $root, String $file) {}
}
