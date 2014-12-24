<?php @ob_clean(); (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) && die('<h1>Direct access not allowed');
/**
 * Horus PHP Framework
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @version     9.0.0
 * @license     MIT
 * @copyright   2014 (c) HPHP Framework
 */

// -------------------------------

/**
 * Horus Container
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal <http://is.gd/alash3al>
 * @since       5.0.0
 * @copyright   2014 (c) HPHP Framework
 */
Class Horus_Container implements ArrayAccess, Countable, IteratorAggregate, Serializable
{
    /**
     * @var array
     */
    public $data = array(), $key_filter = null;

    /**
     * Construct
     * @param   array $data
     * @return  void
     */
    public function __construct(array $data = array())
    {
        $this->data = &$data;
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
     * @param   array $data
     * @return  void
     */
    public function import(array $data)
    {
        $this->data = &$data;
    }

    /**
     * Set a key's value
     * @param   string  $k
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
     * Get a key's value
     * @param   string  $k
     * @return  mixed
     */
    public function get($k)
    {
        return $this->__get($k);
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
        $this->data[$key] = &$value;
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
    public function &__toString()
    {
        return $this->data;
    }

    /**
     * Key filter
     * @param   string $key
     * @return  string
     */
    public function _key($key)
    {
        $key = str_replace(array(' ', '.', '-'), '_', $key);
        return is_callable($this->key_filter) ? call_user_func($this->key_filter, $key) : $key;
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
    protected $hooks;

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

                $default_value = call_user_func_array($c, array_merge((array) $args, array($default_value)));

                if ( $once == true )
                    unset($this->hooks[$hook][$i]);
            }

        return $default_value;
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
        parent::__construct($_SERVER);
        $this->key_filter = create_function('$k', 'return strtoupper($k);');
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
    protected $headers = array(), $status = 200, 
              $output = "", $headers_sent = false,
              $type, $charset, $sys;

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
     * @return  self |  string
     */
    public function type($new = null, $charset = 'utf-8')
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

        $k = str_replace(' ', '-', ucwords(str_replace(array('-', '.', '_'), ' ', strtolower($k))));
        $v2 = "";

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
        $args['horus']    =   $this->sys;

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
        $value = ((is_array($value) or is_object($value)) ? json_encode($value) : $value);
        $args = array_merge(array
        (
            'expire'    =>  (3600 * 24 * 30) + time(),
            'path'      =>  "/",
            'domain'    =>  null,
            'secure'    =>  false,
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
            foreach ( $this->headers as $k => &$v )
                header(sprintf('%s: %s', $k, $v), true, $this->status);

        $this->headers_sent = true;

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
    public function __construct(Horus $app)
    {
        $this->sys = $app;

        if ( ! empty($_POST['X_METHOD_OVERRIDE']) )
            $this->method(strtoupper($_POST['X_METHOD_OVERRIDE']));
        if ( $this->get('x-method-override') != "" )
            $this->method(strtoupper($this->get('x-method-override')));
    }

    /**
     * Get a request header key
     * @param   string $k
     * @return  string
     */
    public function get($k)
    {
        $k = str_replace(array('-', '.', ' '), '_', strtoupper($k));
        return isset($_SERVER["HTTP_{$k}"]) ? $_SERVER["HTTP_{$k}"] : "";
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
        return strtolower($_SERVER['X-Requested-With']) == strtolower('XMLHttpRequest');
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
    protected  $found, $next = 0;

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
        $this->sys              =   $horus;

        $this->rewrite();

        $s                      =   &$_SERVER;
        $s['HORUS_SCHEME']      =   $this->sys->env->get('horus_scheme');      
        $s['HORUS_DOMAIN']      =   $this->sys->env->get('horus_domain');
        $s['HORUS_HAYSTACK']    =   ltrim(rtrim($_SERVER['PATH_INFO'], '/'), '/');
        $s['HORUS_HAYSTACK']    =   empty($s['HORUS_HAYSTACK']) ? '/' : "/{$s['HORUS_HAYSTACK']}/";
        $s['HORUS_HAYSTACK']    =   '//' .
                                    preg_replace('/^('.(join('|', (array) $horus->env->get('horus.sub.domains'))).')\./i', '', $s['HTTP_HOST'], 1) .
                                    preg_replace('/\/+/', '/', $s['HORUS_HAYSTACK']);
    }

    /**
     * Wait for new route
     * @return void
     */
    public function wait()
    {
        $this->next = true;
    }

    /**
     * whether we are waiting or not
     * @return bool
     */
    public function waiting()
    {
        return $this->next == true;
    }

    /**
     * Don't run any route except for current
     * @return void
     */
    public function stop()
    {
        $this->next = false;
    }

    /**
     * whether we are stopped or not
     * @return bool
     */
    public function stopped()
    {
        return $this->next == false;
    }


    /**
     * Return number of found routes
     * @return  int
     */
    public function found()
    {
        return $this->found;
    }

    /**
     * Whether current uri haystack matches a certain pattern
     * @param   string $pattern
     * @return  bool
     */
    public function is($pattern)
    {
        return (bool) preg_match('/^'.($this->pattern($pattern)).'$/', $_SERVER['HORUS_HAYSTACK']);
    }

    /**
     * Virtually rewrite a uri from one to another
     * @param   string $from
     * @param   string $to
     * @param   string $method
     * @return  self
     */
    public function re($from, $to, $method = 'get')
    {
        if ( is_array($from) ) {
            foreach ( $from as &$f )
                $this->re($f, $to, $method);
            return $this;
        }

        if ( $this->is($from) ) {
            $_SERVER['REQUEST_METHOD'] = strtoupper($method);
            $_SERVER['HORUS_HAYSTACK'] = $this->pattern($to, '');
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
     * Fix and prepare the pattern
     * @param   string $pattern
     * @param   string $escape
     * @return  string
     */
    public function pattern( $pattern, $escape = '/.' )
    {
        $s          =   &$_SERVER;
        $pattern    =   is_array($pattern) ? ("".join('|', $pattern)."") : $pattern;

        if ( strpos($this->base, '//') !== 0 )
            $this->base = $s['HORUS_DOMAIN'] . '/';

        if ( strpos($pattern, '//') !== 0 )
            $pattern = $this->base . '/' . $pattern;

        $pattern    =   '//' . preg_replace('/\/+/', '/', ltrim(rtrim($pattern, '/'), '/')) . '/';
        $pattern    =   str_ireplace( array_keys($this->regex), array_values($this->regex), $pattern );

        return preg_replace('/\\\+/', '\\', addcslashes( $pattern, $escape ));
    }

    /**
     * Rewrite and pathinfo fixer helper
     * @return void
     */
    protected function rewrite()
    {
        $s      =   &$_SERVER;
        $using  =   strtolower($this->sys->env->get('horus_rewrite_using'));

        if ( empty($s['REQUEST_URI']) )
            $s['REQUEST_URI'] = '/';

        $s['REQUEST_URI'] = '/' . ltrim($s['REQUEST_URI'], '/');
        $s['SCRIPT_NAME'] = '/' . ltrim($s['SCRIPT_NAME'], '/');

        if ( $using == 'path_info' || $using == 'request_uri' )
            $s['HORUS_REWRITED'] = (bool) (stripos($s['REQUEST_URI'], $s['SCRIPT_NAME'] . '/') === 0);
        else
            $s['HORUS_REWRITED'] =  (bool) (stripos($s['REQUEST_URI'], dirname($s['SCRIPT_NAME']) . '/?/') === 0);

        if ( $this->sys->env->enabled('horus.rewrite') && ! $s['HORUS_REWRITED'] )
            $this->sys->res->redirect($this->sys->util->url('%vurl'));

        if ( $using == 'path_info' )
        {
            if ( ! isset($s['PATH_INFO']) && isset($s['ORIG_PATH_INFO']) )
                $s['PATH_INFO'] = $s['ORIG_PATH_INFO'];
            elseif ( ! isset($s['PATH_INFO']) && isset($s['PORIG_PATH_INFO']) )
                $s['PATH_INFO'] = $s['PORIG_PATH_INFO'];
            else
                $using = 'request_uri';
        }

        if ( $using == 'request_uri' )
        {
            $s['PATH_INFO'] = parse_url($s['REQUEST_URI'], PHP_URL_PATH);
    
            if ( stripos($s['PATH_INFO'], $sn = $s['SCRIPT_NAME']) === 0 )
                $s['PATH_INFO'] = substr($s['PATH_INFO'], strlen($sn));
            elseif ( stripos($s['PATH_INFO'], $dn = dirname($sn)) === 0 )
                $s['PATH_INFO'] = substr($s['PATH_INFO'], strlen($dn));
        }
        elseif ( $using == 'query' )
        {
            @ list($s['PATH_INFO'], $s['QUERY_STRING']) = (array) explode('?', $s['QUERY_STRING'], 2);
            $s['QUERY_STRING'] = (string) $s['QUERY_STRING'];
            parse_str($s['QUERY_STRING'], $_GET);
        }
    }

    /**
     * Handle request
     * @param   mixed $argv
     * @return  self
     */
    protected function handle( $argv )
    {
        if ( ($n = func_num_args()) === 2 )
        {
            list( $pattern, $callable ) = func_get_args();

            if ( is_array($pattern) )
            {
                foreach ( $pattern as &$patt )
                    $this->handle($patt, $callable);
                return $this;
            }

            $old        =   $this->base;
            $this->base =   $this->pattern($pattern, '');

            call_user_func( $callable, $this->sys );

            $this->base = $old;

            return $this;
        }
        elseif ( $n === 3 )
        {
            list( $method, $pattern, $callable )    =   func_get_args();

            if ( is_array($pattern) )
            {
                foreach ( $pattern as &$patt )
                    $this->handle($method, $patt, $callable);
                return $this;
            }

            $pattern = $this->pattern($pattern, '/');
        }

        $s                  =   &$_SERVER;
        $s['HORUS_PATTERN'] =   &$pattern;

        $method     =   empty($method) ? 'any' : $method;
        $method     =   strtolower(is_array($method)  ? join('|', $method) : str_replace(',', '|', $method));
        $method     =   ltrim(rtrim(str_replace('|any|', "|".strtolower($s['REQUEST_METHOD'])."|", "|{$method}|"), '|'), '|');

        //die(json_encode(array('p' => $pattern, 'h' => $s['HORUS_HAYSTACK'])));

        if ( ! is_callable($callable) )
            throw new Horus_Exception('Wait, invalid callable for "'.$method.'" for "'.$pattern.'"');

        elseif ( preg_match("/{$method}/", strtolower($s['REQUEST_METHOD'])) &&  preg_match("/^{$pattern}$/", $s['HORUS_HAYSTACK'], $m) )
        {
            ob_start();

            array_shift($m);

            call_user_func_array( $callable, array_merge(array($this->sys), $m) );

            ++ $this->found;

            $this->sys->res->send(ob_get_clean());

            if ( ! $this->waiting() )
                $this->sys->run();
            else
                $this->stop();
        }

        return $this;
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
    public function url($format, $args = null)
    {
        static $x = array();

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
    public      $res, $req, $env, $hooks, $util = null;

    /** @ignore */
    protected   $router, $pdo, $loader;

    /** @ignore */
    protected static $instance;

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

        $this->env      =   new Horus_Environment;
        $this->hooks    =   new Horus_Hooks;
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
        $this->res->set(array(
            'x-powered-by'              =>  'HPHP9/ExpressDev',
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
