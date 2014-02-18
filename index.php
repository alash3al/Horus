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
 
 $horus = new Horus(array('horus.enable_simulator' => true));
 
 
/**
 * Out project body
 * Write it's routes here 
 */
 
 $horus->router->any('/', create_function('', '
    Horus::getInstance()->http->redirect(asset("wiki"));
 '));

/**
 * Our project footer
 * End every thing and dispatch horus
 */
 
 $horus->run();