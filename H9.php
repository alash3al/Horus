<?php @ ob_clean(); (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) && die('<h1>Direct access not allowed');
/**
 * Horus PHP Framework
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @version     9.4.1
 * @license     MIT
 * @copyright   2014 (c) HPHP Framework
 */

// -------------------------------

/**
 * Horus Container
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       5.1.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_Container implements ArrayAccess, Countable, IteratorAggregate, Serializable
{
    /** @ignore */
    public $data = array(), $key_filter = null;

    /**
     * Construct
     * @param   array $data
     * @param   bool  $ref
     * @return  self
     */
    public function __construct(array $data = array(), $ref = true)
    {
        if ( $ref )
            $this->data = &$data;
        else
            $this->data = $data;
    }

    /**
     * Export as an array
     * @return  array
     */
    public function export()
    {
        return $this->data;
    }

    /**
     * Import an array
     * @param   array   $data
     * @param   bool    $ref
     * @return  self
     */
    public function import(array $data, $ref = true)
    {
        if ( $ref )
            $this->data = &$data;
        else
            $this->data = $data;
        return $this;
    }

    /**
     * Returns the $var value again, better for chaining .
     * @param   mixed $var
     * @return  mixed
     */
    public function with($var)
    {
        return $var;
    }

    /**
     * Set a key's value
     * @param   mixed   $k
     * @param   mixed   $v
     * @return  self
     */
    public function set($k, $v = null)
    {
        if ( is_array($k) )
        {
            foreach ( $k as $x => &$y )
                $this->set($x, $y);
            return $this;
        }

        $this->__set($k, $v);
        return $this;
    }

    /**
     * Checks whether the container has a key or not
     * @param   string  $k
     * @return  bool
     */
    public function has($k)
    {
        $k = $this->_key($k);
        return isset($this->{$k});
    }

    /**
     * Get a key's value
     * @param   string  $k
     * @return  mixed
     */
    public function get($k)
    {
        return $this->__get($k);
    }

    /**
     * Get a key's value
     * @param   string  $k
     * @return  mixed
     */
    public function del($k)
    {
        $k = $this->_key($k);
        unset($this->{$k});
        return $this;
    }

    /**
     * Push one or more elements onto the end of array
     * @param   string  $key
     * @param   mixed   $value
     * @return  self
     */
    public function push($key, $value)
    {
        if ( ! isset($this->data[$key]) )
            $this->data[$key] = array();

        foreach ( array_slice(func_get_args(), 1) as $v )
            array_push($this->data[$key], $v);

        return $this;
    }

    /**
     * Pop elements off the end of array
     * @param   string $key
     * @return  self
     */
    public function pop($key)
    {
        if ( ! isset($this->data[$key]) )
            $this->data[$key] = array();

        array_pop($this->data[$key]);

        return $this;
    }

    /**
     * Shift an element of the beginning
     * @param   string $key
     * @return  self
     */
    public function shift($key)
    {
        if ( ! isset($this->data[$key]) )
            $this->data[$key] = array();

        array_shift($this->data[$key]);

        return $this;
    }

    /**
     * Prepend one or more elements to the beginning of array
     * @param   string  $key
     * @param   mixed   $value
     * @return  self
     */
    public function unshift($key, $value)
    {
        if ( ! isset($this->data[$key]) )
            $this->data[$key] = array();

        foreach ( array_slice(func_get_args(), 1) as $v )
            array_unshift($this->data[$key], $v);

        return $this;
    }

    /**
     * Push one or more elements onto the end of list
     * @param   string  $root     
     * @param   string  $key
     * @param   mixed   $value
     * @return  self
     */
    public function lpush($root, $key, $value)
    {
        if ( ! isset($this->data[$root][$key]) )
            $this->data[$root][$key] = array();

        foreach ( array_slice(func_get_args(), 2) as $v )
            array_push($this->data[$root][$key], $v);

        return $this;
    }

    /**
     * Pop elements off the end of list
     * @param   string  $root
     * @param   string  $key
     * @return  self
     */
    public function lpop($root, $key)
    {
        if ( ! isset($this->data[$root][$key]) )
            $this->data[$root][$key] = array();

        array_pop($this->data[$root][$key]);

        return $this;
    }

    /**
     * Shift an element of the beginning of list
     * @param   string $root
     * @param   string $key
     * @return  self
     */
    public function lshift($root, $key)
    {
        if ( ! isset($this->data[$root][$key]) )
            $this->data[$root][$key] = array();

        array_shift($this->data[$root][$key]);

        return $this;
    }

    /**
     * Prepend one or more elements to the beginning of list
     * @param   string  $root
     * @param   string  $key
     * @param   mixed   $value
     * @return  self
     */
    public function lunshift($root, $key, $value)
    {
        if ( ! isset($this->data[$root][$key]) )
            $this->data[$root][$key] = array();

        foreach ( array_slice(func_get_args(), 2) as $v )
            array_unshift($this->data[$root][$key], $v);

        return $this;
    }

    /**
     * Set a key(s) and value(s) in a list
     * @param   string  $root
     * @param   mixed   $key
     * @param   mixed   $value
     * @return  self
     */
    public function lset($root, $key, $value = null)
    {
        if ( is_array($key) )
        {
            foreach ( $key as $k => &$v )
                $this->lset($root, $k, $v);
            return $this;
        }

        if ( ! isset($this->data[$root][$key]) )
            $this->data[$root][$key] = array();

        $this->data[$root][$key] = $value;

        return $this;
    }

    /**
     * Get a key from a list
     * @param   string  $root
     * @param   string  $key
     * @return  self
     */
    public function lget($root, $key)
    {
        return isset($this->data[$root][$key]) ? $this->data[$root][$key] : null;
    }

    /**
     * Get a key from a list
     * @param   string  $root
     * @param   string  $key
     * @return  self
     */
    public function lhas($root, $key)
    {
        return isset($this->data[$root][$key]);
    }

    /**
     * Unset key(s) from list
     * @param   string  $root
     * @param   string  $key
     * @return  self
     */
    public function lunset($root, $key)
    {
        if ( is_array($key) )
        {
            foreach ( $key as &$k )
                $this->lunset($root, $k);
            return $this;
        }

        unset($this->data[$root][$key]);
        return $this;
    }

    /**
     * Alias of self::lunset
     * @param   string  $root
     * @param   string  $key
     * @return  self
     */
    public function ldel($root, $key)
    {
        return isset($this->data[$root][$key]);
    }

    /**
     * Set a key to true in a list
     * @param   string  $root
     * @param   string  $key
     * @return  self
     */
    public function lenable($root, $key)
    {
        $this->lset($root, $key, true);
    }

    /**
     * Set a key to false in a list
     * @param   string  $root
     * @param   string  $key
     * @return  self
     */
    public function ldisable($root, $key)
    {
        $this->lset($root, $key, false);
    }

    /**
     * Check whether a key is true in a list
     * @param   string  $root
     * @param   string  $key
     * @return  self
     */
    public function lenabled($root, $key)
    {
        return $this->lget($root, $key) == true;
    }

    /**
     * Check whether a key is false in a list
     * @param   string  $root
     * @param   string  $key
     * @return  self
     */
    public function ldisabled($root, $key)
    {
        return $this->lget($root, $key) == false;
    }

    /**
     * Set a key value's to true
     * @param   string $k
     * @return  bool
     */
    public function enable($k)
    {
        $k = $this->_key($k);
        return $this->data[$k] = true;
    }

    /**
     * Check if a key value's were true
     * @param   string $k
     * @return  bool
     */
    public function enabled($k)
    {
        $k = $this->_key($k);
        return $this->data[$k] == true;
    }

    /**
     * Set a key value's to false
     * @param   string $k
     * @return  bool
     */
    public function disable($k)
    {
        $k = $this->_key($k);
        return $this->data[$k] = false;
    }

    /**
     * Check if a key value's were true
     * @param   string $k
     * @return  bool
     */
    public function disabled($k)
    {
        $k = $this->_key($k);
        return $this->data[$k] == false;
    }

    /**
     * Call an internal callback
     * @param   string  $name
     * @param   array   $args
     * @return  mixed
     */
    public function call($name, $args)
    {
        return $this->__call($name, (array) $args);
    }

    /** @ignore */
    public function __call($name, $args)
    {
        $name = $this->_key($name);

        if(!isset($this->data[$name]) or !is_callable($this->data[$name])) {
            throw new Horus_Exception('Call to undefined method '.__CLASS__.'::'.$name.'()');
        } else return call_user_func_array($this->data[$name], $args);
    }

    /** @ignore */
    public function &__get($key)
    {
        $key = $this->_key($key);
        $r = isset($this->data[$key]) ? $this->data[$key] : null;
        return $r;
    }

    /** @ignore */
    public function __set($key, $value)
    {
        $key = $this->_key($key);
        $this->data[$key] = $value;
    }

    /** @ignore */
    public function __isset($key)
    {
        $key = $this->_key($key);
        return isset($this->data[$key]);
    }

    /** @ignore */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /** @ignore */
    public function __invoke($key)
    {
        $key = $this->_key($key);
        return isset($this->{$key}) ? $this->{$key} : false;
    }

    /** @ignore */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /** @ignore */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /** @ignore */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /** @ignore */
    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }

    /** @ignore */
    public function count()
    {
        return sizeof($this->data);
    }

    /** @ignore */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /** @ignore */
    public function serialize()
    {
        return json_encode($this->data);
    }

    /** @ignore */
    public function unserialize($serialized)
    {
        return json_decode($serialized, true);
    }

    /** @ignore */
    public function __toString()
    {
        return sprintf('<pre>%s</pre>', print_r($this->data, true));
    }

    /**
     * Key filter
     * @param   string $key
     * @return  string
     */
    public function _key($key)
    {
        $key = trim(str_replace(array(' ', '.', '-'), '_', $key));
        return is_callable($this->key_filter) ? call_user_func($this->key_filter, $key) : $key;
    }
}

// -------------------------------

/**
 * Horus Environment Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       9.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_Environment extends Horus_Container
{
    /**
     * Constructor
     * @return  self
     */
    public function __construct()
    {
        $this->data         =   &$_SERVER;
        $this->key_filter   =   create_function('$k', 'return strtoupper($k);');
    }
}

// -------------------------------

/**
 * Horus Hooks Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       9.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_Hooks
{
    /** @ignore */
    protected $hooks, $sys;

    /**
     * Constructor
     * @param   Horus $horus
     * @return  self
     */
    public function __construct(Horus $horus)
    {
        $this->sys = $horus;
    }

    /**
     * Register a new hook
     * @param   string      $hook
     * @param   callable    $callable
     * @param   integer     $offset
     * @param   bool        $once
     * @return  self
     */
    public function hook($hook, $callable, $offset = -1, $once = false)
    {
        if ( ! isset($this->hooks[$hook]) )
            $this->hooks[$hook] = array();

        if ( ! is_callable($callable) )
            throw new Horus_Exception("The hook {$hook} in offset {$offset} has in-valid callable");

        $offset = ($offset === -1) ? sizeof($this->hooks[$hook]) : $offset;
        $this->hooks[$hook] = array_merge (
            array_slice($this->hooks[$hook], 0, $offset),
            array(array($callable, (bool) $once)),
            array_slice($this->hooks[$hook], $offset)
        );

        return $this;
    }

    /**
     * Remove hook(s)
     * @param   string      $hook
     * @param   callable    $callable
     * @return  self
     */
    public function unhook($hook, $callable = null)
    {
        if ( empty($this->hooks[$hook]) ):
            return $this;
        elseif ( empty($callable) ):
            unset($this->hooks[$hook]);
            return $this;
        else:
            foreach ( $this->hooks[$hook] as $i => &$clbk )
            {
                list($clbk, $once) = $clbk;
                if ( $callable === $clbk )
                    unset($this->hooks[$hook][$i]);
            }
            return $this;
        endif;
    }

    /**
     * Returns registered hooks, optionally of certain hooks group
     * @param   string $of
     * @return  array
     */
    public function hooks($of = null)
    {
        if ( ! empty($of) )
            return isset($this->hooks[$of]) ? (array) $this->hooks[$of] : array();
        else
            return $this->hooks;
    }

    /**
     * Execute a hooks group
     * @param   string  $hook
     * @param   mixed   $args
     * @param   mixed   $default_value
     * @return  mixed
     */
    public function apply($hook, $args = null, $default_value = null)
    {
        if ( empty($this->hooks[$hook]) )
            return $default_value;
        elseif ( ! is_array($this->hooks[$hook]) )
            return $default_value;
        else
            foreach ( $this->hooks[$hook] as $i => &$c )
            {
                list($c, $once) = $c;

                $default_value = call_user_func_array($c, array_merge(array($this->sys), (array) $args, array($default_value)));

                if ( $once == true )
                    unset($this->hooks[$hook][$i]);
            }

        return $default_value;
    }
}

// -------------------------------

/**
 * Horus Response Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       9.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_Response
{
    /** @ignore */
    protected $headers  =   array(),        $status         =   200, 
              $output   =   '',             $headers_sent   =   false,
              $type     =   'text/html',    $charset        =   'UTF-8',
              $sys      =   null
    ;

    /**
     * Constructor
     * @param   Horus   $horus
     * @return  self
     */
    public function __construct(Horus $horus)
    {
        ob_start();

        $this->sys = &$horus;
    }

    /**
     * SET/GET Current http status code
     * @param   integer $new
     * @return  self |  int
     */
    public function status($new = null)
    {
        if ( ! empty($new) )
            $this->status = (int) $new;
        else
            return $this->status;
        return $this;
    }

    /**
     * SET/GET Current http content-type
     * @param   string  $new
     * @param   string  $charset
     * @return  self |  string
     */
    public function type($new = null, $charset = 'UTF-8')
    {
        if ( ! empty($new) )
            $this->set('content-type', "{$new}; charset={$charset}") && ($this->type = $new) && ($this->charset = $charset);
        else
            return $this->type;

        return $this;
    }

    /**
     * SET/GET Current http charset
     * @param   string  $new
     * @return  self |  string
     */
    public function charset($new = null)
    {
        if ( ! empty($new) )
            $this->type($this->type(), $new) && ($this->charset = $new);
        else
            return $this->charset;

        return $this;
    }

    /**
     * Set header key to value, or pass an array to set multiple keys at once.
     * @param   string  $k
     * @param   string  $v
     * @return  self
     */
    public function set($k, $v = null)
    {
        if ( is_array($k) ) {
            foreach ( $k as $x => &$y )
                $this->set($x, $y);
            return $this;
        }

        if ( $this->headers_sent )
            return $this;

        $k  =   str_replace(' ', '-', ucwords(str_replace(array('-', '.', '_'), ' ', strtolower($k))));
        $v2 =   '';

        if ( is_array($v) )
        {
            foreach ( $v as $x => &$y )
                if ( ! empty($y) )
                    $v2 .= "{$x}={$y}; ";
        }
        else
            $v2 = $v;

        $this->headers[$k] = $v2;

        return $this;
    }

    /**
     * Checks whether a header key is set or not .
     * @param   string $k
     * @return  bool
     */
    public function has($k)
    {
        $k = str_replace(' ', '-', ucwords(str_replace(array('-', '.', '_'), ' ', strtolower($k))));
        return isset($this->headers[$k]);
    }

    /**
     * Get a header value
     * @param   string  $k
     * @return  mixed
     */
    public function get($k = null)
    {
        $k = str_replace(' ', '-', ucwords(str_replace(array('-', '.', '_'), ' ', strtolower($k))));

        if ( ! empty($k) )
            return isset($this->headers[$k]) ? $this->headers[$k] : null;
        else
            return $this->headers;
    }

    /**
     * Delete a header key
     * @param   string  $k
     * @return  self
     */
    public function del($k)
    {
        $k = $k = str_replace(' ', '-', ucwords(str_replace(array('-', '.', '_'), ' ', strtolower($k))));
        unset($this->headers[$k]);
        return $this;
    }

    /**
     * Client side page caching
     * @param   integer $ttl
     * @return  self
     */
    public function cache($ttl = 0)
    {
        $ttl = abs((int) $ttl);

        if ( $ttl )
        {
            if ( ! $this->has('cache control') )
                $this->set('cache control', sprintf('private, max-age=%s, post-check=0, pre-check=0', $ttl));

            if ( ! $this->has('pragma') )
                $this->set('pragma', 'cache');

            $since  =   (int) strtotime($this->sys->req->get('if modified since'));

            if ( (time() - $since) < $ttl )
                $this->status(304)->end();
            else
                $this->set('last modified', gmdate('D, d M Y H:i:s ', time()) . 'GMT');
        }

        return $this;
    }

    /**
     * Returns full output
     * @return  string
     */
    public function output()
    {
        return $this->output;
    }

    /**
     * Send a response
     * @param   string  $output
     * @param   bool    $override
     * @return  self
     */
    public function send($output, $override = false)
    {
        if ( ! is_string($output) )
            $output = json_encode($output);

        if ( $override )
            $this->output = $output;
        else
            $this->output .= $output;

        return $this;
    }

    /**
     * Send a formated output
     * @param   string  $format
     * @param   array   $args
     * @param   bool    $override
     * @return  self
     */
    public function sendf($format, $args, $override = false)
    {
        return $this->send(vsprintf((is_array($format) || is_object($format)) ? json_encode($format) : $format, (array) $args), $override);
    }

    /**
     * Send a template file to the output 
     * @param   string  $filename
     * @param   array   $args
     * @param   bool    $override
     * @return  self
     */
    public function sendt($filename, array $args = array(), $override = false)
    {
        $args[strtolower($this->sys->name)]    =   $this->sys;

        extract($args, EXTR_SKIP|EXTR_REFS);
        ob_start();

        foreach ( (array) $filename as $file )
            if ( is_file($file) )
                include $file;

        return $this->send(ob_get_clean(), $override);
    }

    /**
     * Proxy to print_r()
     * @param   mixed   $var
     * @param   bool    $override
     * @return  self
     */
    public function sendr($var, $override = false)
    {
        return $this->send('<pre>' . print_r($var, true) . '</pre>', $override);
    }

    /**
     * Send a "JSON" response
     * @param   mixed $output
     * @return  self
     */
    public function json($output)
    {
        $this->set('content-type', 'application/json');
        $this->charset('utf-8');
        $this->send(json_encode((object) $output), true );

        return $this;
    }

    /**
     * Send a Cookie
     * @param   string  $name
     * @param   mixed   $value
     * @param   array   $args
     * @return  self
     */
    public function cookie($name, $value, array $args = array())
    {
        $value  =   ((is_array($value) or is_object($value)) ? json_encode($value) : $value);
        $args   =   array_merge(array
        (
            'expire'    =>  (3600 * 24 * 30) + time(),
            'path'      =>  "/",
            'domain'    =>  null,
            'secure'    =>  $this->sys->req->secure(),
            'http'      =>  true
        ), $args);

        setcookie($name, $value, $args['expire'], $args['path'], $args['domain'], $args['secure'], $args['http']);

        return $this;
    }

    /**
     * Send a session value
     * @param   mixed $key
     * @param   mixed $value
     * @return  self
     */
    public function session($key, $value = null)
    {
        if ( session_id() === '' )
            session_start();

        $key        =   is_array($key) ? $key : array($key => $value);
        $_SESSION   =   array_merge($_SESSION, $key);

        return $this;
    }

    /**
     * Redirect to the given url
     * @param   string  $url
     * @param   integer $code
     * @return  void
     */
    public function redirect($url, $code = 302)
    {
        $this->set('location', $url)->status($code);
        $this->end();
    }

    /**
     * Ends the response step
     * @return  void
     */
    public function end()
    {
        if ( ! $this->get('etag') )
            $this->set('etag', md5($this->output));        
        
        if ( ! $this->headers_sent )
        {
            foreach ( $this->headers as $k => &$v )
                header(sprintf('%s: %s', $k, $v), true);
            header('X-HTTP-HORUS-STATUS: ' . $this->status, false, $this->status());
            $this->headers_sent = true;
        }

        if ( strtolower($_SERVER['REQUEST_METHOD']) !== 'head' ) 
            echo $this->sys->hooks->apply('horus.res.output', $this->output, $this->output);

        ob_end_flush();

        exit;
    }
}

// -------------------------------

/**
 * Horus Request Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       9.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_Request
{
    /** @ignore */
    public      $routes     =   array();
    protected   $sys;

    /**
     * Constructor
     * @return self
     */
    public function __construct(Horus $horus)
    {
        $this->sys = $horus;

        if ( $this->get('x-method-override') )
            $this->method(strtoupper($this->get('x-method-override')));
        if ( ! empty($_POST['X_METHOD_OVERRIDE']) )
            $this->method(strtoupper($_POST['X_METHOD_OVERRIDE']));
    }

    /**
     * Get a request header key
     * @param   string $k
     * @return  string
     */
    public function get($k)
    {
        $k = str_replace(array('-', '.', ' '), '_', strtoupper($k));
        return isset($_SERVER["HTTP_{$k}"]) ? $_SERVER["HTTP_{$k}"] : '';
    }

    /**
     * Checks whether a header key is exists or not .
     * @param   string $k
     * @return  string
     */
    public function has($k)
    {
        $k = str_replace(array('-', '.', ' '), '_', strtoupper($k));
        return isset($_SERVER["HTTP_{$k}"]);
    }

    /**
     * GET/SET the current request method
     * @param   string  $method
     * @return  string  
     */
    public function method($method = null)
    {
        if ( ! empty($method) )
            $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        return strtoupper($_SERVER['REQUEST_METHOD']);
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
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || ($_SERVER['SERVER_PORT'] == 443);
    }

    /**
     * Return any ip that may be the client ip address
     * @return  array
     */
    public function ips()
    {
        return array_unique(array_values(preg_grep('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/', array_values($_SERVER))));
    }

    /**
     * Return a value from _SESSION 
     * @param   string  $k
     * @param   mixed   $default
     * @param   mixed   $filter
     * @return  mixed
     */
    public function session($k = null, $default = null, $filter = null)
    {
        return $this->input($_SESSION, $k, $default, $filter);
    }

    /**
     * Return a value from _COOKIE 
     * @param   string  $k
     * @param   mixed   $default
     * @param   mixed   $filter
     * @return  mixed
     */
    public function cookie($k = null, $default = null, $filter = null)
    {
        return $this->input($_COOKIE, $k, $default, $filter);
    }

    /**
     * Return a value from _GET 
     * @param   string  $k
     * @param   mixed   $default
     * @param   mixed   $filter
     * @return  mixed
     */
    public function query($k = null, $default = null, $filter = null)
    {
        return $this->input($_GET, $k, $default, $filter);
    }

    /**
     * Return a value from BODY [post, put, head, .. etc] 
     * @param   string  $k
     * @param   mixed   $default
     * @param   mixed   $filter
     * @return  mixed
     */
    public function body($k = null, $default = null, $filter = null)
    {
        static $body = array();

        if ( empty($body) )
        {
            if ( $this->method() === 'POST' )
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

        return $this->input($body, $k, $default, $filter);
    }

    /**
     * Return value from input source and optionally filter it
     * @param   array   $source
     * @param   string  $k
     * @param   mixed   $default
     * @param   mixed   $filter
     * @return  mixed
     */
    public function &input(array $source, $k = null, $default = null, $filter = null)
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
 * Horus Router Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       1.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_Router
{
    /** @ignore */
    protected   $sys, $base;

    /** @ignore */
    protected  $found, $wait = 0;

    /** @ignore */
    protected   $regex  =   array
    (
        '@int'      =>  '([0-9\.,]+)',
        '@alpha'    =>  '([a-zA-Z]+)',
        '@alnum'    =>  '([a-zA-Z0-9\.\w]+)',
        '@str'      =>  '([a-zA-Z0-9-_\.\w]+)',
        '@any'      =>  '([^\/]+)',
        '@*'        =>  '?(.*)',
        '@date'     =>  '(([0-9]+)\/([0-9]{2,2}+)\/([0-9]{2,2}+))'
    );

    /**
     * Constructor
     * @param   Horus $horus
     * @return  self
     */
    public function __construct(Horus $horus)
    {
        $this->sys                      =   $horus;
        $this->rewrite();
        $horus->env->horus_haystack     =   sizeof($x = ltrim(rtrim($horus->env->path_info, '/'), '/')) == 0 ? '/' : "/{$x}/";
        $horus->env->horus_haystack     =   '//' .
                                            preg_replace('/^('.(join('|', (array) $horus->env->get('horus.sub.domains'))).')\./i', '', $horus->env->get('http.host'), 1) .
                                            preg_replace('/\/+/', '/', $horus->env->horus_haystack);
        $this->domain                   =   $horus->env->get('horus.domain');
    }

    /**
     * Wait for new route
     * @return void
     */
    public function wait()
    {
        $this->wait = true;
    }

    /**
     * whether we are waiting or not
     * @return bool
     */
    public function waiting()
    {
        return $this->wait == true;
    }

    /**
     * Don't run any route except for current
     * @return void
     */
    public function stop()
    {
        $this->wait = false;
    }

    /**
     * whether we are stopped or not
     * @return bool
     */
    public function stopped()
    {
        return $this->wait == false;
    }


    /**
     * Return number of found routes
     * @param   $incr
     * @return  int
     */
    public function found($incr = false)
    {
        if ( $incr )
            ++ $this->found;

        return $this->found;
    }

    /**
     * Fix and prepare the pattern
     * @param   string $pattern
     * @param   string $escape
     * @return  string
     */
    public function pattern($pattern, $escape = '/.')
    {
        $pattern    =   is_array($pattern) ? (join('|', $pattern)) : $pattern;

        if ( strpos($this->base, '//') !== 0 )
            $this->base = $this->sys->env->get('horus.domain') . '/';

        if ( strpos($pattern, '//') !== 0 )
            $pattern = $this->base . '/' . $pattern;

        $pattern    =   '//' . preg_replace('/\/+/', '/', ltrim(rtrim($pattern, '/'), '/')) . '/';
        $pattern    =   str_ireplace( array_keys($this->regex), array_values($this->regex), $pattern );

        return preg_replace('/\\\+/', '\\', addcslashes( $pattern, $escape ));
    }

    /**
     * Whether current uri haystack matches a certain pattern
     * @param   string  $pattern
     * @param   bool    $fix
     * @param   bool    $strict
     * @return  bool
     */
    public function is($pattern, $fix = true, $strcit = true)
    {
        $x = (bool) preg_match('/^'.($fix ? $this->pattern($pattern) : $pattern).($strcit ? '$' : null).'/', $this->sys->env->get('horus.haystack'), $m);
        return $x ? $m : false;
    }

    /**
     * Virtually rewrite a uri from one to another
     * @param   string  $from
     * @param   string  $to
     * @param   string  $method
     * @param   string  $strict
     * @return  self
     */
    public function re($from, $to, $method = 'get', $strict = true)
    {
        if ( is_array($from) ) {
            foreach ( $from as &$f )
                $this->re($f, $to, $method, $strict);
            return $this;
        }

        if ( $this->is($from, true, $strict) ) {
            $this->sys->req->method(strtoupper($method));
            $this->sys->env->set('horus.haystack', $this->pattern($to, ''));
        }

        return $this;
    }

    /**
     * Add new REGEXP shortcut
     * @param   string $k
     * @param   string $v
     * @return  void
     */
    public function __set($k, $v)
    {
        $this->regex["@{$k}"] = $v;
    }

    /**
     * Remove a REGEXP shortcut
     * @param   string $k
     * @return  void
     */
    public function __unset($k)
    {
        unset($this->regex["@{$k}"]);
    }

    /**
     * Proxy to self::handle()
     * @param   string  $k
     * @param   array   $argv
     * @return  self
     */
    public function __call($k, $argv)
    {
        $k = strtolower(str_replace('_', '|', $k));

        if ( $k === 'group' )
            call_user_func_array( array( $this, 'handle' ), $argv );
        else
            call_user_func_array( array( $this, 'handle' ), array_merge( array($k), $argv ) );

        return $this;
    }

    /**
     * Rewrite and pathinfo fixer helper
     * @return void
     */
    protected function rewrite()
    {
        $using  =   strtolower($this->sys->env->get('horus_rewrite_using'));

        if ( empty($this->sys->env->request_uri) )
            $this->sys->env->request_uri = '/';

        $this->sys->env->request_uri    =   '/' . ltrim($this->sys->env->request_uri, '/');
        $this->sys->env->script_name    =   '/' . ltrim($this->sys->env->script_name, '/');

        if ( $using == 'path_info' || $using == 'request_uri' )
            $this->sys->env->horus_rewrited     =   (bool) (stripos($this->sys->env->request_uri, $this->sys->env->script_name . '/') === 0);
        else
            $this->sys->env->horus_rewrited     =   (bool) (stripos($this->sys->env->request_uri, dirname($this->sys->env->script_name) . '/?/') === 0);

        if ( $this->sys->env->enabled('horus.rewrite') && $this->sys->env->disabled('horus.rewrited') )
            $this->sys->res->redirect($this->sys->util->url('%vurl'));

        if ( $using == 'path_info' )
        {
            if ( ! isset($this->sys->env->path_info) && isset($this->sys->env->orig_path_info) )
                $this->sys->env->path_info = $this->sys->env->orig_path_info;
            elseif ( ! isset($this->sys->env->path_info) && isset($this->sys->env->porig_path_info) )
                $this->sys->env->path_info = $this->sys->env->porig_path_info;
            elseif ( ! isset($this->sys->env->path_info) )
                $using = 'request_uri';
        }

        if ( $using == 'request_uri' )
        {
            $this->sys->env->path_info = parse_url($this->sys->env->request_uri, PHP_URL_PATH);
    
            if ( stripos($this->sys->env->path_info, $sn = $this->sys->env->script_name) === 0 )
                $this->sys->env->path_info = substr($this->sys->env->path_info, strlen($sn));
            elseif ( stripos($this->sys->env->path_info, $dn = dirname($sn)) === 0 )
                $this->sys->env->path_info = substr($this->sys->env->path_info, strlen($dn));
        }
        elseif ( $using == 'query' )
        {
            @ list($this->sys->env->path_info, $this->sys->env->query_string) = (array) explode('?', $this->sys->env->query_string, 2);
            $this->sys->env->query_string = (string) $this->sys->env->query_string;
            parse_str(str_replace(array("\0", chr(0)), '', $this->sys->env->query_string), $_GET);
        }
    }

    /**
     * Handle request
     * @return  self
     */
    protected function handle()
    {
        if ( ($n = func_num_args()) === 2 ):

            list( $pattern, $callable ) = func_get_args();

            if ( is_array($pattern) )
            {
                foreach ( $pattern as &$patt )
                    $this->handle($patt, $callable);
                return $this;
            }

            $old        =   (string) $this->base;
            $this->base =   $this->pattern($pattern, null);

            if ( preg_match('/^'.(addcslashes($this->base, '/')).'/', $this->sys->env->horus_haystack) )
                call_user_func( $callable, $this->sys );

            $this->base = $old;

            return $this;

        elseif ( $n >= 3 ):

            @ list( $method, $pattern, $callable, $strict )    =   func_get_args();

            $strict = ($strict === false) ? false : true;

            if ( is_array($pattern) )
            {
                foreach ( $pattern as &$patt )
                    $this->handle($method, $patt, $callable);
                return $this;
            }

            $this->sys->env->horus_pattern =   $this->pattern($pattern, '/');
            $method     =   empty($method) ? 'any' : $method;
            $method     =   strtolower(is_array($method)  ? join('|', $method) : str_replace(',', '|', $method));
            $method     =   ltrim(rtrim(str_replace('|any|', "|".($this->sys->req->method())."|", "|{$method}|"), '|'), '|');

             // die(json_encode(array('p' => $this->sys->env->horus_pattern, 'h' => $this->sys->env->horus_haystack)));

            if ( ! is_callable($callable) )
                throw new Horus_Exception('Wait, invalid callable for "'.$method.'" for "'.$pattern.'"');
    
            elseif ( preg_match("/{$method}/", strtolower($this->sys->req->method())) &&  ($m = $this->is($this->sys->env->horus_pattern, false, $strict)) )
            {
                ob_start();
                array_shift($m);
                call_user_func_array( $callable, array_merge(array($this->sys), $m) );

                $this->sys->res->send(ob_get_clean());

                $this->found(true);

                if ( ! $this->waiting() )
                    $this->sys->run();
                else
                    $this->stop();
            }

            return $this;

        endif;
    }
}

// -------------------------------

/**
 * Horus Utilities Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       9.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_Util
{
    /** @ignore */
    protected $sys;

    /**
     * Constructor
     * @param   Horus   $horus
     * @return  void
     */
    public function __construct(Horus $horus)
    {
        $this->sys = $horus;
    }

    /**
     * Throw a Horus Exception
     * @param   string  $message
     * @return  void
     */
    public function exception($message)
    {
        throw new Horus_Exception( $string );
    }

    /**
     * Generate a formated url
     * @param   string  $format
     * @param   mixed   $args
     * @return  string
     */
    public function url($format = null, $args = null)
    {
        static $x = array();

        if ( empty($format) )
            return $x;

        if ( empty($x) )
        {
            $x = array
            (
                '%scheme'   =>  $this->sys->env->get('horus.scheme'),
                '%domain'   =>  $this->sys->env->get('horus.domain'),
                '%rpath'    =>  dirname($_SERVER['SCRIPT_NAME']),
                '%vpath'    =>  $this->sys->env->enabled('horus.rewrite') 
                                ? 
                                (
                                    (strtolower($this->sys->env->get('horus.rewrite.using')) == 'query') 
                                    ? dirname($_SERVER['SCRIPT_NAME'])."/?/" 
                                    : "/{$_SERVER['SCRIPT_NAME']}/"
                                ) 
                                : dirname($_SERVER['SCRIPT_NAME'])
            );

            $x['%rurl']     =   $x['%scheme'].'://'.$x['%domain'].$x['%rpath'];
            $x['%vurl']     =   $x['%scheme'].'://'.$x['%domain'].$x['%vpath'];
        }

        if ( is_array($format) )
        {
            foreach ( $format as $k => &$f )
                $x[$k] = str_replace(array_keys($x), array_values($x), $f);
            return $this->sys;
        }

        $format =   preg_replace('/^([a-z]+):\/\//', '$scheme$', vsprintf(str_ireplace(array_keys($x), array_values($x), $format), is_array($args) ? $args : array_slice(func_get_args(), 1)), 1);
        $format =   preg_replace('/\/+/', '/', ltrim($format, '/'));

        return      preg_replace('/^\$scheme\$/', $x['%scheme'].'://', $format, 1);
    }

    /**
     * Debug (show errors) or not ?
     * @param   bool $state
     * @return  void
     */
    public function debug($state = true)
    {
        if($state) error_reporting(E_ALL);
        else error_reporting(0);
    }

    /**
     * MCrypt Encrypt
     * @param   mixed   $data
     * @param   string  $key
     * @return  string
     */
    public function encrypt($data, $key)
    {
        $data       =   serialize($data);
        $key        =   hash('sha256', $key, true);
        $cipher     =   MCRYPT_RIJNDAEL_128;
        $mode       =   MCRYPT_MODE_CBC;
        $iv_size    =   mcrypt_get_iv_size($cipher, $mode);
        $iv         =   mcrypt_create_iv($iv_size, MCRYPT_RAND);

        return  base64_encode(serialize(array($iv, mcrypt_encrypt($cipher, $key, $data, $mode, $iv))));
    }

    /**
     * MCrypt Decrypt
     * @param   string $data
     * @param   string $key
     * @return  mixed
     */
    public function decrypt($data, $key)
    {
        $key    =   hash('sha256', $key, true);
        $cipher =   MCRYPT_RIJNDAEL_128;
        $mode   =   MCRYPT_MODE_CBC;

        @ list($iv, $encrypted) = (array) unserialize(base64_decode($data));

        return unserialize(trim(mcrypt_decrypt($cipher, $key, $encrypted, $mode, $iv)));
    }

    /**
     * Hash string
     * @param   string  $string
     * @param   int     $algorithm
     * @return  string
     */
    public function hashMake($string, $algorithm = 'blowfish')
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
     * Checks if the hashed value is valid
     * @param   string $real
     * @param   string $hash
     * @return  bool
     */
    public function hashVerify($real, $hash)
    {
        $hash   =   base64_decode($hash);
        return      crypt($real, $hash) == $hash;
    }

    /**
     * Shortcut to hash()
     * @see     hash()
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
 * Horus SPL Autoloader Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       9.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_AutoLoader
{
    /** @ignore */
    protected $log = array(), $classes = array(), $prefixes = array(), $dirs = array();

    /** @ignore */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Constructor
     * @return self
     */
    public function __construct()
    {
        $this->addDir(BASEPATH, array('php', 'class.php'));
    }

    /**
     * Register Horus_Autoloader as spl autoload
     * @return self
     */
    public function start()
    {
        spl_autoload_register(array($this, 'load'));
        return $this;
    }

    /**
     * Unregister Horus_Autoloader
     * @return  self
     */
    public function stop()
    {
        spl_autoload_unregister(array($this, 'load'));
        return $this;
    }

    /**
     * Prepare a class name/prefix
     * @param   string $name
     * @return  string
     */
    public function prepare($name)
    {
        return rtrim(ltrim(str_replace(array('\\', '_', '/', '.', ' '), self::DS, $name), self::DS), self::DS);
    }

    /**
     * Return logs array
     * @return  array
     */
    public function logs()
    {
        return $this->log;
    }

    /**
     * Return the full autoloader maps
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

    /**
     * Add a directory to directories map
     * @param   string  $dir
     * @param   mixed   $ext
     * @return  self
     */
    public function addDir($dir, $ext = 'php')
    {
        $ext = empty($ext) ? 'php' : $ext;
        $this->dirs[realpath($dir) . self::DS] = (array) $ext;
        return $this;
    }

    /**
     * Add directories to directories map
     * @param   array  $dirs
     * @return  self
     */
    public function addDirs(array $dirs)
    {
        foreach ( $dirs as $dir => &$exts )
            $this->addDir($dir, $exts);
        return $this;
    }

    /**
     * Add a class to classes map
     * @param   string $class_name
     * @param   string $class_path
     * @return  self
     */
    public function addClass($class_name, $class_path)
    {
        $this->classes[$this->prepare($class_name)] = $class_path;
        return $this;
    }

    /**
     * Add map of classes to classes map
     * @param   array $classes
     * @return  self
     */
    public function addClasses(array $classes)
    {
        foreach ( $classes as $class => &$path )
            $this->addClass($class, $path);
        return $this;
    }

    /**
     * Add a prefix with its paths & extensions
     * @param   string  $prefix
     * @param   mixed   $path
     * @param   mixed   $ext
     * @return  self
     */
    public function addPrefix($prefix, $path, $ext = 'php')
    {
        $ext = empty($ext) ? 'php' : $ext;
        $this->prefixes[$this->prepare($prefix) . self::DS] = array((array) $path, (array) $ext);
        return $this;
    }

    /**
     * Add prefxes to the prefixes map
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
     * @param   mixed $class_name
     * @return  string | false
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
     * splLoader callback
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
}

// -------------------------------

/**
 * Horus PDO Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       9.0.0
 * @copyright   2014 (c) HPHP Framework
 */
class Horus_PDO extends PDO
{
    /** @ignore */
    protected $stmnt;

    /** @ignore */
    public function __construct(){}

    /**
     * Connector
     * @param string    $dns
     * @param string    $username
     * @param string    $password
     * @return Object
     */
    public function connect($dns, $username = null, $password = null, array $driver_options = array())
    {
        try
        {
            parent::__construct($dns, $username, $password, $driver_options);
            $this->_setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch( PDOException $e )
        {
            throw new Horus_Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Connect mysql server
     * @param   string  $host
     * @param   string  $dbname
     * @param   string  $username
     * @param   string  $password
     * @param   array   $options
     * @return  self
     */
    public function mysql($host, $dbname, $username = null, $password = null, array $options = array())
    {
        return $this->connect("mysql:host={$host}; dbname={$dbname}", $username, $password, $options);
    }

    /**
     * Connect postgre-sql server
     * @param   string  $host
     * @param   string  $dbname
     * @param   string  $username
     * @param   string  $password
     * @param   array   $options
     * @return  self
     */
    public function pgsql($host, $dbname, $username = null, $password = null, array $options = array())
    {
        return $this->connect("pgsql:host={$host}; dbname={$dbname}; user={$username}; password={$password}", $options);
    }

    /**
     * Connect to sqlite dbfile
     * @param   string  $dbfile
     * @param   array   $options
     * @return  self
     */
    public function sqlite($dbfile, array $options = array())
    {
        return $this->connect("sqlite:{$dbfile}", null, null, $options);
    }

    /**
     * Execute sql statement
     * @param   string    $statement
     * @param   mixed     $inputs
     * @return  False | object
     */
    public function query($statement, $inputs = null)
    {
        $this->stmnt = $this->prepare($statement);

        if ( ! is_object($this->stmnt) && ! $this->stmnt instanceof PDOStatement )
            return false;

        return ((bool) ( $this->stmnt->execute((array) $inputs) ) ? $this : false);
    }

    /** @ignore */
    public function __call($name, $args)
    {
        if(is_callable(array($this->stmnt, $name))) {
            return call_user_func_array(array($this->stmnt, $name), $args);
        }
    }
}

// -------------------------------

/**
 * Horus Main Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       1.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus extends Horus_Container
{
    /** @ignore */
    public      $res, $req, $env, $hooks, $util, $name = null;

    /** @ignore */
    protected   $router, $pdo, $loader;

    /** @ignore */
    protected static $instance;

    /** @ignore */
    const VERSION = '9.4.1';

    /**
     * Constructor
     * @return  self
     */
    public function __construct($ob_handler = null)
    {
        @ ob_clean();

        if ( defined('HORUS_INIT') )
            throw new Horus_Exception( 'You cannot re-create Horus object' );

        is_callable($ob_handler) && ob_start($ob_handler);

        ini_set('session.cookie_httponly',          1);
        ini_set('session.use_only_cookies',         1);
        ini_set('session.name',             'HPHPSESS');

        $this->name     =   'horus';
        $this->env      =   new Horus_Environment;
        $this->hooks    =   new Horus_Hooks($this);
        $this->util     =   new Horus_Util($this);
        $this->res      =   new Horus_Response($this);
        $this->req      =   new Horus_Request($this);

        define('DS',            DIRECTORY_SEPARATOR);
        define('COREPATH',      dirname(__FILE__) . DS);
        define('BASEPATH',      str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME'])) . DS);
        define('HORUS_INIT',    microtime(true));

        $this->env->set('horus.scheme', $this->req->secure() ? 'https' : 'http');
        $this->env->set('horus.domain', $_SERVER['SERVER_NAME']);
        $this->env->set('horus.sub.domains', array('www'));
        $this->env->set('horus.rewrite', (bool) (int) (isset($_SERVER['HORUS_REWRITE']) ? $_SERVER['HORUS_REWRITE'] : 1));
        $this->env->set('horus.rewrite.using', $using = 'path_info');

        $this->set('e404', create_function('', 'return "<h1>404 page not found</h1><p>the requested resource not found</p>";'));

        $this->res->type('text/html');
        $this->res->set(array
        (
            'x-powered-by'              =>  'H-PHP-9',
            'x-frame-options'           =>  'SAMEORIGIN',
            'X-XSS-Protection'          =>  '1; mode=block',
            'X-Content-Type-Options'    =>  'nosniff'
        ));

        self::$instance = $this;
    }

    /**
     * Returns horus instance
     * @return  Horus
     */
    public static function I()
    {
        return self::$instance;
    }

    /** @ignore */
    public function &__get($k)
    {
        if ( strtolower($k) == 'router' )
        {
            if ( ! $this->router instanceof Horus_Router )
                $this->router = new Horus_Router($this);
            return $this->router;
        }
        elseif ( strtolower($k) == 'pdo' )
        {
            if ( ! $this->pdo instanceof Horus_PDO )
                $this->pdo = new Horus_PDO;
            return $this->pdo;
        }
        elseif ( strtolower($k) == 'loader' )
        {
            if ( ! $this->loader instanceof Horus_AutoLoader )
                $this->loader = new Horus_AutoLoader;
            return $this->loader;
        }
        else
            return parent::__get($k);
    }

    /**
     * Run the application
     * @return void
     */
    public function run()
    {
        if ( $this->router instanceof Horus_Router && ! $this->router->found() )
            $this->res->status(404)->send($this->e404());
        $this->res->end();
    }
}

// -------------------------------

/**
 * Horus Exception Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       9.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_Exception extends Exception{}
