<?php

namespace App\Actions;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexAction
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {    
        return [
            'test' => 'ok'
        ];
    }
}