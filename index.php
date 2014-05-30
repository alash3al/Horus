<?php   require_once 'Horus.php';

        // start horus
        $app    =   new Horus;

        // hello world
        $app->router->get('/x', create_function('', ' echo "Hello world"; '));

        // run
        $app->run();