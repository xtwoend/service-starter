<?php

namespace App\Actions;

use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexAction
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {   
        $us = Db::table('users')->get();
        return [
            'test' => 'ok',
            'db'    => $us
        ];
    }
}