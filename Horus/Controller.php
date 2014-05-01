<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/version-4.0.0.html
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     4.1.0
 * @package     Horus
 * @filesource
 */

// -------------------------------------------------------------------

/**
 * Horus Simple Controller Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal
 * @since       4.1.0
 * @copyright   2014 Mohammed Al-Ashaal
 */
class Horus_Controller
{
    /**
     * Index handler
     * 
     * @return void
     */
    function index()
    {
        echo 'Override me';
    }

    // -----------------------------------------

    /** @ignore */
    function __call($a, $b)
    {
        return call_user_func_array(array(Horus::instance(), $a), $b);
    }

    // -----------------------------------------

    /** @ignore */
    function __get($a)
    {
        return @Horus::instance()->$a;
    }

    // -----------------------------------------

    /** @ignore */
    function __set($a, $b)
    {
        Horus::instance()->$a = $b;
    }
}