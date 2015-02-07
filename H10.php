<?php @ ob_clean(); (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) && die('<h1>Direct access not allowed');
/**
 * Horus PHP Micro Framework
 * 
 * @package     Horus
 * @author      Mohammed Al Ashaal <http://is.gd/alash3al>
 * @version     10.1
 * @license     MIT License
 * @copyright   2014 (c) Mohammed Al Ashaal
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

// -------------------------------

/**
 * Horus_Exception
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    9.0.0    
 */
Class Horus_Exception extends Exception{}

// -------------------------------

/**
 * Horus_Facade
 *
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    10.0.0
 *
 * @property array   $instances
 */
Class Horus_Facade
{
    /**
     * Array of registered instances of this class
     * @var array
     */
    protected static $instances = array();

    /**
     * Return an instance of the current called class
     * @return  object
     */
    public static function instance()
    {
        if ( !isset(self::$instances[$class = get_called_class()]) )
            self::$instances[$class] = new $class;

        return self::$instances[$class];
    }

    /**
     * Return the object to be used in static access
     * @return  mixed
     */
    public function target()
    {
        throw new Horus_Exception(sprintf('You must override "%s"', __METHOD__));
    }

    /**
     * Access a class methods using static style
     * 
     * @param   string  $name       the name of method to be called
     * @param   array   $arguments  array of arguments to be passed to the method
     * @return  mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $class = self::instance()->target();

        if ( is_callable($c = array($class, $name)) )
            return call_user_func_array($c, $arguments);
        else
            return false;
    }
}

// -------------------------------

/**
 * Horus_Container
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    5.1.0
 *
 * @property array      $scope
 * @property callable   $key_filter
 */
Class Horus_Container implements ArrayAccess, IteratorAggregate
{
    /**
     * Storage array of registered values
     * @var array
     */
    protected $scope    =   array();

    /**
     * Key filter
     * @var callable
     */
    public $key_filter;

    /**
     * Constructor
     * 
     * @param   array $scope    an array to be set to the storage array
     */
    public function __construct(array $scope = array())
    {
        $this->scope    =   $scope;
    }

    /**
     * Set a value
     *  
     * @param   string  $key
     * @param   mixed   $value
     * @return  void
     */
    public function __set($key, $value)
    {
        $key = $this->key_filter($key);
        $this->scope[$key]   =   $value;
    }

    /**
     * Get a value
     * 
     * @param   string $key
     * @return  mixed
     */
    public function __get($key)
    {
        $key = $this->key_filter($key);

        if ( isset($this->scope[$key]) )
            return $this->scope[$key];
        else
            return null;
    }

    /**
     * Called when a script tries to call this object as a function
     * 
     * @param   string $key
     * @return  mixed
     */
    public function __invoke($key)
    {
        return $this->__get($key);
    }

    /**
     * Check whether a value exists
     * 
     * @param   string $key
     * @return  bool
     */
    public function __isset($key)
    {
        $key = $this->key_filter($key);
        return isset($this->scope[$key]);
    }

    /**
     * Remove a value
     * 
     * @param   string  $key
     * @return  void
     */
    public function __unset($key)
    {
        $key = $this->key_filter($key);
        unset($this->scope[$key]);
    }

    /**
     * Call a value as a method
     * 
     * @param   string  $name
     * @param   array   $arguments
     * @return  mixed
     */
    public function __call($name, $arguments)
    {
        $name = $this->key_filter($name);
        return is_callable($this->scope[$name]) ? call_user_func_array($this->scope[$name], $arguments) : false;
    }

    /**
     * Get a value exists
     * 
     * @param   string $key
     * @return  mixed
     */
    public function offsetGet($key)
    {
        $key = $this->key_filter($key);
        return $this->__get($key);
    }

    /**
     * Set a value
     * 
     * @param   string  $key
     * @param   mixed   $value
     * @return  void
     */
    public function offsetSet($key, $value)
    {
        $key = $this->key_filter($key);
        $this->__set($key, $value);
    }

    /**
     * Check whether a value exists
     * 
     * @param   string $key
     * @return  bool
     */
    public function offsetExists($key)
    {
        $key = $this->key_filter($key);
        return $this->__isset($key);
    }

    /**
     * Remove a value
     * 
     * @param   string  $key
     * @return  void
     */
    public function offsetUnset($key)
    {
        $key = $this->key_filter($key);
        $this->__unset($key);
    }

    /**
     * Return an iterator for the items array
     * 
     * @return  ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->scope);
    }

    /**
     * Set a value
     * 
     * @param   string|array    $key      string or array of key value pairs for multi-values set
     * @param   mixed           $value    leave null if you will set multi-values
     * @return  Horus_Container
     */
    public function set($key, $value = null)
    {
        if ( is_array($key) ) {
            foreach ( $key as $k => $v )
                $this->offsetSet($k, $v);
            return $this;
        }

        $key = $this->key_filter($key);
        $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * Get a value
     * 
     * @param   string $key
     * @return  mixed
     */
    public function get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Check whether a value exists
     * 
     * @param   string $key
     * @return  bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Remove a value
     * 
     * @param   string $key
     * @return  Horus_Container
     */
    public function del($key)
    {
        $this->offsetUnset($key);
        return $this;
    }

    /**
     * Set a value to true
     * 
     * @param   string $key
     * @return  Horus_Container
     */
    public function enable($key)
    {
        return $this->set($key, true);
    }

    /**
     * Set a value to false
     * 
     * @param   string $key
     * @return  Horus_Container
     */
    public function disable($key)
    {
        return $this->set($key, false);
    }

    /**
     * Check whether a value is true
     * 
     * @param   string $key
     * @return  bool
     */
    public function enabled($key)
    {
        return $this->get($key) === true;
    }

    /**
     * Check whether a value is false
     * 
     * @param   string $key
     * @return  bool
     */
    public function disabled($key)
    {
        return $this->get($key) == false;
    }

    /**
     * Push one or more elements onto the end of array
     * 
     * @param   string  $key
     * @param   mixed   $value
     * @return  Horus_Container
     */
    public function push($key, $value)
    {
        $key = $this->key_filter($key);
        if ( !isset($this[$key]) )
            $this[$key] = array();
        call_user_func_array('array_push', array_merge( array(&$this->scope[$key]), (is_array($value) ? $value : array_slice(func_get_args(), 1)) ));
        return $this;
    }

    /**
     * Pop the element off the end of array
     * 
     * @param   string $key
     * @return  Horus_Container
     */
    public function pop($key)
    {
        $key = $this->key_filter($key);
        if ( !isset($this[$key]) )
            $this[$key] = array();
        array_pop($this->scope[$key]);
        return $this;
    }

    /**
     * Shift an element off the beginning of array
     * 
     * @param   string $key
     * @return  Horus_Container
     */
    public function shift($key)
    {
        $key = $this->key_filter($key);
        if ( !isset($this[$key]) )
            $this[$key] = array();
        array_shift($this->scope[$key]);
        return $this;
    }

    /**
     * Prepend one or more elements to the beginning of an array
     * 
     * @param   string  $key
     * @param   mixed   $value
     * @return  Horus_Container
     */
    public function unshift($key, $value)
    {
        $key = $this->key_filter($key);
        if ( !isset($this[$key]) )
            $this[$key] = array();
        call_user_func_array('array_unshift', array_merge( array(&$this->scope[$key]), (is_array($value) ? $value : array_slice(func_get_args(), 1)) ));
        return $this;
    }

    /**
     * Set a value in a list
     * 
     * @param   string  $root   the main list
     * @param   string  $key    the key to set in the list, may be an array for multi-set
     * @param   mixed   $value  the value of the key in the list, leave null if you will multi-set
     * @return  Horus_Container
     */
    public function lset($root, $key, $value = null)
    {
        if ( is_array($key) ) {
            foreach ( $key as $k => $v )
                $this->lset($root, $k, $v);
            return $this;
        }

        $root = $this->key_filter($root);
        $key  = $this->key_filter($key);
        $this->scope[$root][$key] = $value;
        return $this;
    }

    /**
     * Get a value from a list
     * 
     * @param   string $root    the list key
     * @param   string $key     the sub key
     * @return  mixed
     */
    public function lget($root, $key)
    {
        $root = $this->key_filter($root);
        $key  = $this->key_filter($key);
        return isset($this->scope[$root][$key]) ? $this->scope[$root][$key] : null;
    }

    /**
     * Check whether a value exists in a list
     * 
     * @param   string $root    the list key
     * @param   string $key     the sub key
     * @return  bool
     */
    public function lhas($root, $key)
    {
        $root = $this->key_filter($root);
        $key  = $this->key_filter($key);
        return isset($this->scope[$root][$key]);
    }

    /**
     * Remove a value from a list
     * 
     * @param   string $root    the list key
     * @param   string $key     the sub key
     * @return  Horus_Container
     */
    public function ldel($root, $key)
    {
        $root = $this->key_filter($root);
        $key  = $this->key_filter($key);
        unset($this->scope[$root][$key]);
        return $this;
    }

    /**
     * Set value in a list to true
     * 
     * @param   string $root    the list key
     * @param   string $key     the sub key
     * @return  Horus_Container
     */
    public function lenable($root, $key)
    {
        return $this->lset($root, $key, true);
    }

    /**
     * Set a value in a list to false
     * 
     * @param   string $root    the list key
     * @param   string $key     the sub key
     * @return  Horus_Container
     */
    public function ldisable($root, $key)
    {
        return $this->lset($root, $key, false);
    }

    /**
     * Check whether a value in a list is true
     * 
     * @param   string $root    the list key
     * @param   string $key     the sub key
     * @return  bool
     */
    public function lenabled($root, $key)
    {
        return $this->lget($root, $key) === true;
    }

    /**
     * Check whether a value in alist is false
     * 
     * @param   string $root    the list key
     * @param   string $key     the sub key
     * @return  bool
     */
    public function ldisabled($root, $key)
    {
        return $this->lget($root, $key) == false;
    }

    /**
     * Import an array and optinally replace/merge with the current array
     * 
     * @param   array   $scope      the array to import
     * @param   bool    $replace    replace with the current array [true], or just merge with it [false]
     * @return  Horus_Container
     */
    public function import(array $scope = array(), $replace = false)
    {
        if ( $replace )
            $this->scope = $scope;
        else
            $this->scope = array_merge($this->scope, $scope);

        return $this;
    }

    /**
     * Export the current items as array
     * 
     * @return  array
     */
    public function export()
    {
        return $this->scope;
    }

    /**
     * The filter to be used while filtering the keys
     * 
     * @param   string $key the key to be filtered
     * @return  string
     */
    public function key_filter($key)
    {
        $key = str_replace(array('-', '.', ' '), '_', $key);

        if ( is_callable($this->key_filter) )
            $key = call_user_func($this->key_filter, $key);

        return $key;
    }
}

// -------------------------------

/**
 * Horus_Environment
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    9.0.0
 */
Class Horus_Environment extends Horus_Container
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->scope            =   &$_SERVER;
        $this->key_filter       =   function($v){ return strtoupper($v); };

        $this->fix();
    }

    /**
     * Fix some environment variables
     * 
     * @return void
     */
    protected function fix()
    {
        // Create a short cut to _SERVER array
        $s  =   &$this->scope;

        // Make sure that we have '/' at the start of SCRIPT_NAME and REQUEST_URI
        $s['REQUEST_URI']   =   '/' . ltrim($s['REQUEST_URI'], '/');
        $s['SCRIPT_NAME']   =   '/' . ltrim($s['SCRIPT_NAME'], '/');

        // Fix the PATH_INFO
        if ( stripos($s['REQUEST_URI'], $s['SCRIPT_NAME']) === 0 )
            $s['PATH_INFO'] =   substr($s['REQUEST_URI'], strlen($s['SCRIPT_NAME']));
        elseif ( stripos($s['REQUEST_URI'], dirname($s['SCRIPT_NAME'])) === 0 )
            $s['PATH_INFO'] =   substr($s['REQUEST_URI'], strlen(dirname($s['SCRIPT_NAME'])));

        // Extract only thepath from PATH_INFO
        $s['PATH_INFO']     =   ltrim(rtrim(parse_url($s['PATH_INFO'], PHP_URL_PATH), '/'), '/');
        $s['PATH_INFO']     =   preg_replace('/\/+/', '/', '/' . $s['PATH_INFO'] . '/');

        // Fix the HTTP_HOST
        $s['HTTP_HOST']     =   (strpos($s['HTTP_HOST'], ':') !== false) ? parse_url($s['HTTP_HOST'], PHP_URL_HOST) : $s['HTTP_HOST'];

        // Fix the SERVER_NAME
        $s['SERVER_NAME']   =   empty($s['SERVER_NAME']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];
        $s['SERVER_NAME']   =   (strpos($s['SERVER_NAME'], ':') !== false) ? parse_url($s['SERVER_NAME'], PHP_URL_HOST) : $s['SERVER_NAME'];

        // The default scheme
        $s['REQUEST_SCHEME']=   (isset($s['HTTPS']) && (strtolower($s['HTTPS']) !== 'off')) ? 'https' : 'http';

        // Virtual sub-domains
        $s['HORUS_SUBS']    =   array( 'www' );

        // Use self url rewriting or not
        $s['HORUS_REWRITE'] =   !isset($s['HORUS_REWRITE']) ? 1 : (int) $s['HORUS_REWRITE'];

        // Self url rewriting has been used or not
        $s['HORUS_REWRITED']=   (stripos($s['REQUEST_URI'], $s['SCRIPT_NAME'] . '/') === 0);

        // Internal url formats
        $s['HORUS_URLS']    =   array();

        // Set the haystack
        $s['HORUS_HAYSTACK']=   '//' . preg_replace('/^('.(join('|', (array) $s['HORUS_SUBS'])).')\./i', '', $s['HTTP_HOST']) . $s['PATH_INFO'];
    }


    /**
     * Return a formatted string
     * 
     * @param   string  $format
     * @param   [mixed   $args]
     * @param   [mixed   ...]
     * @return  string
     */
    public function url($format = null, $args = null)
    {
        $env = $this;

        if ( empty($env->horus_urls) )
        {
            $env->set('horus_urls', array
            (
                '%scheme'   =>  $env->request_scheme,
                '%host'     =>  $env->server_name,
                '%vpath'    =>  $env->horus_rewrite ? $env->script_name . '/' : dirname($env->script_name),
                '%rpath'    =>  dirname($env->script_name) . '/',
                '%cpath'    =>  $env->path_info,
                '%query'    =>  '?' . empty($env->query_string) ? null : sprintf('&%s', $env->query_string),
                '%rurl'     =>  '%scheme://%host/%rpath/',
                '%vurl'     =>  '%scheme://%host/%vpath/',
                '%curl'     =>  '%scheme://%host/%vpath/%cpath'
            ));
        }

        if ( empty($format) )
            return $this->get('horus_urls');

        for ( $i = 0; $i < 3; ++ $i )
            $format =   str_ireplace( array_keys($env->horus_urls), array_values($env->horus_urls), $format );

        if ( parse_url($format, PHP_URL_SCHEME) )
        {
            list( $scheme, $url ) = (array) explode( '://', $format, 2 );
            $format = sprintf( "%s://%s", $scheme, preg_replace( '/\/+/', '/', $url ) );
        }
        else
            $format = preg_replace('/\/+/', '/', $format);

        return vsprintf($format, (is_array($args) ? $args : array_slice(func_get_args(), 1)));
    }
}

// -------------------------------

/**
 * Horus_Router
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    1.0.0
 * 
 * @property  array  $regex
 * @property  string $base
 * @property  bool   $wait
 * @property  bool   $found
 */
Class Horus_Router
{
    /**
     * Array of REGEX Aliases
     * @var array
     */
    public   $regex  =   array
    (
        '%int'      =>  '([0-9\.,]+)',
        '%alpha'    =>  '([a-zA-Z]+)',
        '%alnum'    =>  '([a-zA-Z0-9\.\w]+)',
        '%str'      =>  '([a-zA-Z0-9-_\.\w]+)',
        '%any'      =>  '([^\/]+)',
        '%*'        =>  '?(.*)',
        '%date'     =>  '(([0-9]+)\/([0-9]{2,2}+)\/([0-9]{2,2}+))'
    );

    /**
     * Base of all routes patterns
     * @var string
     */
    protected   $base = '';

    /**
     * Tell the router to wait for next route or end the response
     * @var bool
     */
    protected   $wait = false;

    /**
     * Whether the requested page found or not
     * @var callable
     */
    protected $found    =   false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $env    =   Horus::instance()->env;
        $res    =   Horus::instance()->res;

        $this->regex['%domain']  =   preg_replace('/^('.(join('|', (array) $env['HORUS_SUBS'])).')\./i', '', $env['SERVER_NAME']);

        if ( $env->horus_rewrite && !$env->horus_rewrited )
            $res->redirect($env->url('%vurl'));
    }

    /**
     * Get the valid router pattern
     * 
     * @param   string $pattern the pattern to use
     * @param   string $escape  chars to escape
     * @return  string
     */
    public function pattern($pattern, $escape = null)
    {
        $pattern    =   stripslashes($pattern);

        if ( strpos($this->base, '//') !== 0 )
            $this->base = '//%domain';

        if ( strpos($pattern, '//') !== 0 )
            $pattern = $this->base . ('/' . ltrim(rtrim($pattern, '/'), '/') . '/');

        $pattern    =   '/' . preg_replace('/\/+/', '/', $pattern . '/');
        $pattern    =   str_ireplace(array_keys($this->regex), array_values($this->regex), $pattern);

        return addcslashes($pattern, $escape);
    }

    /**
     * Check whether a pattern matches current haystack and request-method or not
     * 
     * @param   string  $pattern    pattern to use
     * @param   bool    $strict     strict matching or not [^..$/^..]
     * @param   string  $method     the method to use
     * @return  false
     */
    public function is($pattern, $strict = true, $method = 'auto')
    {
        $method     =   str_replace('_', '|', strtolower($method));
        $method     =   in_array($method, array('group', 'auto')) ? $_SERVER['REQUEST_METHOD'] : strtoupper($method);
        $pattern    =   (('^') . $this->pattern($pattern, '/') . ($strict ? '$' : ''));

        $is         =   preg_match("/{$pattern}/", $_SERVER['HORUS_HAYSTACK'], $matches);
        $is         =   $is && preg_match("/({$method})/i", $_SERVER['REQUEST_METHOD']);

        array_shift($matches);

        return $is ? $matches : false;
    }

    /**
     * Re-route a  route to another route
     * 
     * @param   string  $old        the old pattern
     * @param   string  $new        the new pattern
     * @param   bool    $strict     strict matching or not
     * @return  Horus_Router
     */
    public function re(array $old, array $new, $strict = true)
    {
        $old    =   array_merge(array
        (
            'pattern'   =>  '/',
            'method'    =>  'GET'
        ), $old);

        $new    =   array_merge(array
        (
            'pattern'   =>  '/',
            'method'    =>  'GET'
        ), $new);

        if ( ($args = $this->is($old['pattern'], $strict, $old['method'])) !== false )
        {
            $env = Horus::instance()->env;

            $env['horus_haystack']      =   $this->pattern($new['pattern'] . '/' . join('/', array_values($args)));

            if ( ! in_array(strtolower($new['method']), array('auto')) )
                $env['request_method']  =   strtoupper($new['method']);
        }

        return $this;
    }

    /**
     * Check/Set the router status
     * 
     * @param   bool $found     found a route or not
     * @return  bool
     */
    public function found($found = null)
    {
        if ( is_null($found) )
            return $this->found;

        $this->found = (bool) $found;

        return $this->found;
    }

    /**
     * Check/Set waiting status
     * 
     * @param   bool    $wait   wait or not
     * @return  bool
     */
    public function wait($wait = null)
    {
        if ( is_null($wait) )
            return $this->wait;

        $this->wait = (bool) $wait;

        return $this->wait;
    }

    /**
     * Proxy to Horus_Router::handle()
     * 
     * @param   string  $name   method of the route
     * @param   array   $args   other arguments
     * @return  mixed   Horus_Router
     */
    public function __call($name, $args)
    {
        array_unshift($args, $name);
        return call_user_func_array(array($this, 'handle'), $args);
    }

    /**
     * Handle a route
     * 
     * @param   string      $method     the method of the route
     * @param   string      $pattern    the pattern of the route
     * @param   callable    $callback   the callback of the route
     * @param   bool        $strict     strict matching or not
     * @return  Horus_Router
     */
    protected function handle($method, $pattern, $callback, $strict = true)
    {
        $method = strtolower($method);

        // Multiple patterns ?
        if ( is_array($pattern) ) 
        {
            foreach ( $pattern as $p )
                $this->handle($method, $p, $callback, $strict);
            return $this;
        }

        $pattern = $this->pattern($pattern);


        if ( ($args = $this->is($pattern, ($method == 'group' ? false : $strict), $method)) !== false )
        {
            if ( $method == 'group' )
            {
                $old        = $this->base;
                $this->base = $pattern;
            }

            ob_start();

            call_user_func_array($callback, $args);

            Horus::instance()->res->send(ob_get_clean());

            if ( $method == 'group' )
                $this->base = $old;
            else
            {
                $this->found(1);
                Horus::run();
            }
        }

        return $this;
    }
}

// -------------------------------

/**
 * Horus_Request
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    9.0.0
 * 
 * @propery  array  $regex
 * @propery  string $base
 * @propery  bool   $wait
 */
Class Horus_Request
{
    /**
     * Gets a header field
     * 
     * @param   string $field
     * @return  string
     */
    public function get($field)
    {
        $field = str_replace(' ', '_', strtoupper(str_replace(array('_', '.', '-'), ' ', trim($field))));

        if ( isset($_SERVER["HTTP_{$field}"]) )
            return $_SERVER["HTTP_{$field}"];

        return '';
    }

    /**
     * Checks whether a header field exists
     * 
     * @param   string $field
     * @return  bool
     */
    public function has($field)
    {
        return (bool) $this->get($field);
    }

    /**
     * Set/Get request method
     * 
     * @param   string  $method     the method to set for the current request
     * @return  string|self
     */
    public function method($method = null)
    {
        if ( empty($method) )
            return $_SERVER['REQUEST_METHOD'];
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        return $this;
    }

    /**
     * Check whether the request made with 'X-Requested-With' "Ajax ... etc" or not
     * @return  bool
     */
    public function xhr()
    {
        return 
        (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == strtolower('XMLHttpRequest')
        );
    }

    /**
     * Check whether the request under HTTPS or not
     * @return  bool
     */
    public function secure()
    {
        return 
        (
            (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')    ||
            ($_SERVER['SERVER_PORT'] == 443)
        );
    }

    /**
     * Return a value from request query _GET
     * 
     * @param   string      $field      the field to get
     * @param   string      $default    the default field value
     * @param   callbacks   $filter     callback(s) to filter the field's value
     * @return  mixed
     */
    public function query($field = null, $default = null, $filter = null)
    {
        return $this->input($_GET, $field, $default, $filter);
    }

    /**
     * Return a value from request body [post, put, head, .. etc]
     * 
     * @param   string      $field      the field to get
     * @param   string      $default    the default field value
     * @param   callback    $filter     callback(s) to filter the field's value
     * @return  mixed
     */
    public function body($field = null, $default = null, $filter = null)
    {
        static $body = array();

        if ( empty($body) )
        {
            if ( $this->method() !== 'POST' )
                $body = $_POST;
            else
            {
                $body = file_get_contents('php://input', false);
    
                if ( is_array($x = json_decode($body, true)) )
                    $body = &$x;
                elseif ( (bool) ($x = @simplexml_load_string($body)) )
                    $body = json_decode(json_encode($x), true);
                else
                    parse_str($body, $body);
            }
        }

        return $this->input($body, $field, $default, $filter);
    }

    /**
     * Return value from the session
     * 
     * @param   string      $field      the field to get
     * @param   string      $default    the default field value
     * @return  mixed
     */
    public function session($field = null, $default = null)
    {
        return $this->input($_SESSION, $field, $default);
    }

    /**
     * Return a value from a cookie
     * 
     * @param   string      $field      the field to get
     * @param   string      $default    the default field value
     * @return  mixed
     */
    public function cookie($field = null, $default = null)
    {
        return $this->input($_COOKIE, $field, $default);
    }

    /**
     * Return a value from an input array
     * 
     * @param   array       $source     the input array
     * @param   string      $field      the field to get
     * @param   string      $default    the default field value
     * @param   callback    $filter     callback(s) to filter the field's value
     * @return  mixed
     */
    protected function &input(array $source, $k = null, $default = null, $filter = null)
    {
        if ( is_null($k) || $k === '' )
            return $source;

        $k      =   isset($source[$k]) ? $source[$k] : $default;
        $filter =   is_array($filter) ? $filter : (array) $filter;        

        foreach ( $filter as &$f )
            if (is_callable($f))
                $k = $f($k);

        return $k;
    }
}

// -------------------------------

/**
 * Horus_Response
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    9.0.0
 * 
 * @propery  array      $headers
 * @propery  string     $body
 * @propery  string     $type
 * @propery  string     $charset
 * @propery  integer    $status
 * @propery  bool       $headers_sent
 */
Class Horus_Response
{
    /**
     * Response headers storage
     * @var array
     */
    protected  $headers    =   array();

    /**
     * Response body
     * @var string
     */
    protected   $body       =   '';

    /**
     * Response content type
     * @var string
     */
    protected   $type       =   'text/html';

    /**
     * Response content charset
     * @var string
     */
    protected   $charset    =   'UTF-8';

    /**
     * Response header status
     * @var integer
     */
    protected   $status     =   200;

    /**
     * Whether the headers have been sent or not
     * @var bool
     */
    protected   $headers_sent   =   false;

    /**
     * Constructor
     * @return  self
     */
    public function __construct()
    {
        ob_start();
    }

    /**
     * Set the status code
     * 
     * @param   integer $code
     * @return  Horus_Response
     */
    public function status($code = null)
    {
        if ( empty($code) )
            return $this->status;
        $this->status = (int) $code;
        return $this;
    }

    /**
     * Set header field(s)
     * 
     * @param   string  $field
     * @param   string  $value
     * @return  self
     */
    public function set($field, $value = null)
    {
        if ( is_array($field) )
        {
            foreach ( $field as $k => $v )
                $this->set($k, $v);
            return $this;
        }

        $field = str_replace(' ', '-', ucwords(str_replace(array('_', '.', '-'), ' ', trim($field))));
        $this->headers[$field] = $value;
        return $this;
    }

    /**
     * Get a header field
     * 
     * @param   string  $field
     * @return  string
     */
    public function get($field)
    {
        $field = str_replace(' ', '-', ucwords(str_replace(array('_', '.', '-'), ' ', trim($field))));
        return isset($this->headers[$field]) ? $this->headers[$field] : '';
    }

    /**
     * Whether a response header field exists or not
     * 
     * @param   string $field
     * @return  bool
     */
    public function has($field)
    {
        return (bool) $this->get($field);
    }

    /**
     * Remove a header field
     * 
     * @return  Horus_Response
     */
    public function del()
    {
        $field = str_replace(' ', '-', ucwords(str_replace(array('_', '.', '-'), ' ', trim($field))));
        unset($this->headers[$field]);
        return $this;
    }

    /**
     * Set the content type
     * 
     * @param   string $type
     * @return  string|Horus_Response
     */
    public function type($type = null)
    {
        if ( empty($type) )
            return $this->type;
        $this->type = $type;
        return $this;
    }

    /**
     * Set the charset
     * 
     * @param   string $charset
     * @return  string|Horus_Response
     */
    public function charset($charset = null)
    {
        if ( empty($charset) )
            return $this->charset;
        $this->charset = $charset;
        return $this;
    }

    /**
     * Cache a webpage using HTTP-CACHE
     * 
     * @param   integer $ttl    the time to live used in cache
     * @return  Horus_Response
     */
    public function cache($ttl)
    {
        $ttl = abs((int) $ttl);
        $req = Horus::instance()->req;

        if ( $ttl )
        {
            if ( ! $this->has('cache control') )
                $this->set('cache control', sprintf('private, max-age=%s, post-check=0, pre-check=0', $ttl));

            if ( ! $this->has('pragma') )
                $this->set('pragma', 'cache');

            $since  =   (int) strtotime($req->get('if modified since'));

            if ( (time() - $since) < $ttl )
                $this->status(304)->end();
            else
                $this->set('last modified', gmdate('D, d M Y H:i:s ', time()) . 'GMT');
        }

        return $this;
    }

    /**
     * Clean the previously sent body
     * 
     * @return  Horus_Response
     */
    public function clean()
    {
        $this->body = '';

        return $this;
    }

    /**
     * Send a message to the body
     * 
     * @param   string  $message
     * @return  Horus_Response
     */
    public function send($message)
    {
        if ( is_array($message) || is_object($message) )
        {
            $this->type('application/json');
            $message = json_encode($message);
        }
        else
            $message = implode('', func_get_args());

        $this->body    .=   $message;

        return $this;
    }

    /**
     * Capture an output from a callback
     * 
     * @param   callable $callback
     * @return  Horus_Response
     */
    public function capture($callback)
    {
        if ( is_callable($callback) ) {
            ob_start();
            call_user_func($callback);
            $this->send(ob_get_clean());
        }

        return $this;
    }

    /**
     * Render some files
     * 
     * @param   string  $filename
     * @param   array   $args
     * @return  Horus_Response
     */
    public function render($filename, array $args = array())
    {
        ob_start();

        extract($args, EXTR_OVERWRITE|EXTR_REFS);

        foreach ( (array) $filename as $file )
            require($file);

        return $this->send(ob_get_clean());
    }

    /**
     * Send a cookie
     * 
     * @param   string  $name       the name of the field
     * @param   string  $value      the value of the cookie field
     * @param   array   $options    the cookie options
     * @return  Horus_Response
     */
    public function cookie($name, $value, array $options = array())
    {
        $options = array_merge(array
        (
            'domain'    =>  '',
            'path'      =>  '/',
            'secure'    =>  Horus::instance()->req->secure(),
            'expire'    =>  0,
            'httpOnly'  =>  true
        ), $options);

        setcookie( $name, $value, $options['expire'], $options['path'], $options['domain'],(bool) $options['secure'], (bool) $options['httpOnly'] );

        return $this;
    }

    /**
     * Set session fields
     * 
     * @param   string|array    $key    string or array of fields
     * @param   mixed           $value
     * @return  self
     */
    public function session($field, $value = null)
    {
        if ( ! isset($_SESSION) )
            $_SESSION = array();

        if ( is_array($key) )
        {
            foreach ( $key as $k => $v )
                $_SESSION[$k] = $v;
            return $this;
        }

        $_SESSION[$key] = $value;

        return $this;
    }

    /**
     * Returns response headers
     * 
     * @return  array
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Returns response body
     * 
     * @return  string
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Redirect to another page
     * 
     * @param   string  $url
     * @param   integer $code
     * @return  void
     */
    public function redirect($url, $code = 302)
    {
        $this->clean()->set('location', $url)->status($this->status = $code)->end();
    }

    /**
     * End the response cycle
     * 
     * @return  void
     */
    public function end()
    {
        if ( ! $this->get('etag') )
            $this->set('etag', md5($this->body()));        
        
        if ( ! $this->headers_sent )
        {
            $this->set('content-type', sprintf('%s; charset=%s', $this->type(), $this->charset()));
            foreach ( $this->headers as $k => &$v )
                header(sprintf('%s: %s', $k, $v), true);
            header('X-HTTP-HORUS-STATUS: ' . $this->status, false, $this->status());
            $this->headers_sent = true;
        }

        if ( strtolower($_SERVER['REQUEST_METHOD']) !== 'head' ) 
            echo Horus::instance()->events->emit('horus.res.output', $this->body(), $this->body());

        ob_end_flush();

        exit;
    }
}

// -------------------------------

/**
 * Horus_Events
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    10.0.0
 * 
 * @propery  array      $events
 * @propery  integer    $maximum
 */
Class Horus_Events
{
    /**
     * Array of events storage
     * @var array
     */
    protected $events = array();

    /**
     * Maxmim listeners per event, zero means unlimited
     * @var integer
     */
    protected $maximum = 0;

    /**
     * Set/Get the maximum listeners number per event
     * 
     * @param   integer $maximum    null to get the current maximum number
     * @return  integer
     */
    public function maximum($maximum = null)
    {
        if ( is_null($maximum) )
            return $this->maximum;

        $this->maximum = abs((int) $maximum);
        return $this->maximum;
    }

    /**
     * Register a new event
     * 
     * @param   string      $event      the event name
     * @param   callable    $callable   the listener
     * @param   integer     $offset     the offset of the listener in the event array
     * @param   bool        $once       whether to use the listener once or multiple times
     * @return  Horus_Events
     */
    public function on($event, $callable, $offset = -1, $once = false)
    {
        if ( ! isset($this->events[$event]) )
            $this->events[$event] = array();

        if ( ($this->maximum() > 0) && ($this->maximum() < (sizeof($this->events[$event]) + 1))  )
            return $this;

        if ( ! is_callable($callable) )
            throw new Horus_Exception("The event {$event} in offset {$offset} has in-valid callable");

        $offset = ($offset === -1) ? sizeof($this->events[$event]) : $offset;

        $this->events[$event] = array_merge
        (
            array_slice($this->events[$event], 0, $offset),
            array( array($callable, (bool) $once) ),
            array_slice($this->events[$event], $offset)
        );

        return $this;
    }

    /**
     * Execute an event listeners
     * 
     * @param   string  $event          the event name
     * @param   mixed   $args           array of arguments to pass to the listeners
     * @param   mixed   $default_value  the default value to return and updated each listener executed then passed to the listeners arguments
     * @return  mixed
     */
    public function emit($event, $args = null, $default_value = null)
    {
        if ( empty($this->events[$event]) )
            return $default_value;
        elseif ( ! is_array($this->events[$event]) )
            return $default_value;
        else
        {
            foreach ( $this->events[$event] as $i => $c )
            {
                list($c, $once) = $c;

                $default_value = call_user_func_array($c, array_merge((array) $args, array($default_value)));

                if ( $once == true )
                    unset($this->events[$event][$i]);
            }
        }

        return $default_value;
    }

    /**
     * Remove event
     * 
     * @param   string      $event      the event to remove
     * @param   callable    $callable   the listener to remove
     * @return  Horus_Events
     */
    public function off($event, $callable = null)
    {
        if ( empty($this->events[$event]) ):
            return $this;
        elseif ( empty($callable) ):
            unset($this->events[$event]);
            return $this;
        else:
            foreach ( $this->events[$event] as $i => $clbk )
            {
                list($clbk, $once) = $clbk;
                if ( $callable === $clbk )
                    unset($this->events[$event][$i]);
            }
            return $this;
        endif;
    }

    /**
     * Returns registered events, [optional] of certain events group
     * 
     * @param   string  $of
     * @return  array
     */
    public function get($of = null)
    {
        if ( ! empty($of) )
            return isset($this->events[$of]) ? (array) $this->events[$of] : array();
        else
            return $this->events;
    }
}

// -------------------------------

/**
 * Horus_Util
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    9.0.0
 */
Class Horus_Util
{
    /**
     * Debug (show errors) or not ?
     * 
     * @param   bool $state
     * @return  integer
     */
    public function debug($state = true)
    {
        if($state) error_reporting(E_ALL);
        else error_reporting(0);

        return error_reporting();
    }

    /**
     * Encrypt using mcrypt
     * 
     * @param   mixed   $data
     * @param   string  $key
     * @param   string  $cipher
     * @param   string  $mode
     * @return  string
     */
    public function encrypt($data, $key, $cipher = MCRYPT_RIJNDAEL_128, $mode = MCRYPT_MODE_CBC)
    {
        $data       =   serialize($data);
        $key        =   hash('sha256', $key, true);
        $iv_size    =   mcrypt_get_iv_size($cipher, $mode);
        $iv         =   mcrypt_create_iv($iv_size, MCRYPT_RAND);

        return  base64_encode(serialize(array($iv, mcrypt_encrypt($cipher, $key, $data, $mode, $iv))));
    }

    /**
     * Decrypt using mcrypt
     * 
     * @param   mixed   $data
     * @param   string  $key
     * @param   string  $cipher
     * @param   string  $mode
     * @return  string
     */
    public function decrypt($data, $key, $cipher = MCRYPT_RIJNDAEL_128, $mode = MCRYPT_MODE_CBC)
    {
        $key    =   hash('sha256', $key, true);

        @ list($iv, $encrypted) = (array) unserialize(base64_decode($data));

        return unserialize(trim(mcrypt_decrypt($cipher, $key, $encrypted, $mode, $iv)));
    }

    /**
     * Hash string using crypt function
     * 
     * @param   string  $string     string to hash
     * @param   string  $algorithm  [blowfish, md5, sha256, sha512]
     * @return  string
     */
    public function hash($string, $algorithm = 'blowfish')
    {
        $rand = sha1( crypt(uniqid() . microtime(true) . mt_rand(1, 15), '$2a$07$'.(mt_rand(0, 100)).'$') );

        switch( strtolower($algorithm) ):
            case('md5'):
                $salt = '$1$'.(substr(str_shuffle($rand), 0, 12)).'$';
                break;
            case('sha256'):
                $salt = '$5$rounds=5000$'.(substr(str_shuffle($rand), 0, 16)).'$';
                break;
            case('sha512'):
                $salt = '$6$rounds=5000$'.(substr(str_shuffle($rand), 0, 16)).'$';
                break;
            case('blowfish'):
                $salt = '$2a$09$'.(substr(str_shuffle($rand), 0, 22)).'$';
                break;
        endswitch;

        return base64_encode(crypt($string, $salt));
    }

    /**
     * Checks whether a crypted hash is valid
     * 
     * @param   string $real    the real string
     * @param   string $hash    the hashed string
     * @return  bool
     */
    public function verify($real, $hash)
    {
        $hash   =   base64_decode($hash);
        return      crypt($real, $hash) == $hash;
    }

    /**
     * Generate a random string with a certain length
     * 
     * @param   integer $length
     * @return  string
     */
    public function random($length = 15)
    {
        // a-zA-Z0-9
        $chars  =   ($l = join('', range('a', 'z'))) . strtoupper($l) . join('', range(0, 9));

        // special chars
        $chars .=   '!<@#$%^&*_=+|?{}[]():;>';

        // cut the chars rabdomally from a certian offset
        // after shuffling it
        $return = substr(str_shuffle($chars), rand(0, strlen($chars) / 2), $length);

        $l = strlen($return);

        while( $l < $length )
        {
            //var_dump($length, $l, strlen($return));
            $return .=  substr(str_shuffle($chars), rand(0, strlen($chars) / 2), $length - $l);
            $l = strlen($return);
        }

        return $return;
    }

    /**
     * Proxy to hash() function
     * 
     * @return  string
     */
    public function __call($n, $a)
    {
        $args   =   array_merge(array(strtolower(str_replace('_', ',', $n))), $a);
        $a      =   null;
        return      call_user_func_array('hash', $args);
    }
}

// -------------------------------

/**
 * Horus_Loader
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    9.0.0
 * 
 * @property array  $log
 * @property array  $classes
 * @property array  $prefixes
 * @property array  $dirs
 */
Class Horus_Loader
{
    /**
     * Internal logs storage
     * @var array
     */
    protected $log = array();

    /**
     * Array of registered classes
     * @var array
     */
    protected $classes = array();

    /**
     * Array of registered prefixes
     * @var array
     */
    protected $prefixes = array();

    /**
     * Array of registered directories
     * @var array
     */
    protected $dirs = array();

    /**
     * Directory Separator alias
     * @const string
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Register our loader in spl-autoloads
     * 
     * @return Horus_Loader
     */
    public function register()
    {
        spl_autoload_register(array($this, 'load'));
        return $this;
    }

    /**
     * Unregister our loader
     * 
     * @return  Horus_Loader
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'load'));
        return $this;
    }

    /**
     * Prepare (Fix) a class/namespace name
     * 
     * @param   string $name
     * @return  string
     */
    public function prepare($name)
    {
        return rtrim(ltrim(str_replace(array('\\', '_', '/', '.', ' '), self::DS, $name), self::DS), self::DS);
    }

    /**
     * Return logs array
     * 
     * @return  array
     */
    public function logs()
    {
        return $this->log;
    }

    /**
     * Add a directory to directories map
     * 
     * @param   string          $dir
     * @param   string|array    $ext
     * @return  Horus_Loader
     */
    public function addDir($dir, $ext = 'php')
    {
        $ext = empty($ext) ? 'php' : $ext;
        $this->dirs[realpath($dir) . self::DS] = (array) $ext;
        return $this;
    }

    /**
     * Add directories to directories map
     * 
     * @param   array  $dirs
     * @return  Horus_Loader
     */
    public function addDirs(array $dirs)
    {
        foreach ( $dirs as $dir => &$exts )
            $this->addDir($dir, $exts);
        return $this;
    }

    /**
     * Add a class to classes map
     * 
     * @param   string $class_name
     * @param   string $class_path
     * @return  Horus_Loader
     */
    public function addClass($class_name, $class_path)
    {
        $this->classes[$this->prepare($class_name)] = $class_path;
        return $this;
    }

    /**
     * Add map of classes to classes map
     * 
     * @param   array $classes
     * @return  Horus_Loader
     */
    public function addClasses(array $classes)
    {
        foreach ( $classes as $class => &$path )
            $this->addClass($class, $path);
        return $this;
    }

    /**
     * Add a prefix with its paths & extensions
     * 
     * @param   string          $prefix
     * @param   string          $path
     * @param   string|array    $ext
     * @return  Horus_Loader
     */
    public function addPrefix($prefix, $path, $ext = 'php')
    {
        $ext = empty($ext) ? 'php' : $ext;
        $this->prefixes[$this->prepare($prefix) . self::DS] = array((array) $path, (array) $ext);
        return $this;
    }

    /**
     * Add prefxes to the prefixes map
     * 
     * @param   array $prefixes
     * @return  self
     */
    public function addPrefixes(array $prefixes)
    {
        foreach ( $prefixes as $prefix => &$info )
            $this->addPrefix($prefix, $info[0], @$info[1]);
        return $this;
    }

    /**
     * Find a class path
     * 
     * @param   string  $class_name
     * @return  string|false
     */
    public function find($class_name)
    {
        $class_name = $this->prepare($class_name);

        // a direct class [in class map] ?
        if ( isset($this->classes[$class_name]) )
            return $this->classes[$class_name];

        // a registered prefix is in it ?
        foreach ( $this->prefixes as $prefix => &$info )
        {
            if ( stripos($class_name, $prefix) === 0 )
            {
                list ( $paths, $exts ) = $info;
                foreach ( $paths as &$path ) foreach ( $exts as &$ext )
                {
                    $class_name = substr_replace($class_name, realpath($path) . self::DS, 0, strlen($prefix));

                    if ( is_file($file = $class_name . '.' . ltrim($ext, '.')) )
                        return $file;
                    elseif ( is_file($file = $class_name . self::DS . basename($class_name) . '.' . ltrim($ext, '.')) )
                        return $file;
                }
            }
        }

        // find it in any reigtsered directory 
        foreach ( $this->dirs as $dir => &$exts ) foreach ( $exts as &$ext )
        {
            if ( is_file($file = $dir . $class_name . '.' . ltrim($ext, '.')) )
                return $file;
            elseif ( is_file($file = $dir . $class_name . self::DS . basename($class_name) . '.' . ltrim($ext, '.')) )
                return $file;
        }

        // -_- not found
        return false;
    }

    /**
     * The callback to be registered in spl autoloads
     * 
     * @param   string $classname
     * @return  self
     */
    public function load($classname)
    {
        $x = false;

        if ( ($classname = $this->find($classname)) )
            $x = include $classname;

        $this->log[] = sprintf('Class "%s" is %s', $classname, ($x ? 'found' : 'not found'));

        return $this;
    }

    /**
     * Return the full autoloader maps
     * 
     * @return  array
     */
    public function maps()
    {
        return array (
            'classes'   =>  &$this->classes,
            'prefixes'  =>  &$this->prefixes,
            'dirs'      =>  &$this->dirs
        );
    }
}

// -------------------------------

/**
 * Horus
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    1.0.0
 * 
 * @property Horus  $instance
 */
Class Horus extends Horus_Container
{
    /**
     * Horus instance
     * @var Horus
     */
    protected static $instance;

    /**
     * @const string
     */
    const VERSION = '10.1';

    /**
     * Constructor
     * @param   callable an output buffer handler
     */
    public function __construct($ob_handler = null)
    {
        if ( self::constructed() )
            return $this;

        parent::__construct();

        @ ob_clean();

        is_callable($ob_handler) && ob_start($ob_handler);

        ini_set('session.cookie_httponly',          1);
        ini_set('session.use_only_cookies',         1);
        ini_set('session.name',             'HPHPSESS');

        $this->env      =   new Horus_Environment;
        $this->events   =   new Horus_Events;
        $this->res      =   new Horus_Response;
        $this->req      =   new Horus_Request;
        $this->util     =   new Horus_Util;
        $this->loader   =   new Horus_Loader;

        define('HORUS_INIT_TIME',   microtime(true));
        define('HORUS_INIT_MEM',    memory_get_usage(true));
        define('HORUS_INIT_PMEM',   memory_get_peak_usage(true));
        define('DS',                DIRECTORY_SEPARATOR);
        define('COREPATH',          dirname(__FILE__) . DS);
        define('BASEPATH',          str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME'])) . DS);

        $this->e404                 =   create_function('', 'return "<h1>404 page not found</h1><p>the requested resource not found</p>";');

        $this->res->type('text/html');
        $this->res->set(array
        (
            'x-powered-by'              =>  'HPHP/'.self::VERSION,
            'x-frame-options'           =>  'SAMEORIGIN',
            'X-XSS-Protection'          =>  '1; mode=block',
            'X-Content-Type-Options'    =>  'nosniff'
        ));

        self::$instance = $this;
    }

    /**
     * Return the class instance
     * 
     * @return  Horus
     */
    public static function instance()
    {
        return self::$instance;
    }

    /**
     * Check whether Horus has been started or not
     * 
     * @return  bool
     */
    public static function constructed()
    {
        return self::$instance instanceof Horus;
    }

    /**
     * Get a value
     * 
     * used for lazy initializing of Horus_Router
     * 
     * @return  mixed
     */
    public function __get($k)
    {
        if ( strtolower($k) == 'router' )
        {
            if ( ! isset($this->scope['router']) )
                $this->scope['router'] = new Horus_Router;
            elseif ( ! $this->scope['router'] instanceof Horus_Router )
                $this->scope['router'] = new Horus_Router;
        }

        return parent::__get($k);
    }

    /**
     * Run the application
     * 
     * @return void
     */
    public static function run()
    {
        $self = self::instance();

        if ( isset($self->scope['router']) && $self->scope['router'] instanceof Horus_Router && ! $self->router->found() )
            $self->res->status(404)->clean()->send($self->e404());

        $self->res->end();
    }
}

// -------------------------------

/**
 * Construct & return horus instance
 * 
 * @param   callable    $ob_handler
 * @return  Horus
 */
function Horus($ob_handler = null)
{
    if ( ! Horus::constructed() )
        new Horus($ob_handler);

    return Horus::instance();
}

// -------------------------------

/**
 * Container
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    10.0.0
 */
Class Container extends Horus_Facade
{
    /**
     * Setting the targetted object
     * @return  object
     */
    public function target()
    {
        static $i = null;

        if ( empty($i) )
            $i = new Horus_Container;

        return $i;
    }
}

// -------------------------------

/**
 * Env
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    10.0.0
 */
Class Env extends Horus_Facade
{
    /**
     * Setting the targetted object
     * @return  object
     */
    public function target()
    {
        return Horus::instance()->env;
    }
}

// -------------------------------

/**
 * Req
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    10.0.0
 */
Class Req extends Horus_Facade
{
    /**
     * Setting the targetted object
     * @return  object
     */
    public function target()
    {
        return Horus::instance()->req;
    }
}

// -------------------------------

/**
 * Res
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    10.0.0
 */
Class Res extends Horus_Facade
{
    /**
     * Setting the targetted object
     * @return  object
     */
    public function target()
    {
        return Horus::instance()->res;
    }
}

// -------------------------------

/**
 * Router
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    10.0.0
 */
Class Router extends Horus_Facade
{
    /**
     * Setting the targetted object
     * @return  object
     */
    public function target()
    {
        return Horus::instance()->router;
    }
}

// -------------------------------

/**
 * Loader
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    10.0.0
 */
Class Loader extends Horus_Facade
{
    /**
     * Setting the targetted object
     * @return  object
     */
    public function target()
    {
        return Horus::instance()->loader;
    }
}

// -------------------------------

/**
 * Util
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    10.0.0
 */
Class Util extends Horus_Facade
{
    /**
     * Setting the targetted object
     * @return  object
     */
    public function target()
    {
        return Horus::instance()->util;
    }
}
