<?php

    require "Horus.php";

    use Horus\Horus;

    $app = new Horus;

    $app->all('/api/json', function($req, $res, $app){
        $res->json(array(
           'status' =>  'ok' 
        ));
    });

    $app->run();
