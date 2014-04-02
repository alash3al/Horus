<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     3.0.0
 * @package     Horus
 * @filesource
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
 
// -------------------------------------------------------------------

/**
 * View
 * 
 * This class will help with views just give the views directory and
 * extension of the views then start playing :)
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
    
    // --------------------------------------------------------------------
    
    /**
     * setup settings
     * 
     * @param string $views_dir
     * @param string $views_extension
     * @return void
     */
    function setup($views_directory, $views_extension)
    {
        $this->dir = realpath($views_directory) . DIRECTORY_SEPARATOR;
        $this->ext = '.' . ltrim($views_extension, '.');
        return $this;
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
        unset($this->vars[$var]);
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
     * Return value of view(s)
     * 
     * @param string $view_name     the view filename, can also be an array of views
     * @param array $vars           array of extra vars to pass to the view
     * @return string
     */
    function load($view_name, array $vars = array())
    {
        $view_name = (array) explode(',', $view_name);
        
        extract(array_merge((array) $this->vars,(array) $vars));
        
        ob_start();
        
        foreach($view_name as &$v) {
            if(is_file($file = $this->dir . trim($v) . $this->ext)) {
                include $file;
            }
        }
        
        unset($v, $vars, $view_name, $file);
        return ob_get_clean();
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
