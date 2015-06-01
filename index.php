<?php

    require("Horus.php");

    (new \Horus\App)->on('/', function($req, $res){
        $res->end("Hello World");
    });
