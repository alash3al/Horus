<?php

    // load the framework
    include "H9.php";

    // prepare it
    $app    =   new Horus('ob_gzhandler');

    // res   = response object
    // sendt = send a template file to the output manager
    $app->res->sendt('wiki.html');

    // end it
    $app->run();
