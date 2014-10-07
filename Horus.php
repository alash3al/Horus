<?php while(ob_get_status()) ob_end_clean();
/**
 * Horus - a tiny & portable PHP 5 framework
 *
 * @author      Mohammed Al Ashaal <m7medalash3al@gmail.com|fb.com/alash3al>
 * @copyright   2014 Mohammed Al Ashaal
 * @link        http://alash3al.github.io/Horus
 * @license     MIT LICENSE
 * @version     7.0.0
 * @package     Horus
 */

// ------------------------------------------

/**
 * Container
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    5.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
Class Horus_Container implements ArrayAccess, Countable, IteratorAggregate, Serializable
{
    /**
     * @var array
     */
    public $data = array();

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
        $this->data = $data;
    }

    /**
     * Get stored value
     * @param   string  $key
     * @param   string  $default
     * @return  mixed
     */
    public function _get($key, $default = null)
    {
        if(isset($this->data[$key])) return $this->data[$key];
        else return $default;
    }

    /**
     * Store a key's value
     * @param   string  $key
     * @param   mixed   $value
     * @return  void
     */
    public function _set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if the store has a certain key
     * @param   string $key
     * @return  bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a key from the store
     * @param mixed $key
     * @return
     */
    function remove($key)
    {
        unset($this->data[$key]);
    }

    /** @ignore */
    public function __call($name, $args)
    {
        if(!isset($this->data[$name]) or !is_callable($this->data[$name])) {
            throw new InvalidArgumentException('Call to undefined method '.__CLASS__.'::'.$name.'()');
        } else return call_user_func_array($this->data[$name], $args);
    }

    /** @ignore */
    public function __get($key)
    {
        return $this->_get($key);
    }

    /** @ignore */
    public function __set($key, $value)
    {
        $this->_set($key, $value);
    }

    /** @ignore */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /** @ignore */
    public function __unset($key)
    {
        return $this->remove($key);
    }

    /** @ignore */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /** @ignore */
    public function offsetGet($offset)
    {
        return $this->_get($offset);
    }

    /** @ignore */
    public function offsetSet($offset, $value)
    {
        $this->_set($offset, $value);
    }

    /** @ignore */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /** @ignore */
    public function count()
    {
        return count($this->data);
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
}

// -------------------------------

/**
 * Horus Router
 * @package  Horus
 * @author   Mohammed Al Ashaal
 * @since    7.0.0
 */
Class Horus_Router
{
    /**
     * The current assigned group .
     * @var string
     */
    protected $group = '';

    /**
     * The routes array & regex-vars.
     * @var array
     */
    public $routes, $vars = array();

    /**
     * Current request state & output
     * @var bool
     */
    public $state, $output = false;

    /**
     * Constructor
     */
    public function __construct()
    {
		$this->vars = array (
            '@num'      =>  '([0-9\.,]+)',
            '@alpha'    =>  '([a-zA-Z]+)',
            '@alnum'    =>  '([a-zA-Z0-9\.\w]+)',
            '@str'      =>  '([a-zA-Z0-9-_\.\w]+)',
            '@any'      =>  '([^\/]+)',
            '@*'        =>  '?(.*)',
            '@date'     =>  '(([0-9]+)\/([0-9]{2,2}+)\/([0-9]{2,2}+))',
            '@null'     =>  '^',
            '@domain'   =>  sprintf('(%s)', addcslashes($_SERVER['DEFAULT_DOMAIN'], './'))
        );
        $this->group = sprintf('//%s/', $_SERVER['DEFAULT_DOMAIN']);
    }

    /**
     * Group routes under a parent pattern
     * @param   string      $group
     * @param   callable    $callable
     * @return  Horus_Router
     */
    public function group($group, $callable = null)
    {
        // trim '/' from left and right
        $trimed = rtrim(ltrim($group, '/'), '/');

        // detect whether domain/path routing group
        // and force it to be domain routing if not .
        if ( strpos($group, '//') === 0 )
            $this->group = sprintf('//%s/', $trimed);
        else
            $this->group = '//' . $_SERVER['DEFAULT_DOMAIN'] . '/' . $trimed . '/';

        // if the callable is file create a callable, then call it anyway .
        $callable = (!is_callable($callable) && is_file($callable)) ? create_function('$f = ' . var_export($callable,1), 'include "$f";') : $callable;
        call_user_func($callable);

        // reset the group to default
        $this->group = sprintf('//%s/', $_SERVER['DEFAULT_DOMAIN']);

        // return this object again [for method chaining]
        return $this;
    }

	/**
	 * Serve a new route
	 * @param  string      $method
	 * @param  string      $pattern
	 * @param  callable    $callable
	 * @return bool
	 */
	public function on($method, $pattern, $callable)
    {
        // do not do anything in case of the $state is true
        if ( $this->state ) return ;

        // allow multiple methods routing
        if ( is_array($method) ) {
            foreach ( $method as &$m )
                call_user_func(array($this, __FUNCTION__), $m, $pattern, $callable);
            return $this;
        }
        else $method = strtoupper($method);

        // the method [any] means dispatched on any request-method
        // so it means run on the curent request method
        $method = ($method === 'ANY') ? ($_SERVER['REQUEST_METHOD'] = strtoupper($_SERVER['REQUEST_METHOD'])) : $method;

        // must has valid callable
        if ( ! (list($callable, $callback_type) = $this->getCallable($callable)) )
            throw new Exception("Callable of ' {$method} -> {$pattern} ' is not valid");

        // the pattern
        $trimed = rtrim(ltrim($pattern, '/'), '/');
        if ( strpos($pattern, '//') === 0 )
            $pattern = '//' . $trimed . '/';
        else
            $pattern = rtrim($this->group . $trimed, '/') . '/';

        // escape regex-chars
        $pattern = str_replace('\\\\', '\\', addcslashes(str_ireplace(array_keys($this->vars), array_values($this->vars), $pattern), './'));
        $patternR = "/^{$pattern}".($callback_type == 'normal' ? '$' : '')."/";

        // the haystack of current request
        $haystack = '//' . $_SERVER['SERVER_NAME'] . $_SERVER['PATH_INFO'];
        //(var_dump($pattern, $haystack));

        // apply it if matched current request
        if ( ($method == $_SERVER['REQUEST_METHOD']) && preg_match($patternR, $haystack, $args) ) {
            $this->state = true;
            array_shift($args); ob_start();
            $x = $this->callRouteCallback($haystack, $pattern, $callback_type, $callable, $args);
            $this->output = ob_get_clean();
            return !($x === false);
        }
        else return false;
    }

    /**
     * Call a route callable
     * @return  bool
     */
    protected function callRouteCallback($haystack, $pattern, $type, $callable, $args)
    {
        // the status [successed or failed]
        $status = false;

        // is it a class
        if ( ($_1 = ($type == 'class:name')) || ($_2 = ($type == 'class:object')) )
        {
            // create/get the class object
            $callable = ($_1 ? (new $callable) : $callable);

            // the default method [home]
            $method = 'index';

            // get request method with args
            $path = preg_replace("/^{$pattern}/i", '', $haystack);
            $path = rtrim(ltrim($path, '/'), '/');
            $parts = (array) explode('/', $path);
            $method = empty($parts[0]) ? 'index' : $parts[0];
            $method = str_replace('-', '_', $method);
            $args = array_slice($parts, 1);

            // set the calling status
            if ( is_callable(array($callable, $method)) && $method[0] !== '_' ) {
                call_user_func_array(array($callable, $method), $args);
                $status = true;
            }

            // end
            return $status;
        }

        // or a basic callable
        elseif ($type == 'normal')
        {
            call_user_func_array($callable, (array) $args);
            $status = true;
        }

        // end
        return $status;
    }

    /**
     * Returns callable and its type {class, class_object, normal}
     * @param   mixed $callable
     * @return  false | array
     */
    protected function getCallable($callable)
    {
        if ( is_callable($callable) )
            $callback_type = 'normal';
        elseif ( is_string($callable) && is_file($callable) && is_readable($callable) )
            ($callback_type = 'normal') && ($callable = create_function('', "\$args = func_get_args(); include '{$callable}';"));
        elseif (is_string($callable) && class_exists($callable))
            $callback_type = 'class:name';
        elseif ( is_object($callable) && class_exists(get_class($callable)) )
            $callback_type = 'class:object';
        else
            return false;

        return array($callable, $callback_type);
    }

    /**
     * Magic method used to trigger any required route easily
     * @ignore
     */
    public function __call($method, $args)
    {
        // convert _ to array
        $method = strpos($method, '_') !== false ? array_filter(explode('_', $method)) : $method;

        // add the method name to start
        array_unshift($args, $method);

        // call on() method
        return call_user_func_array (
            array($this, 'on'),
            $args
        );
    }
}

// -------------------------------

/**
 * SQL Manager
 * A simple yet powerful sql framework that extends PDO
 * and adds some new features to make code simple .
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    4.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_SQL extends PDO
{
    /** @ignore */
    protected $stmnt, $driver, $database, $connected;

    /** @ignore */
    public function __construct(){}

    /**
     * Construtor
     * @param string    $dns
     * @param string    $username
     * @param string    $password
     * @param array     $driver_options
     * @return Object
     */
    public function connect($dns, $username = null, $password = null, array $driver_options = array())
    {
        parent::__construct($dns, $username, $password, $driver_options);
        $this->_setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $this;
    }

    /**
     * Create a new instance of the class
     * @return  Horus_SQL
     */
    public function newInstance()
    {
        return new self();
    }

    /**
     * Execute sql statement
     * This is prepare + execute
     * @param   string    $statement
     * @param   mixed     $inputs
     * @return  False | object
     */
    public function query($statement, $inputs = null)
    {
        $this->stmnt = $this->prepare($statement);
        if(!is_object($this->stmnt) && ! $this->stmnt instanceof PDOStatement) return false;
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
 * Advanced Extensible Horus Controller
 * @package     Horus 7
 * @author      Mohammed Al Ashaal
 * @copyright   2014
 * @version     1.0
 */
Abstract Class Horus_Controller extends Horus_Container
{
    /**
     * Controller Constructor
     * @return Horus_Controller
     */
    final public function __construct()
    {
        parent::__construct();
        $this->horus = horus();
        $this->__init();
    }

    /**
     * Works just like an constructor
     * @return void
     */
    public function __init(){}

    /**
     * Works just like __call()
     * @return void
     */
    public function __caller($name, $args){}

    /**
     * Here is the magic of extensible controllers
     * @ignore
     */
    public function __call($n, $a)
    {
        $this->__caller($n, $a);
        if ( ! isset($this->data[$n]) ) {
            $this->horus->e404();
            return ;
        }
        parent::__call($n, $a);
    }

    /**
     * Destructor
     * @return void
     */
    public function __destruct()
    {
        $this->data = array();
    }

    /**
     * Abstracted index method
     * @return void
     */
    abstract public function index();
}

// -------------------------------

/**
 * Horus kernel
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
Class Horus extends Horus_Container
{
    /**
     * The output stored here
     * @var string
     */
    protected $output = null;

    /**
     * events/hooks array
     * @var array
     */
    public $events = array();

    /**
     * Horus instance stored here
     * @var object
     */
    protected static $instance;

    /**
     * @const string
     */
    const VERSION = '7.0.0';

    /**
     * Constructor
     * @return  void
     */
    public function __construct()
    {
        // is PHP 5 >= PHP 5.2.17
        if(!version_compare(phpversion(), '5.2.17', '>=')) die('Sorrty i need PHP at least 5.2.17');

        // starting horus timer
        define('HORUS_START_TIME', microtime(1), true);
        define('HORUS_START_PEAK_MEM', memory_get_peak_usage(true));
        define('HORUS_VERSION', self::VERSION);

        // unset some global vars
        unset 
        (
            $HTTP_COOKIE_VARS, $HTTP_SESSION_VARS, $HTTP_SERVER_VARS, 
            $HTTP_POST_VARS, $HTTP_POST_FILES, $HTTP_GET_VARS, $HTTP_ENV_VARS
        );

        // construct the container
        parent::__construct();

        // get the any output buffer
        // add it to our output, then clean & start new
        ob_start();

        // environment vars
        $this->setEnv();

        // some php ini settings
        ini_set('session.hash_function',    1);
        ini_set('session.use_only_cookies', 1);
        session_name('HORUSSESID');

        // set some constants
        defined('ROUTE')    or define('ROUTE', $_SERVER['ROUTE'], true);
        defined('URL')      or define('URL', $_SERVER['URL'], true);
        defined('DS') 		or define('DS', DIRECTORY_SEPARATOR, true);
        defined('BASEPATH') or define('BASEPATH', realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . DS, true);
        defined('COREPATH') or define('COREPATH', realpath(dirname(__FILE__)) . DS, false);

        // some other methods
        $this->e404 = create_function
        ('', 
        '
            @ob_get_clean();
            die
            ("
                <!DOCTYPE HTML>
                <html>
                    <head><title>404 not found</title></head>
                    <body style=\'margin: auto; padding: 15px; max-width: 400px; text-align: center; margin-top: 15%\'>
                        <h1>404 not found</h1>
                        <p> The requested URL was not found on this server . </p>
                    </body>
                </html>
            ");
        ');

        // store the instance
        self::$instance = $this;
    }

    /**
     * Run the horus application
     * @return void
     */
    public function run()
    {
        // dispatch all routes [between events]
        $this->trigger('horus.dispatch.before');

        // execute/disptach all routes relates to current request
        if ( isset($this->router) && $this->router instanceof Horus_Router )
        {
            if($this->router->state == false)
                $this->e404();
            else
                $this->output .= $this->router->output;
        }

        // trigger after router exec events
        $this->trigger('horus.dispatch.after');

        // get the output
        // prepare then only send if the request is not head
        $this->output .= ob_get_clean();

        // trigger before output events
        $this->trigger('horus.output.before', array($this));

        // trigger output filters
        $this->output  = $this->trigger('horus.output.filter', $this->output, $this->output);

        // only send output if not HEAD request
        (strtoupper($_SERVER['REQUEST_METHOD']) == 'HEAD') || (print $this->output);

        // trigger after output events
        $this->trigger('horus.output.after', array($this));

        // end
        @ob_end_flush(); exit;
    }

    /**
     * Get horus instance
     * @return  object
     */
    public static function &instance()
    {
        (! empty(self::$instance)) or (self::$instance = new self());
        return self::$instance;
    }

    /**
     * Listen an event
     * @param   string      $tag
     * @param   callback    $callback
     * @param   integer     $prority
     * @return  void
     */
    public function listen($tag, $callback, $prority = 0)
    {
        $this->events[$tag][$prority][] = $callback;
        ksort($this->events[$tag]);
    }

    /**
     * Trigger event(s)
     * @param   string  $tag
     * @param   mixed   $params
     * @return  mixed
     */
    public function trigger($tag, $params = null, $default = null)
    {
        if(isset($this->events[$tag])) 
        {
            $filtered = null;

            foreach(new ArrayIterator($this->events[$tag]) as $p => $callbacks) 
            {
                  foreach($callbacks as $id => &$callback)
                        if(is_callable($callback)) 
                            $filtered = call_user_func_array($callback, array_merge((array) $params, array($filtered)));
            }

            return empty($filtered) ? $default : $filtered;
        }

        return $default;
    }

    /**
     * Access any input key from any request method
     * @param   mixed       $needle
     * @param   mixed       $default
     * @param   callable    $callable
     * @return  mixed
     */
    public function input($needle = '', $default = FALSE, $filter = null)
    {
        $needle = ( empty($needle) ? $_REQUEST : (isset($_REQUEST[$needle]) ? $_REQUEST[$needle] : $default) );
        return is_callable($filter) ? (is_array($needle) ? array_walk_recursive($needle, $filter) : $filter($needle)) : $needle;
    }

    /**
     * Register a driectory [and extension] to be autoloaded .
     * @param   string $path
     * @param   string $extension
     * @return  lambada
     */
    public function &autoload($path, $extension = 'php')
    {
        $params = '$class, $g = '.var_export(array( realpath($path) . DS, "." . ltrim($extension, '.') ), true);

        spl_autoload_register($func = create_function($params, '
            list($path, $extension) = $g;
            $class = rtrim(ltrim(str_replace(array("\\\\", "/", "_"), DS, $class), DS), DS);
            if(!is_file($file = $path . $class . $extension)) {
                $file = $path . $class . DS . basename($class) . $extension;
            }
            if(is_file($file)) include_once $file;
        '));

        return $func;
    }

    /**
     * Smart redirect method
     * @since   version 1.0
     * @param   string  $to
     * @param   integer $using
     * @return  void
     */
    public function go($to, $using = 302)
    {
        $scheme = parse_url($to, PHP_URL_SCHEME);
        $to = (!empty($scheme) ? $to : (ROUTE . ltrim($to, '/')));

        if(headers_sent()) return call_user_func_array(__FUNCTION__, array($to, 'html'));

        @list($using, $after) = (array) explode(':', $using);

        switch(strtolower($using)):
            case 'html':
                echo('<meta http-equiv="refresh" content="'.(int) $after.'; URL='.$to.'">');
                break;
            case 'js':
                echo('<script type="text/javascript">setTimeout(function(){window.location="'.$to.'";}, '.(((int) $after)*1000).');</script>');
                break;
            default:
                exit(header("Location: {$to}", true, $using));
        endswitch;
    }

    /**
     * Debug (show errors) or not ?
     * @param   bool $state
     * @return  void
     */
    public function debug($state = TRUE)
    {
        if($state) error_reporting(E_ALL);
        else error_reporting(0);
    }

    /**
     * Returns some statics
     * @return  object[float]
     */
    public function statics()
    {
        $statics = array();
        $statics['time']    =   (float) round(microtime(1) - HORUS_START_TIME, 4);
        $statics['mem']     =   (float) (memory_get_usage(1) / 1024);
        $statics['pmem']    =   (float) (memory_get_peak_usage(1) - HORUS_START_PEAK_MEM);

        // the next cpu code from here
        // <http://php.net/manual/en/function.sys-getloadavg.php#107243>
        if (stristr(PHP_OS, 'win'))
        {
            $wmi = new COM("Winmgmts://");
            $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");
            $cpu_num = 0;
            $load_total = 0;

            foreach($server as $cpu){
                ++$cpu_num;
                $load_total += $cpu->loadpercentage;
            }
            $statics['cpu'] = round($load_total/$cpu_num);
        }
        else
        {
            $sys_load = sys_getloadavg();
            $statics['cpu'] = $sys_load[0];
        }

        return (object) $statics;
    }


    /**
     * Set Some of environment vars
     * @return Horus
     */
    protected function setEnv()
    {
        // this function called before ?
        static $called = false;

        // prevent for calling multiple times
        if ( $called ) return; $called = true;

        // Create a ref. of _SERVER array
        // to simplify coding ^_*
        $s = &$_SERVER;

        // Set some environment vars
        $s['DEFAULT_SCHEME']    =   empty($s['DEFAULT_SCHEME']) ? 'http' : $s['DEFAULT_SCHEME'];        
        $s['DEFAULT_DOMAIN']    =   (empty($s['DEFAULT_DOMAIN']) ? $s['SERVER_NAME'] : $s['DEFAULT_DOMAIN']);
        $s['SIMULATOR']         =   (empty($s['SIMULATOR']) ? ($s['SIMULATOR'] = 'on') : strtolower($s['SIMULATOR']));
        $s['SIMULATED']         =   isset($s['PATH_INFO']);
        $s['URL']               =   $s['DEFAULT_SCHEME'] . '://' . $s['DEFAULT_DOMAIN'] . '/' . rtrim(ltrim(dirname($s['SCRIPT_NAME']), '/\\'), '\\/') . '/';
        $s['ROUTE']             =   $s['URL'] . (($s['SIMULATOR'] == 'on') ? 'index.php/' : '');
        $s['QUERY_STRING']      =   str_replace(array("\0", chr(0), '%00'), '', ($s['QUERY_STRING']));
        $s['REQUEST_METHOD']    =   strtoupper($s['REQUEST_METHOD']);

        // simulator started ?
        if ( $s['SIMULATOR'] == 'on' )
            $s['SIMULATED'] || header("Location: {$s['ROUTE']}", TRUE, 302);

        // update the path_info
        $s['PATH_INFO'] = empty($s['PATH_INFO']) ? '/' : preg_replace('/\/+/', '/', ('/'.(rtrim(ltrim($s['PATH_INFO'], '/'), '/')).'/'));
        $s['PATH_INFO'] = str_replace(array("\0", chr(0), '%00'), '', ($s['PATH_INFO']));

        // disable libxml errors
        libxml_use_internal_errors(TRUE);

        // Read header_inputs
        $_INPUT = array();
        $i =    str_replace(array("\0", chr(0), '%00'), '', rawurldecode(file_get_contents('php://input')));

        // then parse it and detect whether it is
        // basic_string, json or xml
        if ( is_array($t = json_decode($i, true)) && $t != FALSE )
            $_INPUT = &$t;
        elseif ( ($t = simplexml_load_string($i)) && $t != FALSE )
             $_INPUT = &$t;
        else 
            parse_str($i, $_INPUT);

        // _POST ?
        $_POST = &$_INPUT;

        // Read query_string
        // then parse it and detect whether it is
        // basic_string, json or xml
        $decoded = rawurldecode($s['QUERY_STRING']);
        if (is_array($x = json_decode($decoded, true)) && $x != FALSE )
            $_GET = &$x;
        elseif ( ($x = simplexml_load_string($decoded)) && $x != FALSE  )
             $_GET = &$x;
        else 
            parse_str($s['QUERY_STRING'], $_GET);

        // enable libxml errors again
        libxml_use_internal_errors(FALSE);

        // Set the requests arrays [get, post, request]
        $_GET       =   (array) $_GET;
        $_POST      =   (array) $_POST;
        $_REQUEST   =   (array) array_merge($_GET, $_POST, (array) $_INPUT);

        // Set environment headers
        header('Content-Type: text/html; charset=UTF-8', TRUE);
        header('X-Powered-By: HORUS/PHP', TRUE);
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0", TRUE);
		header("Cache-Control: post-check=0, pre-check=0", TRUE);
		header("Pragma: no-cache", TRUE);
        header("X-Frame-Options: SAMEORIGIN", TRUE);
        header("X-XSS-Protection: 1; mode=block", TRUE);
        header("X-Content-Type-Options: nosniff", TRUE);

        // free memory from some vars
        unset($s, $i, $x, $t, $_INPUT, $path);

        return $this;
    }

    /** @ignore */
    public function __get($k)
    {
        // lazy initializeing ...

        // router
        if ( $k == 'router' ) {
            if ( empty($this->data['router']) )
                $this->router = new Horus_Router;
            return $this->data['router'];
        }

        // sql
        elseif ( $k == 'sql' ) {
            if ( empty($this->data['sql']) )
                $this->sql = new Horus_SQL;
            return $this->data['sql'];
        }

        // continue the default getter
        return parent::__get($k);
    }

    /** @ignore */
    public function &__invoke($k)
    {
        static $horus;
        $horus = empty($horus) ? Horus::instance() : $horus;
        $k = str_replace(array(' ', '-', '.'), '_', $k);   
        $k = (!isset($horus->{$k}) or empty($k)) ? $horus : $horus->{$k};
        unset($horus); return $k;
    }

    /** @ignore */
    public function &__toString()
    {
        $this->run();
    }
}

// ------------------------------

/**
 * Horus object
 * @param   string $k
 * @return  mixed
 */
function &Horus($k = null)
{
    static $horus;

    $horus = empty($horus) ? Horus::instance() : $horus;

    $k = str_replace(array(' ', '-', '.'), '_', $k);   
    $k = (!isset($horus->{$k})) ? $horus : $horus->{$k};

    return $k;
}
