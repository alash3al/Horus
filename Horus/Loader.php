<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/version-4.0.0.html
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     4.0.0
 * @package     Horus
 * @filesource
 */

// -------------------------------------------------------------------

/**
 * Horus Universal Classes Loader
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    4.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Loader
{
    /** @ignore */
    protected $paths = array();
    /** @ignore */
    protected $basedir;

    // ---------------------------------------

    /**
     * Constructor
     * 
     * @param string $base_dir
     * @return object
     */
    function __construct($base_dir)
    {
        $this->basedir = realpath($base_dir) . DIRECTORY_SEPARATOR;
        spl_autoload_register(array($this, 'load'));
    }

    // ---------------------------------------

    /**
     * Register a new name
     * 
     * @param string $name
     * @param string $path
     * @return void
     */
    function register($name, $path = null)
    {
        $name = is_array($name) ? $name : array($name => $path);
        foreach($name as $n => &$p) {
            $this->paths[$this->_prepare($n)] = realpath($p) . DIRECTORY_SEPARATOR;
        }
    }

    // ---------------------------------------

    /**
     * Unregister a name
     * 
     * @param string $name
     * @return void
     */
    function unregister($name)
    {
        unset($this->paths[$this->_prepare($name)]);
    }

    // ---------------------------------------
    
    /**
     * Gets array of registered paths
     * 
     * @return array
     */
    function get()
    {
        return $this->paths;
    }

    // ---------------------------------------

    /**
     * Classes Loader
     * 
     * @param string $needle
     * @return bool
     */
    function load($needle)
    {
        // if the needle already a file just load it
        if(is_file($needle)) {
            return require_once $needle;
        }

        // short the directory separator
        // prepare the class name
        // detect the prefix/namespace
        // if the file . php exists then load it
        // else then search it a step deeper
        // else cannot find it (false)
        $ds = DIRECTORY_SEPARATOR;
        $needle = $this->_prepare($needle);
        $paths = array_reverse($this->paths);
        foreach($paths as $n => &$p) {
            $n = $this->_prepare($n);
            if(stripos($needle, $n) === 0) {
                $needle = str_ireplace($n, rtrim($p, $ds), $needle);
                if(is_file($f = $needle . '.php')) {
                    return require_once $f;
                } elseif(is_file($f = $needle . $ds . basename($needle) . '.php')) {
                    return require_once $f;
                }
            }
        }
    }

    // ---------------------------------------

    /** @ignore */
    function _prepare($str)
    {
        $str = trim(str_replace(array('\\', '/', '_'), DIRECTORY_SEPARATOR, $str));
        return ltrim(rtrim($str, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
    }
}