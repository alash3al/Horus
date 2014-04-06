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
    protected $vars, $dir, $ext, $shortcuts;
    
    // --------------------------------------------------------------------
    
    /**
     * Constructor
     * 
     * @param string $extension
     * @return void
     */
    function __construct($extension = 'html')
    {
        $this->ext = '.' . ltrim($extension, '.');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Add View Shortcut(s)
     * 
     * @param string $shortcut
     * @param string $path
     * @return void
     */
    function shortcut($shortcut, $path = null)
    {
        $shortcut = is_array($shortcut) ? $shortcut : array($shortcut => $path);
        foreach($shortcut as $s => &$p) {
            if(file_exists($p)) {
                $this->shortcuts[$s] = realpath($p);
            }
        }
    }
    
    // --------------------------------------------------------------------

    /**
     * Remove View Shortcut(s)
     * 
     * @param string $shortcut
     * @param string $path
     * @return void
     */
    function unshortcut($shortcut)
    {
        foreach((array) $shortcut as $s) {
            unset($this->shortcuts[$s]);
        }
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
        $var = is_array($var) ? $var : array($var => $value);
        $this->vars = array_merge((array) $this->vars, $var);
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
        foreach((array) $var as $v) {
            unset($this->vars[$v]);
        }
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
        $view_name = str_ireplace(array_keys((array) $this->shortcuts), array_values((array) $this->shortcuts), $view_name);
        $view_name = (array) explode(',', $view_name);
        
        extract(array_merge((array) $this->vars,(array) $vars));
        ob_start();
        
        foreach($view_name as &$v) {
            if(is_file($file = realpath(trim($v) . $this->ext))) {
                require $file;
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
