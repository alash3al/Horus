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


    /**
     * Map and add uri-pattern to the router
     * 
     * @param string        $pattern    the uri pattern
     * @param callback      $callback   the uri callback/class
     * @param string        $method     the request method
     * @return bool
     */
    public function map($pattern, $callback, $method = 'any')
    {
        if(sizeof($x = (array)array_filter(explode('|', $method))) > 1)
        {
            foreach($x as &$m) {
                call_user_func_array(array($this, __FUNCTION__), array($pattern, $callback, $m));
            }
        }
        elseif(is_array($pattern))
        {
            foreach($pattern as &$pt) {
                call_user_func_array(array($this, __FUNCTION__), array($pt, $callback, $method));
            }
        }
        else
        {
            if(!is_callable($callback) and !class_exists($callback))
                return false;
            
            $x = array('{num}', '{alpha}', '{alnum}', '{str}', '{any}', '{*}');
            $y = array('([0-9\.]+)', '([a-zA-z]+)', '([a-zA-Z0-9\.]+)', '([a-zA-Z0-9-_\.]+)', '.+', '?|(.*?)');
            
            $uri = $this->prepare_uri(str_ireplace($x, $y, $pattern), '/');
            
            if($this->dispatched === false )
            {
                $this->maps[strtolower($method)][$uri] = $callback;
                return true;
            }
            else
            {
                return $this->dispatch(array($method => array($uri => $callback)));
            }
        }
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
        
        if(empty($maps) or !is_array($maps)) $maps = $this->maps;
        
        @$cm = (array)$maps[strtolower($_SERVER['REQUES_METHOD'])];
        @$am = (array)$maps['any'];
        
        $maps = (array)array_merge((array)$cm, (array)$am);
        
        foreach($maps as $pattern => &$callback)
        {
            // if the callback is any type of php callbacks ... call it
            if(is_callable($callback) and preg_match('/^'.$pattern.'$/', $_SERVER['PATH_INFO'], $m))
            {
                unset($m[0]);
                
                call_user_func_array($callback, $m);
                return true;
            }
            // the callback is class then, deal with it
            elseif(!is_object($callback) and @class_exists($callback))
            {
                if(preg_match('/^'.$pattern.'/', $_SERVER['PATH_INFO']))
                {
                    $x = preg_replace('/^('.$pattern.')/', '', $_SERVER['PATH_INFO']);
                    $segments = array_values(array_filter(explode('/', $x)));
                    
                    unset($x);
                    
                    $class = new $callback;
                    $func = empty($segments[0]) ? 'index' : $segments[0];
                    
                    if(!is_callable(array($class, $func)))
                        return false;
                    
                    array_shift($segments);
                    
                    call_user_func_array(array($class, $func), (array)$segments);
                    
                    return true;
                }
            }
        }
        return false;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get URI Segments
     * 
     * @return array
     */
    public function segments()
    {
        return (array)array_values(array_filter((array) explode('/', $_SERVER['PATH_INFO'])));
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this, 'map'), array_merge($args, array($name)));
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    protected function prepare_uri($uri, $escape = '')
    {
        $uri = preg_replace('/\/+/', '/', ('/' . rtrim(ltrim($uri, '/'), '/') . '/'));
        
        if(empty($uri)) $uri = '/';
        
        $uri = addcslashes($uri, $escape);
        
        return $uri;
    }
}