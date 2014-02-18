<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.2.0
 * @package     Horus
 * @filesource
 */
 
// -------------------------------------------------------------------


/**
 * Horus Loader Class
 * 
 * Works like pear naming convention and also supports namespaces
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Loader
{
    /** @ignore */
    protected $vendors = array();
    
    
    /**
     * Constructor
     * 
     * @return void
     */
    function __construct()
    {
        spl_autoload_register(array($this, 'load'));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load a file
     * 
     * @param string $filename
     * @return bool
     */
    function load($filename)
    {
        if(is_file($filename)) {
            return include $filename;
        }
        
        if(is_file($filename.'.php')) {
            return include $filename.'.php';
        }
        
        if(is_file($f = $this->realpath($filename))) {
            return include $f;
        }
        
        return false;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Add your own vendor
     * 
     * @param string $vendor_name
     * @param string $path
     * @param string $extension
     * @return bool
     */
    function addVendor($vendor_name, $path, $extension = 'php')
    {
        $path = realpath($path);
        
        if(empty($path) or !$path or !is_dir($path)) {
            return false;
        }
        
        $vendor_name = $this->_toPrefix($vendor_name);
        
        if(empty($vendor_name)) {
            return false;
        }
        
        $this->vendors[strtolower($vendor_name)] = array($path . DIRECTORY_SEPARATOR, '.' . ltrim($extension, '.'));
        
        return true;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get realpath of class filename
     * 
     * @param string $filename
     * @return string
     */
    function realpath($filename)
    {
        $filename = $this->_toPrefix($filename);
        
        if(isset($this->vendors[strtolower($filename)])) {
            list($path, $ext) = $this->vendors[strtolower($filename)];
            $filename = $path . basename($path) . $ext;
        }
        elseif(strpos($filename, '_') !== False) {
            list($vendor, $file) = explode('_', $filename, 2);
            
            if(isset($this->vendors[strtolower($vendor)])) {
                list($path, $ext) = $this->vendors[strtolower($vendor)];
                if(!file_exists($filename = $path . $file . $ext)) {
                    $filename = $path . $file . DIRECTORY_SEPARATOR . basename($file) . $ext;
                }
            }
        }
        
        return realpath($filename);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * prepare filename with prefixes
     * 
     * @param string $name
     * @return string
     */
    protected function _toPrefix($name)
    {
        $name = str_replace(array('\\', '/'), '_', $name);
        $name = rtrim(ltrim($name, '_'), '_');
        
        return preg_replace('/_+/', '_', $name);
    }
}