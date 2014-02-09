<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.0.0
 * @package     Horus
 * @filesource
 */
 
// --------------------------------------------------------------------

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

    /**
     * Router::__construct()
     * 
     * @param bool $simulate    simulate rewriter (e.g: htaccess mod rewrite) ?
     * @return object
     */
    public function __construct($simulate = true)
    {
        # set new server vars
        $_SERVER['SERVER_URL'] = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . rtrim($_SERVER['SERVER_NAME'], '/') . '/' ;
        $_SERVER['SCRIPT_URL'] = $_SERVER['SERVER_URL'].ltrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/', '/');
        $_SERVER['SCRIPT_URI'] = $_SERVER['SCRIPT_URL'].($simulate === true ? basename($_SERVER['SCRIPT_NAME']) . '/' : '');
        
        # Force Redirection To 'script_name/' if simulation enabled
        if($simulate === true and (!isset($_SERVER['PATH_INFO']))) {
            go($_SERVER['SCRIPT_URI']);
        }
        
        # Update the PATH_INFO
        $uri = $_SERVER['REQUEST_URI'];
        @list($u, $_SERVER['QUERY_STRING']) = (array)explode('?', $uri);
        
        # Prepare $u and set the path_info
        # The path_info starts after current installation dir, and current filename
        $base_1 = addcslashes($_SERVER['SCRIPT_NAME'], './');
        $base_2 = addcslashes(dirname($_SERVER['SCRIPT_NAME']), './');
        
        # Must Remove The 'base/dir/and-file' if exists
        # Or 'base/dir' if exists
        if(preg_match("/^{$base_1}/i", $u))
            $u = preg_replace("/^{$base_1}/i", '', $u);
        elseif(preg_match("/^{$base_2}/i", $u))
            $u = preg_replace("/^{$base_2}/i", '', $u);
        
        # prepare $u
        $u = preg_replace('/\/+/', '/', '/'.ltrim(rtrim($u ,'/'), '/').'/');
        
        # update path_info
        $_SERVER['PATH_INFO'] = $u;
        
        # Update $_GET
        parse_str($_SERVER['QUERY_STRING'], $_GET);
        
        # Set extra values
        $_SERVER['RAW_INPUT'] = (string)@file_get_contents('php://input'); 
        
        # free some memory
        unset($uri, $u, $base_1, $base_2);
    }

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
            
            $this->maps[strtolower($method)][$uri] = $callback;
            
            return true;
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
    public function dispatch()
    {
        @$cm = (array)$this->maps[strtolower($_SERVER['REQUES_METHOD'])];
        @$am = (array)$this->maps['any'];
        
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