<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     2.0.0
 * @package     Horus
 * @filesource
 */
 
// -------------------------------------------------------------------

/* alias of true */
defined('yes') or define('yes', true, true);
/* alias of false */
defined('no') or define('no', false, true);

// -------------------------------------------------------------------

/**
 * Horus
 * 
 * Horus is a PHP 5 micro framework perfect for getting programmers start
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus
{
    /** @ignore */
    CONST VERSION   = '2.0';
    /** @ignore */
    CONST DS        = DIRECTORY_SEPARATOR;
    
    /** @ignore */
    protected $coredir;
    /** @ignore */
    protected static $vars;
    /** @ignore */
    protected static $instance;
    /** @ignore */
    protected $ran = false;
    /** @ignore */
    protected $configs = array();
    /** @ignore */
    protected $autoloads = array();
    
    // --------------------------------------------------------------------
    
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
        
        // Our Smart AutoClassMapper Autoloader
        spl_autoload_register(array($this, 'XAutoloader'));
        
        // start the http manager
        $this->http     =   new Horus_Http($this->config('horus.http_version'));
        
        // load the common functions file
        require_once $this->coredir . 'Functions.php';
        
        // continue some-settings
        $this->boot();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Horus Tiny Advanced Auto[Mapper & Loader]
     * 
     * @param string $ClassName
     * @return void
     */
    public function XAutoloader($ClassName)
    {
        $ClassName = rtrim(ltrim(str_replace( array('_', '/', '\\'), self::DS, $ClassName ), self::DS), self::DS);
        $x = (array) explode(self::DS, $ClassName, 2);
        @$pre = $x[0];
        $Root = isset($this->autoloads[$pre]) ? $this->autoloads[$pre] . self::DS : dirname(dirname(__FILE__)) . self::DS;
        $ClassName = isset($this->autoloads[$pre]) ? $x[1] : $ClassName;
        $Root = preg_replace('/'.preg_quote(self::DS, '/').'+/', self::DS, $Root);
        
        if( is_file( $f = realpath($Root . $ClassName . '.php') ) ) {
            return require_once $f;
        }
        
        elseif( is_file( $f = realpath($Root . $ClassName . self::DS . basename($ClassName) . '.php') ) ) {
            return require_once $f;
        }
        
        return $f;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set an alias for an autoload path
     * 
     * @param string $alias
     * @param string $path
     * @return void
     */
    public function autoloadPathAlias($alias, $path)
    {
        if(!is_dir($path)) {
            return null;
        }
        
        $alias = str_replace(array('/', '_', '\\'), '', $alias);
        $this->autoloads[$alias] = $path;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get Class Instance
     * @return object
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    // --------------------------------------------------------------------
    
    /**
     * Horus Config
     * 
     * Set/Get Config items
     * 
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function config($key = null, $value = null)
    {

        // get a configs key ?
        if(!is_array($key) and is_null($value)) {
            return isset($this->configs[$key]) ? $this->configs[$key] : null;
        }
            
        // get all configs
        elseif($key === '*') {
            return $this->configs;
        }
        
        // set configs ?
        else {
            $cnfgs = is_array($key) ? $key : array($key => $value);
            $this->configs = array_merge($this->configs, $cnfgs); unset($cnfgs);
            $this->more_sets();
        }

    }
    
    // --------------------------------------------------------------------
    
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
        $output = '';
        
        // don't forget that we have started 
        // an output-block in construction .
        if(ob_get_level() > 0) {
            $output = ob_get_clean();
            ob_end_clean();
        }
        
        // ------------------------------
        
        // using gzip if gzip is enabled .
        if((bool) $this->config('horus.enable_gzip') == true) {
            ob_start('ob_gzhandler');
        }
        
        // ------------------------------
        
        // start new buffer block to get the output then end the block
        // dispatch all routes
        // but also dispatch events [before_dispatch, after_dispatch]
        // if no route dispatched, send 404 errDoc
        ob_start();
        
        if($this->config('horus.use_router') == true) {
            
            events_dispatch('horus.before_dispatch');
            
            $x = $this->router->dispatch();
            
            events_dispatch('horus.after_dispatch');
            
            if($x == false) {
                halt(404, is_callable($z = $this->config('horus.default_404')) ? call_user_func($z) : $this->errDocs()->e404);
            }
            
            unset($x, $z);
            $output .= ob_get_clean();
        }
        
        // ------------------------------
        
        // fix the output as html if needed
        // only will work if xml-fixer disabled
        // uses dom .
        if
        (
            (bool) $this->config('horus.fix_html') == true and 
            (bool) $this->config('horus.fix_xml') == false
        )
        {
            $dom = new DomDocument;
            libxml_use_internal_errors(true);
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->recover = true;
            $dom->loadHTML($output);
            $output = $dom->saveHTML();
            unset($dom);
        }
        
        // ------------------------------
        
        // fix the output as xml if needed
        // only will work if html-fixer disabled
        // uses dom .
        if
        (
            (bool) $this->config('horus.fix_xml') == true and 
            (bool) $this->config('horus.fix_html') == false
        )
        {
            $dom = new DomDocument;
            libxml_use_internal_errors(true);
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->recover = true;
            $dom->loadXML($output);
            $output = $dom->saveXML();
            unset($dom);
        }
        
        // ------------------------------
        
        // minify the output if needed
        // minifier will just remove duplicated [spaces, lines ... etc]
        if((bool) $this->config('horus.minify_output') == true)
        {
            $output = preg_replace('/\s+/', ' ', $output);
        }
        
        // ------------------------------
        
        // the output hooks/events [filters]
        // here i started new buffer block and end it
        // then get it's result
        // then send it to http manager
        // then dispatch on-shutdown events
        ob_start();
        
        echo events_dispatch('horus.output', array($output));
        
        $x = ob_get_clean();
        $output = empty($x) ? $output : $x; unset($x);
        
        $this->http->send($output);
        
        events_dispatch('horus.shutdown');
        
        // ------------------------------
        
        // clean any buffer if still exists
        if(ob_get_level() > 0) ob_end_flush();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Show An Error Document
     * 
     * Based On Horus::horusTemplate()
     * 
     * @param   string   $title
     * @param   string   $message
     * @param   bool     $return
     * @return void
     */
    public function errDoc($title, $message, $return = false)
    {
        @ob_end_clean();
        ob_start();
        
        $r = $this->horusTemplate($title, '<p>'.$message.'</p>', 'body{text-align: center; margin: 18%;} p{font-size: 17px}');
        
        if($return == true)
            return $r;
        else
            exit($r);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * List Of Error Documents
     * 
     * @return void
     */
    public function errDocs()
    {
        return (object)array
        (
            'e400'  =>  $this->errDoc('400 Bad Request', 'This is a bad request', true),
            'e401'  =>  $this->errDoc('401 Unauthorized', 'Authorization is required to access this page', true),
            'e402'  =>  $this->errDoc('401 Payment Required', 'You must perform the payment to continue', true),
            'e403'  =>  $this->errDoc('403 Forbidden Page', 'This page is forbidden', true),
            'e404'  =>  $this->errDoc('404 Page Not Found', 'The requested page not found on this server', true),
            'e405'  =>  $this->errDoc('405 Method Not Allowed', 'The requested method is not allowed', true),
            'e406'  =>  $this->errDoc('406 Not Acceptable', 'This request is not acceptable', true),
            'e500'  =>  $this->errDoc('500 Internal Server Error', 'A server error happend', true),
            'e502'  =>  $this->errDoc('502 Bad Gateway', 'Network error between servers occured', true),
            'e503'  =>  $this->errDoc('503 Service Unavailable', 'I\'m not available now, request me at another time', true),
            'e504'  =>  $this->errDoc('504 Gateway Timeout', 'I couldn\'t receive response from the remote server', true)
        );
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Horus Default Template
     * 
     * @param string $title
     * @param string $body
     * @param string $more_style
     * @return string
     */
    function horusTemplate($title, $body, $more_style = '')
    {
        $tpl = '<!DOCTYPE HTML><html><head><meta charset="UTF-8" /><title> %s </title><style>*{virtecal-position: middle}body{margin:0; padding: 20px;}h1{color:#555}%s</style></head><body><h1> %s </h1>%s</body></html>';
        return vsprintf($tpl, array($title, $more_style, $title, $body));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * More Horus Settings
     * 
     * @return void
     */
    protected function boot()
    {
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
        
        // call the on-boot events
        events_dispatch('horus.boot');
        
        // if the router enabled, autoload it
        if($this->config('horus.use_router') == true) {
            if(!isset($this->router)) {
                $this->router   =   new Horus_Router();
            }
        }
    }
    
    // --------------------------------------------------------------------
    
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
            'horus.default_404'                 =>  create_function('', 'echo horus()->errDocs()->e404;'),
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
            'horus.minify_output'               =>  false,
            'horus.fix_html'                    =>  false,
            'horus.fix_xml'                     =>  false,
            'horus.mode'                        =>  'dev'      // [development, production]
        );
    }
    
    // --------------------------------------------------------------------
    
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
        
        if(stripos($_SERVER['PATH_INFO'], $_SERVER['SCRIPT_NAME']) === 0) {
            $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], strlen($_SERVER['SCRIPT_NAME']));
        }
        elseif(stripos($_SERVER['PATH_INFO'], dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __call($name, $args)
    {
        return  (isset(self::$vars[$name]) and !empty(self::$vars[$name]))
                ? @call_user_func_array(self::$vars[$name], $args)
                : null;
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public static function __callStatic($name, $args)
    {
        return  (isset(self::$vars[$name]) and !empty(self::$vars[$name]))
                ? @call_user_func_array(self::$vars[$name], $args)
                : null;
    }
    
    // --------------------------------------------------------------------

    /** @ignore */
    public function __set($name, $value)
    {
        self::$vars[$name] = $value;
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __get($name)
    {
        return (isset(self::$vars[$name]) ? self::$vars[$name] : null);
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __isset($name)
    {
        return isset(self::$vars[$name]);
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __unset($name)
    {
        unset(self::$vars[$name]);
    }
    
}
