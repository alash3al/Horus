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

/* alias of true */
defined('yes') or define('yes', true, true);
/* alias of false */
defined('no') or define('no', false, true);

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
    CONST VERSION   = '3.0.0';
    /** @ignore */
    CONST DS        = DIRECTORY_SEPARATOR;
    
    /** @ignore */
    protected $coredir;
    /** @ignore */
    protected $vars;
    /** @ignore */
    protected static $instance;
    /** @ignore */
    protected $ran = false;
    /** @ignore */
    protected $configs = array();
    /** @ignore */
    public $autoload_paths = array();
    
    /**
     * Horus Constructor
     * 
     * @param array $configs
     * @return object
     */
    public function __construct(array $configs = array())
    {
        // start output block
        ob_start();
        
        // set the instance, core-directory & configurations
        $this->coredir  = dirname(__FILE__) . self::DS;
        $this->configs  = array_merge($this->getDefaultConfigs(), $configs);
        self::$instance = $this;
        
        // Our Smart Class Autoloader
        spl_autoload_register(array($this, 'autoload'));
        
        // assign some methods/properties
        $this->vars     =   new Horus_Container;
        $this->http     =   new Horus_Http($this->config('horus.http_version'));
        $this->events   =   new Horus_Events;
        $this->error    =   create_function('$title, $content, $style = null', '
            return sprintf("
            <!DOCTYPE HTML>
            <html>
                <head><title>%s</title><style>body{margin:10px;text-align:left;}h1{color:#555}p{color:#333}%s</style></head>
                <body><h1>%s</h1><p>%s</p></body>
            </html>", $title, $style, $title, $content);
        ');
        
        // load the common functions file
        require_once $this->coredir . 'Functions.php';
        
        // continue some-settings
        $this->setup();
    }
    
    // ---------------------------------------
    
    /**
     * Horus PSR Autoloader Function
     * 
     * @param string $ClassName
     * @return void
     */
    public function autoload($ClassName)
    {
        $ClassName = rtrim(ltrim(str_replace(array('\\', '/', '_'), self::DS, $ClassName), self::DS), self::DS);
        list($prefix, $class)   =   (array) explode(self::DS, $ClassName, 2);
        
        if(isset($this->autoload_paths[$prefix])) {
            $prefix = $this->autoload_paths[$prefix];
        } else {
            $prefix = dirname($this->coredir) . self::DS . $prefix . self::DS;
        }
        
        if(!is_file($file = $prefix . $class . '.php')) {
            $file = $prefix . $class . self::DS . basename($class) . '.php';
        }
        
        return is_file($file) ? require_once $file : false;
    }
    
    // ---------------------------------------
    
    /**
     * Get Class Instance
     * @return object
     */
    public static function getInstance()
    {
        return self::$instance;
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
        // set
        if(is_array($key)) {
            $this->configs = array_merge($this->configs, $key);
            $this->setup();
        }
        
        // get all
        elseif(empty($key)) {
            return $this->configs;
        }
        
        // get one
        else return @$this->configs[$key];
    }
    
    // ---------------------------------------
    
    /**
     * Run
     * 
     * Run Horus Application
     * 
     * @return void
     */
    public function run()
    {
        // ran before or not ?
        if($this->ran) {
            return ;
        }
        $this->ran = true;
        $this->__output = '';
        
        // don't forget that we have started 
        // an output-block in construction .
        if(ob_get_level() > 0) {
            $this->__output = ob_get_clean();
            ob_end_clean();
        }
        
        // using gzip if gzip is enabled .
        if((bool) $this->config('horus.enable_gzip') == true) {
            ob_start('ob_gzhandler');
        }
        
        // start new buffer block to get the output then end the block
        // dispatch all routes
        // but also dispatch events [before.dispatch, after.dispatch]
        // if no route dispatched, send 404 errDoc
        ob_start();
        if($this->config('horus.use_router') == true) {
            $x = (bool) $this->router->state();
            if($x == false) {
                halt(404, call_user_func($this->config('horus.error_404')));
            }

            unset($x, $z);
            $this->__output .= ob_get_clean();
        }
        
        // - fire/trigger before.output events
        // - send the output to http
        // - fire/trigger after.output events
        $this->events->trigger('horus.before.output');
        $this->http->send($this->__output);
        $this->events->trigger('horus.after.output');
        
        // clean any buffer if still exists
        if(ob_get_level() > 0) ob_end_flush();
    }
    
    // ---------------------------------------
    
    /**
     * More Horus Settings
     * 
     * @return void
     */
    protected function setup()
    {
        // error reporting
        error_reporting(($this->config('horus.mode') == 'dev') ? E_ALL : 0);
        
        // call the url_rewriter simulator
        $this->simulator();
        
        // override some headers
        $this->http->header('Server: ', true);
        $this->http->header('X-Powered-By: Horus/'.self::VERSION, true);
        $this->http->header('Content-Type: text/html; charset=UTF-8', true);
        
        // auto-run horus if needed
        if((bool) $this->config('horus.auto_run') == true) {
            register_shutdown_function(array($this, 'run'));
        }
        
        // environment settings
        date_default_timezone_set( ( $tz = $this->config('horus.timezone') ) == '' ? date_default_timezone_get() :  $tz);
        
        // some php ini settings
        ini_set('session.hash_function',    $this->config('horus.session_hash_function'));
        ini_set('session.use_only_cookies', (boolean) $this->config('horus.session_use_only_cookies'));
        ini_set('session.name',             $this->config('horus.session_name'));
        ini_set('session.save_path',        $this->config('horus.session_save_path'));
        ini_set('session.cookie_httponly',  (boolean) $this->config('horus.session_http_only'));
        
        // if the router enabled, autoload it
        if($this->config('horus.use_router') == true) {
            if(!isset($this->router)) {
                $this->router   =   new Horus_Router();
            }
        }
    }
    
    // ---------------------------------------
    
    /**
     * Get the default horus settings
     * 
     * @return array
     */
    protected function getDefaultConfigs()
    {
        return array
        (
            'horus.use_router'                  =>  false,
            'horus.timezone'                    =>  date_default_timezone_get(),
            'horus.error_404'                   =>  create_function('', 'die(horus()->error("404 Not Found", "The Requested Page Not Found On This Server ."));'),
            'horus.auto_run'                    =>  true,
            'horus.http_version'                =>  '1.1',
            'horus.enable_simulator'            =>  false,
            'horus.session_name'                =>  'HORUS_SESSID',
            'horus.session_hash_function'       =>  1,
            'horus.session_use_only_cookies'    =>  1,
            'horus.session_http_only'           =>  1,
            'horus.session_regenerate_id'       =>  1,
            'horus.session_save_path'           =>  session_save_path(),
            'horus.enable_gzip'                 =>  true,
            'horus.mode'                        =>  'dev'      // [development, production]
        );
    }
    
    // ---------------------------------------
    
    /**
     * Horus Simulator
     * 
     * @return void
     */
    protected function simulator()
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
                $this->http->redirect($_SERVER['SCRIPT_URI']);
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

    }
    
    // ---------------------------------------
    
    /** @ignore */
    public function __call($name, $args)
    {
        return  (isset($this->vars->$name) and !empty($this->vars->$name))
                ? @call_user_func_array($this->vars->$name, $args)
                : null;
    }
    
    // ---------------------------------------

    /** @ignore */
    public function __set($name, $value)
    {
        $this->vars->{$name} = $value;
    }
    
    // ---------------------------------------
    
    /** @ignore */
    public function __get($name)
    {
        return (isset($this->vars->$name) ? $this->vars->$name : null);
    }
    
    // ---------------------------------------
    
    /** @ignore */
    public function __isset($name)
    {
        return isset($this->vars->$name);
    }
    
    // ---------------------------------------
    
    /** @ignore */
    public function __unset($name)
    {
        unset($this->vars->$name);
    }
    
}
