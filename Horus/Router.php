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
    protected $vars     =   array();
    /** @ignore */
    protected $state    =   0;
    /** @ignore */
    protected $url      =   null;
    
    // -------------------------------------------
    
    /**
     * Router Constructor
     * 
     * @return void
     */
    function __construct()
    {
        $this->url = $this->prepareUri($_SERVER['PATH_INFO']);
        $this->addVar(array(
            '{num}'      =>  '([0-9\.,]+)',
            '{alpha}'    =>  '([a-zA-Z]+)',
            '{alnum}'    =>  '([a-zA-Z0-9\.]+)',
            '{str}'      =>  '([a-zA-Z0-9-_\.]+)',
            '{any}'      =>  '(.+)',
            '{*}'        =>  '?|(.*?)'
        ));
    }
    
    // -------------------------------------------
    
    /** @ignore */
    function __call($name, $args)
    {
        if(count($args) < 3) {
            $args[] = '*';
        }
        
        $name = (strpos($name, '_') !== false ? (array) explode('_', $name) : $name);
        return call_user_func_array(array($this, 'map'), array_merge($args, array($name)));
    }
    
    // -------------------------------------------
    
    /**
     * Add Router Varibale
     * 
     * @param string $key
     * @param string $value
     * @return void
     */
    function addVar($key, $value = null)
    {
        $key = !is_array($key) ? array($key => $value) : $key;
        
        foreach($key as $k => &$v) {
            $this->vars[$k] =  $v;
        }
    }
    
    // -------------------------------------------

    /**
     * Get URL Segments
     * 
     * @param int $index
     * @return mixed
     */
    function segments($index = null)
    {
        $all = (array)array_values(array_filter((array) explode('/', $this->url)));
        return isset($all[$index]) ? $all[$index] : $all;
    }
    
    // -------------------------------------------

    /**
     * Router state
     * 
     * @return bool
     */
    function state()
    {
        return $this->state > 0;
    }
    
    // -------------------------------------------
    
    /** @ignore */
    protected function map($pattern, $callback, $permission = '*', $method = 'any')
    {
        if(is_array($pattern)) {
            foreach($pattern as &$p) {
                call_user_func_array(array($this, __FUNCTION__), array($p, $callback, $permission, $method));
            }
            
            return ;
        }
        
        if(is_array($method)) {
            foreach($method as &$m) {
                call_user_func_array(array($this, __FUNCTION__), array($pattern, $callback, $permission, $m));
            }
            
            return ;
        }
        
        $method     =   strtolower($method);
        $pattern    =   str_ireplace(array_keys($this->vars), array_values($this->vars), $pattern);
        $pattern    =   $this->prepareUri($pattern,'/');
        //die($pattern);
        $this->dispatch(array($method => array($pattern => array($callback, $permission))));
    }
    
    // -------------------------------------------
    
    /** @ignore */
    protected function dispatch($map = array())
    {
        $current_method = isset($map[strtolower($_SERVER['REQUEST_METHOD'])]) ? $map[strtolower($_SERVER['REQUEST_METHOD'])] : null;
        $any_method = isset($map['any']) ? $map['any'] : null;
        $methods = (array) array_merge( (array)$current_method, (array)$any_method);
        unset($current_method, $any_method);
        
        foreach($methods as $p => &$i) {
            list($clbk, $perm) = $i;
            // if the clbk is callback then match the url from start to end .
            if(is_callable($clbk) and preg_match("/^{$p}$/", $this->url, $params)) {
                ++$this->state;
                array_shift($params);
                $params[] = $this->permission($perm);
                call_user_func_array($clbk, $params);
            }
            // if the clbk is callback then match the url from start only .
            elseif(!is_object($clbk) and class_exists($clbk)and preg_match("/^{$p}/", $this->url)) {
                $segments = $this->segments();
                $class = new $clbk;
                $method = empty($segments[0]) ? 'index' : $segments[0];
                $method = str_replace('-', '_', pathinfo($method, PATHINFO_FILENAME));
                array_shift($segments);
                
                if(is_callable(array($class, $method))) {
                    ++$this->state;
                    $segments[] = $this->permission($perm);
                    call_user_func_array(array($class, $method), (array) $segments);
                }
            }
        }
    }
    
    // -------------------------------------------
    
    /** @ignore */
    protected function permission($permission = '*')
    {
        if(empty($permission) or $permission == '*') {
            return true;
        }
        
        return (bool) (
            isset($_SESSION['permission'])  and
            in_array($_SESSION['permission'], (array) $permission)
        );
    }
    
    // -------------------------------------------
    
    /** @ignore */
    protected function prepareUri($uri, $escape = '')
    {
        $uri = addcslashes(preg_replace( '/\/+/', '/', ('/'. rtrim(ltrim($uri, '/'), '/') .'/') ), $escape);
        
        return empty($uri) ? '\/' : $uri;
    }
}