<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.0.1
 * @package     Horus
 * @filesource
 */
 
// -------------------------------------------------------------------


/**
 * View
 * 
 * Horus Templates Viewer
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_View
{
    /** @ignore */
    protected $vars = array();
    /** @ignore */
    protected $dir;
    /** @ignore */
    protected $ext;
    
    /**
     * Class Constructor
     * 
     * @param string $views_directory   the templates/views directory
     * @param string $views_extension   the extension of the views
     * @return object
     */
    function __construct($views_directory, $views_extension)
    {
        if(file_exists($views_directory))
        {
            $this->dir = realpath($views_directory) . DIRECTORY_SEPARATOR;
        }
        $this->ext = '.' . ltrim($views_extension, '.');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Assign Var(s)
     * 
     * Assign var to the view-data so it can be passed by default to any view
     * 
     * @param string $var
     * @param string $value
     * @return void
     */
    function addVar($var, $value = null)
    {
        if(is_array($var)) {
            foreach($var as $k => &$v) {
                call_user_func(array($this, __FUNCTION__), $k, $v);
            }
        }
        else
            $this->vars[$var] = $value;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get var from assigned vars
     * 
     * @param string $var
     * @return mixed
     */
    function getVar($var)
    {
        return (isset($this->vars[$var]) ? $this->vars[$var] : null);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Remove var
     * 
     * @param string $var
     * @return void
     */
    function unsetVar($var)
    {
        unset($var);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * check if a var exists
     * 
     * @param string $var
     * @return bool
     */
    function hasVar($var)
    {
        return isset($this->vars[$var]);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load a view and return it as string
     * 
     * @param string $view_name     the view filename
     * @param array $vars           array of extra vars to pass to the view
     * @return string
     */
    function load($view_name, array $vars = array())
    {
        if(file_exists($file = $this->dir . $view_name . $this->ext))
        {
            extract(array_merge($this->vars,(array) $vars));
            
            ob_start();
            
            include $file;
            
            return ob_get_clean();
        }
        else
            return false;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Display a view file
     * 
     * @param string $view_name     the view filename
     * @param array $vars           array of extra vars to pass to the view
     * @return void
     */
    function render($view_name, $vars = null)
    {
        echo $this->load($view_name, (array)$vars);
    }
}