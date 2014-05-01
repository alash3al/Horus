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
 * Horus Routing System
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    4.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Router
{
    /** @ignore */
    protected $routes   =   array();
    /** @ignore */
    protected $vars     =   array();
    /** @ignore */
    protected $state    =   0;
    /** @ignore */
    protected $url      =   null;
    /** @ignore */
    protected $dir      =   null;

    // -------------------------------------------

    /**
     * Router Constructor
     * 
     * @return void
     */
    function __construct($default_url, $controllers_dir = null)
    {
        $this->url = $this->_u($default_url);
        $this->shortcut(array(
            '{num}'      =>  '([0-9\.,]+)',
            '{alpha}'    =>  '([a-zA-Z]+)',
            '{alnum}'    =>  '([a-zA-Z0-9\.]+)',
            '{str}'      =>  '([a-zA-Z0-9-_\.]+)',
            '{any}'      =>  '(.+)',
            '{*}'        =>  '?|(.*?)'
        ));
        $this->dir = $controllers_dir;
    }

    // -------------------------------------------

    /** @ignore */
    function __call($name, $args)
    {
        $name = (strpos($name, '_') !== false ? (array) explode('_', $name) : $name);
        if(count($args) < 3) $args[] = false;
        return call_user_func_array(array($this, 'route'), array_merge($args, array($name)));
    }

    // -------------------------------------------

    /**
     * Dispatch routes
     * 
     * @param array     $route  array of routes or null
     * @param string    $url  the url to parse and dispatch based on it  
     * @return bool
     */
    public function dispatch($url = null, array $route = array())
    {
        $route          =   empty($route) ? $this->routes : $route;
        $url            =   empty($url) ? $this->url : $this->_u($url);
        $current_method =   isset($route[strtolower($_SERVER['REQUEST_METHOD'])]) ? $route[strtolower($_SERVER['REQUEST_METHOD'])] : null;
        $any_method     =   isset($route['any']) ? $route['any'] : null;
        $methods        =   (array) array_merge( (array)$current_method, (array)$any_method);
        
        unset($current_method, $any_method);
        
        foreach($methods as $p => &$clbk):
            // if the clbk is callback then match the url from start to end .
            if(is_callable($clbk) and preg_match("/^{$p}$/", $url, $params)) {
                ++$this->state;
                array_shift($params);
                call_user_func_array($clbk, $params);
            }
            // if the clbk is class then match the url from start only .
            elseif(!is_object($clbk) and $this->class_exists($clbk) and preg_match("/^{$p}/", $url)) {
                $segments = array_values(array_filter((array) explode('/', preg_replace("/^{$p}/i",'', $url))));
                $class = new $clbk;
                $method = empty($segments[0]) ? 'index' : $segments[0];
                $method = str_replace('-', '_', pathinfo($method, PATHINFO_FILENAME));
                array_shift($segments);
                
                // applying
                if(is_callable(array($class, $method)) and method_exists($class, $method) and $method{0} !== '_') {
                    ++$this->state;
                    call_user_func_array(array($class, $method), (array) $segments);
                }
            }
        endforeach;

        return $this->state;
    }

    // -------------------------------------------

    /**
     * Request another route
     * 
     * @param string $url
     * @return false | string
     */
    public function request($url, $method = 'get', array $params = array())
    {
        // setting the request method
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);

        // setting the global params
        $m = strtolower($_SERVER['REQUEST_METHOD']);
        if($m == 'post') {
            $_POST = array_merge($_POST, $params);
        } elseif($m !== 'get') {
            @file_put_contents('php://input', http_build_query($params));
        }

        // start output controller
        ob_start();

        $s = $this->dispatch($this->_u($url), array());
        if($s < 1) {
            ob_end_clean();
            return false;
        }

        // return the routes output
        return ob_get_clean();
    }

    // -------------------------------------------

    /**
     * Add regex shortcut(s)
     * 
     * @param string $key
     * @param string $value
     * @return void
     */
    public function shortcut($key, $value = null)
    {
        $key = !is_array($key) ? array($key => $value) : $key;
        
        foreach($key as $k => &$v) {
            $this->vars[$k] =  $v;
        }
    }

    // -------------------------------------------

    /**
     * Remove regex shortcut(s)
     * 
     * @param string $key
     * @return void
     */
    public function unshortcut($key)
    {
        foreach( (array) $key as $k ) {
            unset($this->vars[$k]);
        }
    }

    // -------------------------------------------

    /**
     * Gets all available shortcuts
     * 
     * @return array
     */
    function shortcuts()
    {
        return $this->vars;
    }

    // -------------------------------------------

    /**
     * Router state
     * 
     * @return bool
     */
    public function state()
    {
        return $this->state > 0;
    }

    // -------------------------------------------
    
    /**
     * Get url segment(s)
     * 
     * @param   int     $needle
     * @param   string  $base
     * @return  mixed
     */
    public function segments($needle = null, $base = '\/')
    {
        $url = preg_replace("/^{$base}/i", '', $this->_u($this->url), 1);
        $segments = array_values(array_filter((array) explode('/', $url)));
        
        return (is_null($needle) or $needle == '') ? (array) $segments : @$segments[$needle];
    }

    // -------------------------------------------

    /** @ignore */
    protected function route($pattern, $callback, $dispatch = false, $method = 'any')
    {
        if(is_array($pattern)) {
            foreach($pattern as &$p) {
                call_user_func_array(array($this, 'route'), array($p, $callback, $method));
            }
            
            return ;
        }
        
        if(is_array($method)) {
            foreach($method as &$m) {
                call_user_func_array(array($this, 'route'), array($pattern, $callback, $m));
            }
            
            return ;
        }
        
        $method     =   strtolower($method);
        $pattern    =   str_ireplace(array_keys($this->vars), array_values($this->vars), $pattern);
        $pattern    =   $this->_u($pattern, '/');
        
        if($dispatch == true) {
            return (bool) $this->dispatch(null, array($method => array($pattern => $callback)));
        }
        
        $this->routes[$method][$pattern] = $callback;
    }

    // -------------------------------------------

    /** @ignore */
    protected function _u($uri, $escape = '')
    {
        $uri = addcslashes(preg_replace( '/\/+/', '/', ('/'. rtrim(ltrim($uri, '/'), '/') .'/') ), $escape);
        
        return $uri;
    }

    // -------------------------------------------
    
    /** @ignore */
    protected function class_exists($class_name)
    {
        if(class_exists($class_name)) {
             return TRUE;
        } elseif( !is_dir($this->dir) or empty($this->dir) ) {
            return FALSE;
        } elseif(is_file($f = realpath($this->dir) . DIRECTORY_SEPARATOR . $class_name . '.php')) {
            include_once $f;
            return class_exists($class_name);
        } elseif(is_file($f = realpath($this->dir) . DIRECTORY_SEPARATOR . $class_name . DIRECTORY_SEPARATOR . basename($class_name) . '.php')) {
            include_once $f;
            return class_exists($class_name);
        }
        return FALSE;
    }
}