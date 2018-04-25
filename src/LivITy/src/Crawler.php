<?php
/*
 * This file is part of iso-tool - the iso query and download tool.
 *
 * (c) LivITy Consultinbg Ltd, Enbridge Inc., Kevin Edwards
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace LivITy;
use GuzzleHttp\Exception\TransferException;

abstract class Crawler
{
    public abstract function recurse(String $path, Array $opts);

    /**
     *
     */
    public function request(String $path, Array $opts)
    {
        try {
            if (empty($opts)) {
                $res = $this->props['client']->request('GET', $path);
            } else {
                $res = $this->props['client']->request('GET', $path, $opts);
            }
        } catch(TransferException $e) {
            throw new \Exception($e->getMessage());
        }

        return $res;
    }
}
