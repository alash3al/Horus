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
 * Horus Framework Kernel
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus
{
    /** @ignore */
    protected static $instance;
    /** @ignore */
    protected $started = false;
    /** @ignore */
    protected $ran = false;
    /** @ignore */
    protected $configs = array();
    /** @ignore */
    protected $libs = array('router', 'sql');
    /** @ignore */
    protected static $vars = array();
    
    /**
     * Horus Constructor
     * 
     * @param array $configs
     * @return object
     */
    public function __construct(array $configs = array())
    {
        // if started don't continue
        if($this->started == true) return $this;

        // start
        // and start an output buffer
        $this->started = true;
        ob_start();

        // system constants
        define('HORUS_VERSION', (float) '4.0.0', true);
        define('IS_AJAX', (bool)(isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'), true);
        define('DS', DIRECTORY_SEPARATOR, true);
        define('COREPATH', dirname(__FILE__) . ds, true);
        define('BASEPATH', dirname(corepath) . ds, true);
        define('HORUS_START', microtime(1), true);

        // configurations and our instance
        $this->configs = array_merge($this->settings(), $configs);
        self::$instance = $this;

        // our autoloader
        spl_autoload_register(array($this, '_load'));

        // start the response manager, events
        // assign them
        // repir server vars
        // call the setup
        $this->response =   new Horus_Response($this->config('horus.http_version'));
        $this->events   =   new Horus_Events;
        $this->repairVars();
        $this->setup();
    }

    // ---------------------------------------

    /**
     * Horus Config
     * 
     * Set/Get Config items
     * 
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function config($key = null)
    {
        // set configs
        // return all if empty key
        // or return the key's value
        if(is_array($key)) {
            $this->configs = array_merge($this->configs, $key);
            $this->setup();
        } elseif(empty($key)) {
            return $this->configs;
        } else return @$this->configs[$key];
    }
    
    // ---------------------------------------
    
    /** @ignore */
    public static function instance()
    {
        return self::$instance;
    }
    
    // ---------------------------------------
    
    /** @ignore */
    public function run()
    {
        // if ran before then don't continue
        // get the buffer
        // trigger before.dispatch events
        // dispatch router and st the current uri as path_info
        // trigger after.dispatch events
        // get the buffer
        // trigger before.output events
        // send the output to response
        // trigger after.output events
        // flush and send the output buffring if needed
        
        if($this->ran) {
            return ;
        }
        
        $this->ran = true;
        $this->__output = ob_get_clean();

        ob_start();
        
        if($this->config('horus.use_router') == true) {
            $this->events->trigger('horus.before.dispatch');
            if((bool) $this->router->dispatch($_SERVER['PATH_INFO']) == false) {
                $this->response->halt(404, call_user_func($this->config('horus.error_404')));
            }
            $this->events->trigger('horus.after.dispatch');
        }
        
        $this->__output .= ob_get_clean();
        $this->events->trigger('horus.before.output');
        $this->response->send($this->__output);
        $this->events->trigger('horus.after.output');
        
        if(ob_get_level() > 0) {
            ob_end_flush();
        }
    }
    
    // ---------------------------------------
    
    /** @ignore */
    protected function settings()
    {
        return array
        (
            'horus.use'                         =>  array(),
            'horus.error_404'                   =>  create_function('', 'die("<h1>404 Not Found</h1> The Requested Page Not Found On This Server .");'),
            'horus.http_version'                =>  '1.1',
            'horus.enable_simulator'            =>  false,
            'horus.controllers_dir'             =>  null
        );
    }
    
    // ---------------------------------------
    
    /** @ignore */
    protected function setup()
    {
        // override some headers
        $this->response->header('Server: ', true);
        $this->response->header('X-Powered-By: Horus', true);
        $this->response->header('Content-Type: text/html; charset=UTF-8', true);
        
        // some php ini settings
        ini_set('session.hash_function',    1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly',  1);
        
        // using what ... ?
        // if all, then set it to the default optional libs .
        // then loop over them and register them .
        // then load every registered class and start it and assign it to horus .
        if($this->config('horus.use') == '*') {
            $this->config(array('horus.use' => $this->libs));
        }
        
        foreach((array) $this->config('horus.use') as $l) {
            $this->configs[sprintf('horus.use_%s', $l)] = true;
        }
        
        if($this->config('horus.use_router') == true) {
            if(!isset($this->router)) {
                $this->router   =   new Horus_Router($_SERVER['PATH_INFO'], $this->config('horus.controllers_dir'));
            }
        }
        
        if($this->config('horus.use_sql') == true) {
            if(!isset($this->sql)) {
                $this->sql          =   new Horus_SQL;
                $this->sql_table    =   new Horus_SQL_Table($this->sql);
            }
        }

    }
    
    // ---------------------------------------

    /** @ignore */
    protected function repairVars()
    {
        // setting some vars
        $_SERVER['SERVER_URL'] = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . rtrim($_SERVER['SERVER_NAME'], '/') . '/' ;
        $_SERVER['SCRIPT_URL'] = $_SERVER['SERVER_URL'].ltrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/', '/');
        $_SERVER['SCRIPT_URI'] = $_SERVER['SCRIPT_URL'];
        $_SERVER['SCRIPT_NAME'] = '/' . ltrim($_SERVER['SCRIPT_NAME'], '/');
        $_SERVER['REQUEST_URI'] = '/' . ltrim($_SERVER['REQUEST_URI'], '/');

        // simulate ?
        if($this->config('horus.enable_simulator') == true) {
            $_SERVER['SCRIPT_URI'] .= basename($_SERVER['SCRIPT_NAME']) . '/';
            
            if(!isset($_SERVER['PATH_INFO'])) {
                $this->response->redirect($_SERVER['SCRIPT_URI']);
            }
        }

        // start preparing for fixing server { path_info }
        $_SERVER['PATH_INFO'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // we want only our url not the path too ( /path/to/horus/url/url/url )
        if(stripos($_SERVER['PATH_INFO'], $_SERVER['SCRIPT_NAME']) === 0) {
            $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], strlen($_SERVER['SCRIPT_NAME']));
        } elseif(stripos($_SERVER['PATH_INFO'], dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        // define some http-constants
        defined('route') or define('route', rtrim($_SERVER['SCRIPT_URI'], '/') . '/', true);
        defined('url') or define('url', rtrim($_SERVER['SCRIPT_URL'], '/') . '/', true);
    }
    
    // ---------------------------------------

    /** @ignore */
    public function _load($class)
    {
        $class = rtrim(ltrim(str_replace(array('/', '_', '\\'), DS, $class), DS), DS);

        if(stripos($class, 'horus') === 0):
            $class = substr($class, strlen(__CLASS__));
            $class = ltrim($class, DS);

            if(is_file($f = COREPATH.$class.'.php'))
                require_once $f;
            elseif(is_file($f = COREPATH.$class.DS.basename($class).'.php'))
                require_once $f;
        endif;
    }
    
    // ---------------------------------------

    /** @ignore */
    public function __call($name, $args)
    {
        return @call_user_func_array(array(self::$vars, $name), $args);
    }

    // ---------------------------------------

    /** @ignore */
    public function __get($k)
    {
        return @self::$vars[$k];
    }

    // ---------------------------------------

    /** @ignore */
    public function __set($k,$v)
    {
        self::$vars[$k] = $v;
    }

    // ---------------------------------------

    /** @ignore */
    public function __unset($k)
    {
        unset(self::$vars[$k]);
    }

    // ---------------------------------------

    /** @ignore */
    public function __isset($k)
    {
        return isset(self::$vars[$k]);
    }
}
