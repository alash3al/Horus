<?php

    include "H10.php";

    Horus('ob_gzhandler');

    Res::render('wiki.html');

    Horus::run();
