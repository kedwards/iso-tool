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
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

abstract class Config
{
    // protected $rowData;
    // protected $enabledReports;
    // protected $env;

    /**
	 * @param array $container
	 */
	public abstract static function init(String $root, String $file);
    // {
    //     $dotEnv = empty($file) ? new Dotenv($root) : new Dotenv($root, $file);
    //     $dotEnv->load();
    //
    //     if (!empty($xlsConfigFile) && file_exists($xlsConfigFile)) {
    //         $reader = ReaderFactory::create(Type::XLSX);
    //         $reader->open($file);
    //
    //         foreach($reader->getSheetIterator() as $sheet) {
    //             foreach ($sheet->getRowIterator() as $row) {
    //                 $this->rowData[$sheet->getName()][] = $row;
    //             }
    //        }
    //     }
    // }

    // public abstract function getEnabledReports();
    // {
        // foreach($this->rowData as $iso => $data) {
        //     foreach($data as $val) {
        //         if ($val[2] == 'true') {
        //             $this->enabledReports[$iso][$val[0]] = $val;
        //         }
        //     }
        // }
        // return $this->enabledReports;
    // }

    // public function getEnabledReportsByISO($isoName)
    // {
    //     $isoName = strtoupper($isoName);
    //     foreach($this->rowData as $iso => $data) {
    //         foreach($data as $val) {
    //             if ($val[2] == 'true') {
    //                 $this->enabledReports[$iso][$val[0]] = $val;
    //             }
    //         }
    //     }
    //
    //     if (array_key_exists($isoName, $this->enabledReports)) {
    //         return $this->enabledReports[$isoName];
    //     } else {
    //         return [];
    //     }
    // }
}
