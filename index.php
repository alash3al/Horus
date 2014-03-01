<?php 

/**
* Any Horus Applications Contains 3 Parts
* 
* 1- Head      horus constructor
* 2- Body      your application code
* 3- Footer    horus dispatcher
* 
* But first include Horus
*/

require_once 'Horus/Horus.php';

/**
* Construct Horus and set some configs
* tell horus to simulate htaccess mode_rewrite
*/

$horus = new Horus(array
(
    'horus.enable_simulator'    =>  false,
    'horus.use_db'              =>  true,
    'horus.use_orm'             =>  true,
    'horus.use_view'            =>  true,
));

/**
* Out project body
* Write it's routes here
*/

$horus->router->any('/', create_function('', '
    go(asset("/wiki"), "html");
'));


/**
* Our project footer
* End every thing and dispatch horus
*/

$horus->run();