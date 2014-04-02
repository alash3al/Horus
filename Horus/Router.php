<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     2.0.0
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
 * Routing System
 * 
 * Smart Featured Routing system
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Router
{
    /** @ignore */
    protected $maps = array();
    protected $dispatched = false;
    protected $uri;

    // --------------------------------------------------------------------
    
    /**
     * Constructor
     * 
     * @return  object
     */
    public function __construct()
    {
        @$this->uri = $this->prepare_uri($_SERVER['PATH_INFO']);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Dispatch
     * 
     * Loop over the registered uri-patterns and find which to apply
     * 
     * @return bool
     */
    public function dispatch($maps = null)
    {
        $this->dispatched = true;
        
        if(empty($maps) or !is_array($maps)) {
            $maps = $this->maps;
        }
        
        $cm = (array) (isset($maps[strtolower($_SERVER['REQUEST_METHOD'])]) ? $maps[strtolower($_SERVER['REQUEST_METHOD'])] : null);
        $am = (array) (isset($maps['any']) ? $maps['any'] : null);
        $maps = (array)array_merge((array)$cm, (array)$am);
        $status = 0;
        
        foreach($maps as $pattern => &$r)
        {
            list($callback, $permission) = $r;
            
            // if the callback is any type of php callbacks ... call it
            if(is_callable($callback) and preg_match('/^'.$pattern.'$/', $this->uri, $m))
            {
                unset($m[0]);
                $m[] = (bool) $this->permission($permission);
                call_user_func_array($callback, $m);
                ++$status;
                
            }
            // the callback is class then, deal with it
            elseif(!is_object($callback) and class_exists($callback)) 
            {
                if(preg_match('/^'.$pattern.'/', $this->uri))
                {
                    $x = preg_replace('/^('.$pattern.')/', '', $this->uri);
                    $segments = array_values(array_filter(explode('/', $x)));
                    unset($x);
                    $class = new $callback;
                    $func = empty($segments[0]) ? 'index' : $segments[0];
                    $func = str_replace('-', '_', pathinfo($func, PATHINFO_FILENAME));
                    $segments[] = (bool) $this->permission($permission);
                    
                    if(is_callable(array($class, $func))) {
                        array_shift($segments);
                        call_user_func_array(array($class, $func), (array)$segments);
                        ++$status;
                    }
                }
            }
        }
        
        return ($status > 0);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get URI Segment(s)
     * 
     * @return array | string
     */
    public function segments($index = null)
    {
        $all = (array)array_values(array_filter((array) explode('/', $this->uri)));
        return isset($all[$index]) ? $all[$index] : $all;
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this, 'map'), array_merge($args, array(str_replace('_', '|', $name))));
    }
    
    // --------------------------------------------------------------------

    /**
     * Map and add uri-pattern to the router
     * 
     * @param string        $pattern    the uri pattern
     * @param callback      $callback   the uri callback/class
     * @param string        $method     the request method
     * @return bool
     */
    protected function map($pattern, $callback, $permission = null, $method = 'any')
    {
        // multiple methods
        if(sizeof($x = (array)array_filter(explode('|', $method))) > 1)
        {
            foreach($x as &$m) {
                call_user_func_array(array($this, __FUNCTION__), array($pattern, $callback, $m));
            }
            
            unset($x);
        }
        // multiple uris/patterns
        elseif(is_array($pattern))
        {
            foreach($pattern as &$pt) {
                call_user_func_array(array($this, __FUNCTION__), array($pt, $callback, $method));
            }
        }
        // the core
        else
        {
            if(!is_callable($callback) and !class_exists($callback)) {
                return false;
            }
            
            $x = array('{num}', '{alpha}', '{alnum}', '{str}', '{any}', '{*}');
            $y = array('([0-9\.]+)', '([a-zA-Z]+)', '([a-zA-Z0-9\.]+)', '([a-zA-Z0-9-_\.]+)', '.+', '?|(.*?)');
            $uri = $this->prepare_uri(str_ireplace($x, $y, $pattern), '/');
            
            if($this->dispatched === false ) {
                $this->maps[strtolower($method)][$uri] = array($callback, $permission);
                return true;
            } else {
                return $this->dispatch(array($method => array($uri => array($callback, $permission))));
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    protected function prepare_uri($uri, $escape = '')
    {
        $uri = preg_replace('/\/+/', '/', ('/' . rtrim(ltrim($uri, '/'), '/') . '/'));

        if(empty($uri)) {
            $uri = '/';
        }
        
        $uri = addcslashes($uri, $escape);
        
        return $uri;
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    protected function permission($allowed)
    {
        if(is_null($allowed) or empty($allowed) or $allowed == '*') {
            return true;
        }
        
        return (bool) (
            isset($_SESSION['permission']) and
            in_array($_SESSION['permission'], (array) $allowed)
        );
    }
}