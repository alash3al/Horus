<?php
/*
 *---------------------------------------------------------------
 * Load Horus Kernel .
 *---------------------------------------------------------------
 */
    require_once 'Horus/Horus.php';
/*
 *---------------------------------------------------------------
 * Start Horus ( without any configs )
 *---------------------------------------------------------------
 * - By defult when you call "new Horus" you start horus framework
 *   without router and sql class, also by default horu.simulator is
 *   off .
 * 
 * - But first what is horus simulator ?
 *   Horus simulator you will need it when you use the router and
 *   your server doesn't support mod_rewrite or any url_writer, so
 *   horus provides you with  simple way to do this from here :) .
 * 
 * - How to config ?
 *   on construction :
 *      new Horus(array(
 *          'horus.use_sql'             =>  true, // use sql-class and start it .
 *          'horus.use_router'          =>  true, // use the router and auto-configure it .
 *          'horus.enable_simulator'    =>  true  // use horus simulator .
 *      ));
 */
    $app = new Horus;
/*
 *---------------------------------------------------------------
 * Hello World .
 *---------------------------------------------------------------
 */
    echo "Hello World";
/*
 *---------------------------------------------------------------
 * Run Horus Application .
 *---------------------------------------------------------------
 * This will do this :
 *      - catch the output .
 *      - apply some settings .
 *      - trigger the before.distpatch events "before router starting responding" .
 *      - dispatch all routes .
 *      - trigger the after.dispatch events .
 *      - catch the output and append it to the first caught .
 *      - trigger the before output events .
 *      - send the output to the http reponse manager .
 *      - tell the response manager to send the output to the browser .
 *      - trigger the after output events .
 */
    $app->run();
