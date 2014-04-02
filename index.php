<?php

    require_once 'Horus/Horus.php';
    
    new Horus(['horus.use_router'=>yes]);
    
    horus('router') -> any('/', function(){
        echo 'index';
    });