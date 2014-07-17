 <?php

    // load Horus.php
    include 'Horus.php';

    // start horus
    $app = new Horus;

        $app->router->get('/', BASEPATH . 'wiki.html');

    // run it
    $app->run();
