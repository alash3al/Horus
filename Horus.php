<?php
/**
 * Horus - a PHP 5 micro framework
 * 
 * @package     Horus
 * @copyright   2014 (c) Mohammed Al Ashaal
 * @author      Mohammed Al Ashaal <http://is.gd/alash3al>
 * @link        http://alash3al.github.io/Horus
 * @license     MIT LICENSE
 * @version     11.0.0
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
 * The above copyright notice and this permission notice must be
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
namespace Horus;

/**
 * Prototype
 *
 * @package     Horus
 * @author      Mohammed Al Ashaal
 * @since       11.0.0
 */
Class Prototype extends \ArrayObject
{
    /**
     * The instances storage of this class
     * @var ArrayObject
     */
    protected   static   $instances  =   null;

    /**
     * Constructor
     * 
     * @param   mixed   $from   the input to be imported
     */
    public function __construct($from = array())
    {
        parent::__construct($from);

        if ( ! self::$instances instanceof \ArrayObject ) {
            self::$instances    =   new \ArrayObject(array());
        }

        self::$instances[$c = get_called_class()] = $this;
    }

    /** Destructor */
    public function __destruct()
    {
        self::$instances    =   null;
    }

    /**
     * Called to normalize the key
     * 
     * @param   string  $key    the key to be normalized
     * @return  string
     */
    public function __key($key)
    {
        return $key;
    }

    /**
     * Get a key from the storage
     * 
     * @param   string  $key    the key to fetch
     * @return  mixed
     */
    public function __get($key)
    {
        if ( ! isset($this[$key]) ) {
            $this[$key] = new Prototype;
        } elseif ( is_array($this[$key]) ) {
            $this[$key] = new Prototype($this[$key]);
        }

        return $this[$key];
    }

    /**
     * Store a new key and its value in the store
     * 
     * @param   string  $key    the key name
     * @param   mixed   $value  the value of the key
     * @return  void
     */
    public function __set($key, $value)
    {
        if ( empty($key) ) {
            $key = $this->count();
        }

        $this[$key] = is_array($value) ? new Prototype($value) : $value;
    }

    /**
     * Called when you try to access an object as a function
     * 
     * @param   string  $key    the key that will be called as a function
     * @return  mixed
     */
    public function __invoke($key)
    {
        return $this->__get($key);
    }

    /**
     * Whether the requested key exists or not
     * 
     * @param   string  $key    the key to check
     * @return  bool
     */
    public function __isset($key)
    {
        return isset($this[$key]);
    }

    /**
     * Remove a key from the store
     * 
     * @param   string   $key   the key to be removed
     * @return  void
     */
    public function __unset($key)
    {
        unset($this[$key]);
    }

    /**
     * Return a serialized string of this object
     * 
     * @return  string
     */
    public function __toString()
    {
        return serialize($this);
    }

    /**
     * @see \ArrayObject::offsetSet()
     */
    public function offsetSet($key, $value)
    {
        $key    =   $this->__key($key);
        parent::offsetSet($key, $value);
    }

    /**
     * @see \ArrayObject::offsetGet()
     */
    public function offsetGet($key)
    {
        $key    =   $this->__key($key);
        return parent::offsetGet($key);
    }

    /**
     * @see \ArrayObject::offsetUnset()
     */
    public function offsetUnset($key)
    {
        $key    =   $this->__key($key);
        parent::offsetUnset($key);
    }

    /**
     * @see \ArrayObject::offsetExists()
     */
    public function offsetExists($key)
    {
        $key    =   $this->__key($key);
        return parent::offsetExists($key);
    }

    /**
     * Call a key as a method from the store
     * 
     * @param   string  $key    the key to call
     * @param   array   $args   the arguments to pass to the function
     * @return  mixed
     */
    public function __call($key, $args)
    {
        return is_callable($c = $this->__get($key)) ? call_user_func_array($c, $args) : null;
    }

    /**
     * Call a key as a function statically
     * 
     * @param   string  $key    the key to call
     * @param   array   $args   the arguments to pass to the function
     * @return  mixed
     */
    public static function __callStatic($key, $args)
    {
        return self::instance()->__call($key, $args);
    }

    /**
     * Return the instance of the called class
     * 
     * @return  this
     */
    public static function instance()
    {
        $class = get_called_class();

        if ( ! isset(self::$instances[$class]) ) {
            self::$instances[$class] = new $class;
        }

        return self::$instances[$class];
    }

    /**
     * @see \ArrayObject::exchangeArray()
     */
    public function import($input)
    {
        $this->exchangeArray($input);
        return $this;
    }

    /**
     * Return the current object as an array
     * 
     * @return  array
     */
    public function toArray()
    {
        return (array) $this;
    }
}

// ------------------------------

/**
 * EventEmitter
 *
 * @package     Horus
 * @author      Mohammed Al Ashaal
 * @since       11.0.0
 */
Class EventEmitter extends Prototype
{
    /**
     * The maximum listeners per event
     * @var integer
     */
    private $maxListeners =   0;

    /**
     * The listeners array
     * @var array
     */
    private $listeners    =   null;

    /**
     * Adds a listener to the listeners array for the specified event
     * 
     * @param   string      $event      the event name
     * @param   callable    $listener   the listener callback
     * @param   bool        $once       whether to register this listener as a one-time listener or not
     * @return  this
     */
    public function addListener($event, $listener, $once = false)
    {
        $event = strtolower($event);

        if ( ($this->maxListeners > 0) && ! ($this->listenerCount($event) <= $this->maxListeners) ) {
            return $this;
        }

        $this->listeners[$event][] = array($listener, $once);

        return $this;
    }

    /** @see EventEmitter::addListener() */
    public function on($event, $listener, $once = false)
    {
        return call_user_func_array(array($this, 'addListener'), func_get_args());
    }

    /** Alias of Emitter::addListener() but registers a one-time event */
    public function once($event, $listener)
    {
        return $this->addListener($event, $listener, true);
    }

    /**
     * Remove a registered listener from the specified event
     * 
     * @param   string      $event
     * @param   callable    $listener
     * @return  this
     */
    public function removeListener($event, $listener)
    {
        $event = strtolower($event);
        unset($this->listeners[$event][array_search($listener, $this->listeners[$event])]);
        return $this;
    }

    /**
     * Removes all listeners, or those of the specified event
     * 
     * @param   string      $event
     * @return  this
     */
    public function removeAllListeners($event = null)
    {
        if ( empty($event) )
            unset($this->listeners[$event]);
        else
            $this->listeners = array();
        return $this;
    }

    /**
     * Sets the maximum listeners per-event
     * 
     * @param   integer     $n
     * @return  this
     */
    public function setMaxListeners($n)
    {
        $this->maxListeners =   abs((int) $n);
        return $this;
    }

    /**
     * Returns an array of listeners for the specified event
     * 
     * @param   string      $event
     * @return  array
     */
    public function &listeners($event)
    {
        $array = array();

        if ( ! isset($this->listeners[$event = strtolower($event)]) ) {
            return $array;
        } else {
            return $this->listeners[$event];
        }
    }

    /**
     * Return the number of listeners for a given event
     * 
     * @param   string      $event
     * @return  integer
     */
    public function listenerCount($event)
    {
        return sizeof($this->listeners($event));
    }

    /**
     * Execute each of the listeners in order with the supplied arguments
     * 
     * @param   string      $event      the event to emit its listeners
     * @param   mixed       $args       the argument(s) passed to the listeners
     * @param   mixed       $return     the default value to return
     * @return  mixed
     */
    public function emit($event, $args = null, $return = null)
    {
        $event  =   strtolower($event);

        foreach ( $this->listeners($event) as $id => $listener )
        {
            $return = call_user_func_array($listener[0], array_merge((array) $args, array($return)));

            if ( $listener[1] ) {
                unset($this->listeners[$event][$id]);
            }

            if ( $return === false ) {
                return $return;
            }
        }

        return $return;
    }
}

// ------------------------------

/**
 * Util
 *
 * @package     Horus
 * @author      Mohammed Al Ashaal
 * @since       9.0.0
 */
Class Util extends Prototype
{
    /**
     * Enable/Disable error reporting
     * 
     * @param   bool $state
     * @return  this
     */
    public function debug($state = true)
    {
        if ( $state ) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL|E_STRICT);
        } else {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        return $this;
    }

    /**
     * Make a directory classes be lazy-autoloaded
     * 
     * @param   string      $dir
     * @return  Closure
     */
    public function autoload($dir)
    {
        spl_autoload_register($cb = function($class) use($dir)
        {
            $ds     =   DIRECTORY_SEPARATOR;
            $dir    =   trim($dir, $ds);
            $class  =   trim(str_replace(array('/', '\\'), $ds, $class), $ds);

            if ( is_file($file = $dir . $ds . $class . '.php') ) {
                return require $file;
            } elseif ( is_file($file = $dir . $ds . $class . $ds . basename($class) . '.php') ) {
                return require $file;
            }

            return 0;
        });

        return $cb;
    }

    /**
     * Encrypt using mcrypt
     * 
     * @param   mixed   $data   the data to encrypt, supports any type "it will be serialized"
     * @param   string  $key    the secrt key
     * @param   string  $cipher the cipher
     * @param   string  $mode   the mode
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
     * @param   mixed   $data   the data to be decrypted
     * @param   string  $key    the secret key
     * @param   string  $cipher the cipher key
     * @param   string  $mode   the mode
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
     * @uses    crypt()
     * @param   string  $string     string to hash
     * @param   string  $algorithm  [blowfish, md5, sha256, sha512]
     * @return  string
     */
    public function hash($string, $algorithm = 'blowfish')
    {
        switch( strtolower($algorithm) ):
            case('md5'):
                $salt = '$1$'.($this->rand(12)).'$';
                break;
            case('sha256'):
                $salt = '$5$rounds=5000$'.($this->rand(16)).'$';
                break;
            case('sha512'):
                $salt = '$6$rounds=5000$'.($this->rand(16)).'$';
                break;
            case('blowfish'):
            default:
                $salt = '$2a$09$'.($this->rand(22)).'$';
                break;
        endswitch;

        return base64_encode(crypt($string, $salt));
    }

    /**
     * Checks whether a crypted hash is valid
     * 
     * @uses    crypt()
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
     * @param   bool    $special
     * @return  string
     */
    public function rand($length, $special = false)
    {
        $chars  =   'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $special && ($chars .= '!@#$%^&*()+=<>:;*-~`{}[],');
        $chars  =   str_shuffle($chars);
        $ret    =   substr($chars, mt_rand(0, strlen($chars) / 2), $length);

        while ( ($more = ($length - strlen($ret))) > 0 ) {
            $ret .= str_shuffle(substr(str_shuffle($chars), mt_rand(0, strlen($ret) / 2), $length));
        }

        return $ret;        
    }

    /** @ignore */
    public function __call($n, $a)
    {
        if ( isset($this[$n]) && is_callable($this[$n]) ) {
            return call_user_func($this[$n], $a);
        }

        $a   =   array_merge(array(strtolower(str_replace('_', ',', $n))), $a);

        return  call_user_func_array('hash', $a);
    }
}

// ------------------------------

/**
 * Request
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    11.0.0
 */
Class Request extends Prototype
{
    /** Constrcutor */
    public function __construct()
    {
        // i will get the query, body and cookies
        // then create a prototype object from each one
        // then overwrite the main global var with a refrence
        // of the prototype object that constructed for the specified var.
        // We overwrite the global vars with just references because we want
        // a real low memory usage, high performance app .

        $req            =   $this;
        $this->query    =   new Prototype($_GET);
        $this->cookies  =   new Prototype($_COOKIE);
        $this->params   =   new Prototype;
        $this->body     =   call_user_func(function() use($req) {
            $type = strtolower($req->get('content-type'));
            if ( strstr($type, 'application/x-www-form-urlencoded') ) {
                return new Prototype($_POST);
            } elseif ( strstr($type, 'application/json') ) {
                return new Prototype(json_decode(file_get_contents('php://input')));
            } elseif ( strstr($type, 'application/xml') ) {
                return new Prototype(json_decode(json_encode(simplexml_load_string(file_get_contents('php://input')))));
            } else {
                return new Prototype;
            }
        });

        $_GET           =   &$this->query;
        $_POST          =   &$this->body;
        $_COOKIE        =   &$this->cookies;

        // detect ssl/https
        if ( ($proto = strtolower($this->get('X-Forwarded-Proto'))) && $proto == 'https' ) {
            $_SERVER['HTTPS']   =   'on';
        } elseif ( ! isset($_SERVER['HTTPS']) ) {
            $_SERVER['HTTPS']   =   'off';
        } elseif ( empty($_SERVER['HTTPS']) ) {
            $_SERVER['HTTPS']   =   'off';
        } elseif ( !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) !== 'off') ) {
            $_SERVER['HTTPS']   =   'on';
        }

        // enable/disable internal url-rewriter
        if ( ! isset($_SERVER['HORUS_FORCE_REWRITE']) ) {
            $_SERVER['HORUS_FORCE_REWRITE'] = false;
        } else {
            $_SERVER['HORUS_FORCE_REWRITE'] = (bool) (int) $_SERVER['HORUS_FORCE_REWRITE'];
        }

        // the current request method
        $this->method   =   strtoupper($_SERVER['REQUEST_METHOD']);

        // whether the request from XMLHttpRequest "javascript" ajax
        $this->xhr      =   (strtolower($this->get('X-Requested-With')) === strtolower('XMLHttpRequest'));

        // whether the request is secure or not
        $this->secure   =   ($_SERVER['HTTPS'] === 'on');

        // the request prototcol 'http/https'
        $this->protocol =   $this->secure ? 'https' : 'http';

        // the request hostname of the host 'example.com:xxx' --> 'example.com'
        $this->hostname =   strtok($this->get('host'), ':');

        // the path of the request uri '/x/y/z?q=v' --> '/x/y/z'
        $this->path     =   strtok($_SERVER['REQUEST_URI'], '?');

        // the path-info or 'virtual-path' '/index.php/x/y/z/' --> '/x/y/z/'
        $this->vpath    =   call_user_func(function(){
            $e = &$_SERVER;
            $e['REQUEST_URI']   =   '/' . trim($e['REQUEST_URI'], '/');
            $e['SCRIPT_NAME']   =   '/' . trim($e['SCRIPT_NAME'], '/');
            $e['PATH_INFO']     =   '/' . trim(strtok($e['REQUEST_URI'], '?'), '/') . '/';
            if ( ($pos = stripos($e['PATH_INFO'], $s = $e['SCRIPT_NAME'])) === 0 || ($pos = stripos($e['PATH_INFO'], $s = dirname($e['SCRIPT_NAME']))) === 0 ) {
                $e['PATH_INFO'] =   substr($e['PATH_INFO'], strlen($s));
            }
            $e['PATH_INFO']     =   preg_replace('/\/+/', '/', '/' . ( $e['PATH_INFO'] ) . '/');
            return $e['PATH_INFO'];
        });

        // the current matched route haystack '//{hostname}/{vpath}'
        $hostname       =   (stripos($n = $this->hostname, 'www.') === 0) ? substr($n, 4) : $n;
        $this->route    =   "//{$hostname}{$this->vpath}";

        // whether to force internal url-rewrite or not
        $this->forceURW =   !empty($_SERVER['HORUS_FORCE_REWRITE']);

        // the main url [real & virtual]
        $url            =   $this->protocol . '://' . $_SERVER['SERVER_NAME'] . '/';
        $this->url      =   $url . preg_replace('/\/+/', '/', ltrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/');
        $this->vurl     =   $this->forceURW ? ($url . preg_replace('/\/+/', '/', ltrim($_SERVER['SCRIPT_NAME'], '/') . '/')) : $this->url;
    }

    /**
     * Returns the specified HTTP request header field (case-insensitive match) 
     * 
     * @param   string  $field
     * @return  string
     */
    public function get($field)
    {
        $field = 'HTTP_' . strtoupper(str_replace('-', '_', $field));
        return isset($_SERVER[$field]) ? $_SERVER[$field] : "";
    }
}

// ------------------------------

/**
 * Response
 *  
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    11.0.0
 */
Class Response
{
    /** @ignore */
    private $status, $req;

    /** Constructor */
    public function __construct(Request $req)
    {
        ob_start();
        $this->req      =   $req;
        $this->status   =   200;
    }

    /**
     * Sets the response’s HTTP header field to the specified value, you can pass multiple field => value as array to the first argument .
     * 
     * @param   string|array    $field
     * @param   string|array    $value
     * @return  this
     */
    public function set($field, $value = "")
    {
        if ( is_array($field) ) {
            foreach ( $field as $f => $v ) {
                $this->set($f, $v);
            }
            return $this;
        }

        $field  =   str_replace(' ', '-', ucwords(strtolower(str_replace(array('-', '_'), ' ', $field))));
        $value  =   is_array($value) ? implode('; ', $value) : $value;

        header("{$field}: {$value}", true, $this->status);

        return $this;
    }

    /**
     * Appends the specified value to the specified HTTP response header field, you can pass multiple as field => value pairs in the first argument .
     * 
     * @param   string|array    $field
     * @param   string|array    $value
     * @return  this
     */
    public function append($field, $value = "")
    {
        if ( is_array($field) ) {
            foreach ( $field as $f => $v ) {
                $this->append($f, $v);
            }
            return $this;
        }

        $field  =   str_replace(' ', '-', ucwords(strtolower(str_replace(array('-', '_'), ' ', $field))));
        $value  =   is_array($value) ? implode('; ', $value) : $value;

        header("{$field}: {$value}", false, $this->status);

        return $this;
    }

    /**
     * Set the response’s HTTP status code 
     * 
     * @param   integer     $code
     * @return  this
     */
    public function status($code)
    {
        $this->status = (int) $code;
        $this->set('x-status', $code);
        return $this;
    }

    /**
     * Send a message to the client
     * 
     * @param   mixed  $message
     * @return  this
     */
    public function send($message)
    {
        if ( is_array($message) || is_object($message) ) {
            return $this->json($message);
        }

        echo $message;

        return $this;
    }

    /**
     * Send a json response
     * 
     * @param   mixed   $data
     * @return  this
     */
    public function json($data)
    {
        $this->set('content-type', 'application/json; charset=UTF-8');
        echo json_encode($data);
        return $this;
    }

    /**
     * Send a json response that supports jsop
     * 
     * @param   mixed   $data
     * @param   string  $callback
     * @return  this
     */
    public function jsonp($data, $callback = 'callback')
    {
        $this->set('content-type', 'application/javascript; charset=UTF-8');
        printf('%s(%s);', $callback, json_encode($data));
        return $this;
    }

    /**
     * Send an xml data to the HTTP response
     * 
     * @param   array   $data
     * @param   string  $version
     * @param   string  $encoding
     * @return  this
     */
    public function xml(array $data, $version = "1.0", $encoding = "UTF-8")
    {
        $nodes = function(array $data) use(&$nodes) {
            foreach ( $data as $key => $val ) {
                if ( is_array($val) && ! is_int($key) ) {
                    if ( !empty($val['__attr']) && is_array($val['__attr']) ) {
                        $attrs = "";
                        foreach ( $val['__attr'] as $k => $v ) {
                            $attrs .= " {$k}='{$v}' ";
                        }
                        unset($val['__attr']);
                    } else {
                        $attrs = "";
                    }
                    echo "<{$key} {$attrs}>";
                    $nodes($val);
                    echo "</{$key}>";
                } elseif ( is_int($key) ) {
                    $nodes($val);
                }else {
                    echo "<{$key}>{$val}</{$key}>";
                }
            }
        };

        $this->set('content-type', 'text/xml; charset="'.($encoding).'"');
        echo("<?xml version='{$version}' encoding='{$encoding}'?>");
        $nodes($data);

        return $this;
    }

    /**
     * Send a cookie
     * 
     * @param   string  $name
     * @param   string  $value
     * @param   array   $options
     * @return  this
     */
    public function cookie($name, $value = null, array $options = array())
    {
        $options    =   array_merge(array(
            'domain'    =>  null,
            'path'      =>  '/' . trim(dirname($_SERVER['SCRIPT_NAME']), '/'),
            'expires'   =>  0,
            'secure'    =>  $this->req->secure,
            'httpOnly'  =>  true
        ), $options);

        setcookie (
            $name,
            $value,
            (int) $options['expires'], 
            $options['path'],
            $options['domain'],
            $options['secure'], 
            $options['httpOnly']
        );

        return $this;
    }

    /**
     * Send a last-modified header and cache the page for "ttl" time
     * 
     * @param   integer     $ttl
     * @return  this
     */
    public function cache($ttl)
    {
        $last_mod = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? (int) strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;

        if ( (time() - $last_mod) < $ttl ) {
            $this->status(304)->clear()->halt();
            return $this;
        }

        $this->set('last-modified', gmdate('D, d M Y H:i:s T', time() + $ttl));

        return $this;
    }

    /**
     * Send expires header
     * 
     * @param   integer     $when
     * @return  this
     */
    public function expires($when)
    {
        $this->set('expires', gmdate('D, d M Y H:i:s T', $when));
        return $this;
    }

    /**
     * Send file(s) to the output
     * 
     * @param   string|array    $filename
     * @param   array           $scope
     * @return  this
     */
    public function render($filename, array $scope = array())
    {
        extract($scope, EXTR_OVERWRITE|EXTR_REFS);

        foreach ( (array) $filename as $_____ ) {
            if ( is_file($_____) ) {
                require $_____;
            }
        }

        return $this;
    }

    /**
     * Redirect to another page
     * 
     * @param   string  $target
     * @param   bool    $permenant
     * @return  this
     */
    public function redirect($target, $permenant = false)
    {
        $code = $permenant ? 301 : 302;

        $this->set('location', $target)->clear()->status($code)->halt();

        return $this;
    }

    /**
     * Clear the response
     * 
     * @return  this
     */
    public function clear()
    {
        ob_clean();
        return $this;
    }

    /**
     * Halt the response cycle
     * 
     * @param   integer $status     the status code 'optional'
     * @param   mixed   $message    the message to send 'optional'
     * @return  void
     */
    public function halt($status = null, $message = null)
    {
        if ( $status ) {
            $this->status($status);
        }

        if ( $message ) {
            $this->clear()->send($message);
        }

        Horus::instance()->run();
    }
}

// -------------------------------

/**
 * Horus
 *
 * @package     Horus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 */
Class Horus extends EventEmitter
{
    /** Constructor */
    public function __construct()
    {
        // start the parent
        parent::__construct();

        // default properties
        $this->locals   =   new Prototype;
        $this->util     =   new Util;
        $this->req      =   new Request;
        $this->res      =   new Response($this->req);
        $this->base     =   "//" . ((stripos($n = $_SERVER['SERVER_NAME'], 'www.') === 0) ? substr($n, 4) : $n) . '/';
        $this->found    =   false;
        $this->wait     =   false;
        $app            =   $this;

        // default 404 handler
        $this->e404 = function() use($app) {
            $app->res->clear();
            $app->res->status(404);
            if ( $app->req->method == 'HEAD' ) {
                return ;
            }
            print "<title>404 - Horus</title>";
            print "<h1>404 page not found</h1>";
            print "<p>The requested object cannot found '".($app->req->route)."'</p>";
        };

        // use internal re-write if needed
        if ( $this->req->forceURW && (stripos($this->req->path, $_SERVER['SCRIPT_NAME'] .'/') === false) ) {
            $this->res->redirect($this->req->vurl);
        }

        // the default headers
        $this->res->set(array(
            'x-powered-by'              =>  'Horus/11',
            'content-type'              =>  'text/html; charset=utf-8',
            'x-frame-options'           =>  'SAMEORIGIN',
            'X-XSS-Protection'          =>  '1; mode=block',
            'X-Content-Type-Options'    =>  'nosniff'
        ));

        // default timezone
        date_default_timezone_set('UTC');
    }

    /**
     * Normalize the pattern
     * 
     * @param   string  $pattern
     * @return  string
     */
    public function pattern($pattern)
    {
        // the base must be valid one "//servername/[path]"
        if ( strpos($this->base, '//') !== 0 ) {
            $this->base = "//" . ((stripos($n = $_SERVER['SERVER_NAME'], 'www.') === 0) ? substr($n, 4) : $n) . '/';
        }

        // also make sure that the
        if ( strpos($pattern, '//') !== 0 ) {
            $pattern = $this->base . ltrim($pattern, '/');
        }

        // wildcards ?
        $pattern    =   str_replace('/*', '/?(.*?)', $pattern);

        // remove the duplicated slashes
        $pattern    =   "/" . preg_replace('/\/+/', '/', $pattern . '/');

        // extract our named-regex "our syntax"
        $matches = preg_match_all("/\{([^\/\{\}]+)\}/i", $pattern, $m);

        // normalise each named-regex
        if ( $matches )
        {
            foreach ( $m[1] as $i => $v ) {
                $name   =   $v;
                $regex  =   "[^\/]+";
                if ( strstr($v, ":") ) {
                    $name   =   substr($v, 0, $pos = strpos($name, ':'));
                    $regex  =   substr($v, $pos + 1);
                }
                $name   =   strtolower($name);
                $m[1][$i]  =   "(?<{$name}>{$regex})";
            }
    
            // now replace our syntax with the normalized one
            $pattern    =   str_replace($m[0], $m[1], $pattern);
        }

        // and finaly escape any slashes and remove duplicates
        return  preg_replace("/\\\\+/", '\\', addcslashes($pattern, '/'));
    }

    /**
     * whether the current route matches the specified pattern or not
     * 
     * @param   string  $pattern
     * @param   bool    $strict
     * @return  \stdClass
     */
    public function matches($pattern, $strict = true)
    {
        $return         =   new \stdClass;
        $return->ok     =   false;
        $return->params =   array();

        if ( trim($pattern) === '*' ) {
            $return->ok = true;
            return $return;
        }

        $pattern        =   $this->pattern($pattern);

        //var_dump($pattern);

        if ( preg_match("/^{$pattern}".($strict ? "$" : "")."/", $this->req->route, $m) ) {
            array_shift($m);
            $return->ok     =   true;
            $return->params =   $m;
        }

        return $return;
    }

    /**
     * Route a group of routes
     * 
     * @param   string|array    $base
     * @param   callable        $listener
     * @return  this
     */
    public function group($base, $listener)
    {
        if ( is_array($base) ) {
            foreach ( $base as $b ) {
                call_user_func(array($this, __FUNCTION__), $b, $listener);
            }
            return $this;
        }

        $old        =   $this->base;
        $this->base =   stripcslashes($this->pattern($base));
        $is         =   $this->matches($this->base, false);

        if ( $is->ok ) {
            $listener($this->req, $this->res, $this);
        }

        $this->base =   $old;

        return $this;
    }

    /**
     * Just a shortcut
     * 
     * @param   string  $method
     * @param   array   $args
     * @return  this
     * 
     * @ignore
     */
    public function __call($method, $args)
    {
        if ( isset($this[$method]) && is_callable($this[$method]) ) {
            return call_user_func_array($this[$method], $args);
        }

        $method = str_replace('_', '|', $method);

        return call_user_func_array(array($this, 'handle'), array_merge(array($method), $args));
    }

    /**
     * Run the horus application
     * 
     * @return void
     */
    public function run()
    {
        if ( ! $this->found ) {
            $this->e404();
        }

        $output = $this->emit('horus.output', $o = ob_get_clean(), $o);

        ($this->req->method !== 'HEAD') && (print $output);

        ob_end_flush();

        exit;
    }

    /**
     * Register a route handler
     * 
     * @param   string          $method
     * @param   string|array    $pattern
     * @param   callable        $listener
     * @param   bool $strict
     * @return  this
     * 
     * @ignore
     */
    protected function handle($method, $pattern, $listener, $strict = true)
    {
        if ( is_array($pattern) ) {
            foreach ( $pattern as $p ) {
                call_user_func(array($this, __FUNCTION__), $method, $p, $listener, $strict);
            }
            return $this;
        }

        $this->wait =   false;
        $method     =   strtolower($method);

        if ( $method === 'all' ) {
            $method = $this->req->method;
        } elseif ( stripos($method, '_') !== false ) {
            $method = '('.(str_replace('_', '|', $method)).')';
        }

        if ( ! preg_match("/{$method}/i", $this->req->method) ) {
            return $this;
        }

        $is     =   $this->matches($pattern, $strict);

        if ( $is->ok ) {
            $this->req->params->import($is->params);
            $listener($this->req, $this->res, $this);
            $this->found    =   true;

            if ( ! $this->wait ) {
                $this->res->halt();
            }
        }

        return $this;
    }
}
