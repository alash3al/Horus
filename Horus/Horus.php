<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.4.0
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
    CONST VERSION   = '1.4.0';
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
    protected $configs = array
    (
        'horus.timezone'                    =>  '',
        'horus.auto_run'                    =>  true,
        'horus.use_lang'                    =>  false,
        'horus.use_view'                    =>  false,
        'horus.use_db'                      =>  false,
        'horus.use_orm'                     =>  false,
        'horus.use_events'                  =>  false,
        'horus.use_router'                  =>  false,
        'horus.langs_dir'                   =>  null,
        'horus.langs_ext'                   =>  null,
        'horus.views_dir'                   =>  null,
        'horus.views_ext'                   =>  'html',
        'horus.http_version'                =>  '1.1',
        'horus.enable_simulator'            =>  false,
        'horus.simulator_method'            =>  1,
        'horus.session_hash_function'       =>  1,
        'horus.session_use_only_cookies'    =>  1,
        'horus.session_http_only'           =>  1,
        'horus.session_use_strict_mode'     =>  1,
        'horus.temp_dir'                    =>  null,
        'horus.enable_gzip'                 =>  true,
        'horus.minify_output'               =>  false,
        'horus.fix_html'                    =>  false,
        'horus.fix_xml'                     =>  false,
        'horus.session_name'                =>  'HORUS_SESSID',
        'horus.mode'                        =>  'dev'      // [development, production]
    );
    
    // --------------------------------------------------------------------
    
    /**
     * Horus Constructor
     * 
     * @param array $configs
     * @return object
     */
    public function __construct(array $configs = array())
    {
        // initializing
        ob_start();
        
        // ------------------------------
        
        // System vars
        $this->coredir = dirname(__FILE__) . self::DS;
        $this->configs = array_merge($this->configs, $configs);
        
        // ------------------------------
        
        // load some files
        require_once $this->coredir . 'Loader.php';
        
        // ------------------------------
        
        // setting some defaults
        $this->loader   =   new Horus_Loader(); $this->loader->addVendor('Horus', $this->coredir);
        $this->http     =   new Horus_Http((bool) $this->config('horus.http_version'));   
        
        // dispatch simulator
        $this->simulator();
        
        // more sets
        $this->more_sets();
        
        // ------------------------------
        
        // load some files
        require_once $this->coredir . 'Functions.php';
        
        // ------------------------------
        
        // some headers
        $this->http->header('Server: ', true);
        $this->http->header('X-Powered-By: Horus/'.self::VERSION, true);
        $this->http->header('Content-Type: text/html; charset=UTF-8', true);
        
        // ------------------------------
        
        // run horus (auto) when you finish ?
        if((bool) $this->config('horus.auto_run') == true) {
            register_shutdown_function(array($this, 'run'));
        }
        
        // ------------------------------
        
        // save the instance    {simple}
        self::$instance = $this;
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
        if(is_null($value))
            return isset($this->configs[$key]) ? $this->configs[$key] : null;
            
        // get all configs
        elseif($key === '*')
            return $this->configs;
        
        // set a config ?
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
        // but also dispatch events [before_dispatch, after_dispatch] { if events is used }
        // if no route dispatched, send 404 errDoc
        ob_start();
        
        if($this->config('horus.use_router') == true) {
            if($this->config('horus.use_events') == true) {
                $this->events->dispatch('horus.before_dispatch');
            }
            
            $x = $this->router->dispatch();
            
            if($this->config('horus.use_events') == true) {
                $this->events->dispatch('horus.after_dispatch');
            }
            
            if($x == false) {
                $this->http->halt(404, $this->errDocs()->e404);
            }
            
            unset($x);
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
        // then send it to http client
        if($this->config('horus.use_events') == true) {
            ob_start();
            echo $this->events->dispatch('horus.output', array($output));
            $x = ob_get_clean();
        }
        
        $output = empty($x) ? $output : $x; unset($x);
        $this->http->send($output);
        
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
    protected function more_sets()
    {
        // environment settings
        date_default_timezone_set( ( $tz = $this->config('horus.timezone') ) == '' ? date_default_timezone_get() :  $tz);
        
        // some php ini settings
        ini_set('session.hash_function',    $this->config('horus.session_hash_function'));
        ini_set('session.use_only_cookies', $this->config('horus.session_use_only_cookies'));
        ini_set('session.use_strict_mode',  $this->config('horus.session_use_strict_mode'));
        ini_set('session.name',             $this->config('horus.session_name'));
        ini_set('session.save_path',        !is_dir($this->config('horus.temp_dir')) ? session_save_path() : $this->config('horus.temp_dir'));
        ini_set('session.cookie_httponly',  $this->config('horus.session_http_only'));
        
        
        // ------------------------------
        
        // continue add/config default methods
        if($this->config('horus.use_router') == true) {
            if(!isset($this->router)) {
                $this->router   =   new Horus_Router();
            }
        }
        
        if($this->config('horus.use_events') == true) {
            if(!isset($this->events)) {
                $this->events   =   new Horus_Events();
            }
        }
        
        if($this->config('horus.use_view') == true) {
            if(!isset($this->view)) {
                $this->view     =   new Horus_View($this->config('horus.views_dir'), $this->config('horus.views_ext'));
            } else {
                $this->view->reConfig($this->config('horus.views_dir'), $this->config('horus.views_ext'));
            }
        }
            
        if($this->config('horus.use_db') == true) {
            if(!isset($this->db)) {
                $this->db       =   new Horus_DB();
            }
        }  
       
        if($this->config('horus.use_lang')) {
            if(!isset($this->lang)) {
                $this->lang     =   new Horus_Lang($this->config('horus.langs_dir'), $this->config('horus.langs_ext'));
            } else {
                $this->lang->reConfig($this->config('horus.langs_dir'), $this->config('horus.langs_ext'));
            }
        }
        
        if($this->config('horus.use_orm') == true) {
            if(!isset($this->orm)) {
                if($this->config('horus.use_db') !== true) {
                    $this->db       =   new Horus_DB();
                }
                
                $this->orm          =   new Horus_DB_ORM($this->db);
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Horus Simulator
     * 
     * @return void
     */
    protected function simulator()
    {
        // remove some vars
        unset($_SERVER['PATH_INFO']);
        
        // setting some vars
        $_SERVER['SERVER_URL'] = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . rtrim($_SERVER['SERVER_NAME'], '/') . '/' ;
        $_SERVER['SCRIPT_URL'] = $_SERVER['SERVER_URL'].ltrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/', '/');
        $_SERVER['SCRIPT_URI'] = $_SERVER['SCRIPT_URL'];
        
        // based on '/filename.php/x/y/z?q=uuu'
        if($this->config('horus.simulator_method') == 1) {
            @list($p, $_SERVER['QUERY_STRING']) = (array) explode('?', $_SERVER['REQUEST_URI'], 2);
            $m = $_SERVER['SCRIPT_NAME'];    
            $p = ltrim($p, '/');
            
            parse_str($_SERVER['QUERY_STRING'], $_GET);
            
            if(stripos($p, basename($m).'/') !== false) {
                @list(, $p) = (array) explode(basename($m).'/', $p, 2);
                $p = preg_replace('/\/+/', '/', '/' . rtrim(ltrim($p, '/'), '/') . '/');
                $_SERVER['PATH_INFO'] = $p;
            }
            
            $_SERVER['HORUS_SIMULATOR'] = basename($_SERVER['SCRIPT_NAME']).'/';
            unset($p, $m);
        }
        
        // based on '?x/y/z?q=uuu' or '?/x/y/z?q=uuu'
        elseif($this->config('horus.simulator_method') == 2) {
            @list(, $p, $_SERVER['QUERY_STRING']) = (array) explode('?', $_SERVER['REQUEST_URI'], 3);
            
            parse_str($_SERVER['QUERY_STRING'], $_GET);
            
            if(!empty($p)) {
                $p = preg_replace('/\/+/', '/', '/' .  rtrim(ltrim($p, '/'), '/') . '/');
                $_SERVER['PATH_INFO'] = $p;
            }
            
            $_SERVER['HORUS_SIMULATOR'] = '?/';
            unset($p);
        }
        
        $_SERVER['SCRIPT_URI'] .= $_SERVER['HORUS_SIMULATOR'];
        
        // simulate ?
        if($this->config('horus.enable_simulator') == true and !isset($_SERVER['PATH_INFO'])) {
            $this->http->redirect($_SERVER['SCRIPT_URI']);
            $this->http->halt(302);
        }
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __call($name, $args)
    {
        return  (isset(self::$vars[$name]) and !empty(self::$vars[$name]))
                ? call_user_func_array(self::$vars[$name], $args)
                : null;
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public static function __callStatic($name, $args)
    {
        return  (isset(self::$vars[$name]) and !empty(self::$vars[$name]))
                ? call_user_func_array(self::$vars[$name], $args)
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