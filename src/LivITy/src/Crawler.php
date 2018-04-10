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
    public abstract function recurse(String $path, String $sink);

    /**
     *
     */
    public function request(String $path, String $sink)
    {
        try {
            if ($sink === '') {
                $res = $this->props['client']->request('GET', $path);
            } else {
                $res = $this->props['client']->request('GET', $path, [
                    'sink' => $sink
                ]);
            }
        } catch(TransferException $e) {
            throw new \Exception($e->getMessage());
        }

        return $res;
    }
}
