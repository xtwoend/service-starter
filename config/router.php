<?php

$router->get('/', [\App\Actions\IndexAction::class, 'index']);

$router->get('/ping', function(){
    return 'pong';
});