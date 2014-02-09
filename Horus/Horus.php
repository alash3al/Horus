<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.0.0
 * @package     Horus
 * @filesource
 */
 
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
    CONST VERSION   = '1.0.0';
    /** @ignore */
    CONST DS        = DIRECTORY_SEPARATOR;
    
    /** @ignore */
    protected $basedir;
    /** @ignore */
    protected $autoloads;
    /** @ignore */
    protected $vars;
    /** @ignore */
    protected static $instance;
    /** @ignore */
    protected $configs = array(
        'horus.temp_dir'            =>  null,
        'horus.enable_gzip'         =>  true,
        'horus.minify_output'       =>  false,
        'horus.fix_html'            =>  false,
        'horus.fix_xml'             =>  false,
        'horus.enable_simulator'    =>  true,
        'horus.views_dir'           =>  null,
        'horus.views_ext'           =>  'tpl',
        'horus.mode'                =>  'dev'      // [development, production]
    );
    
    /**
     * Horus Constructor
     * 
     * @param array $configs
     * @return object
     */
    public function __construct(array $configs = array())
    {
        // the base directory
        $this->basedir = dirname(__FILE__) . self::DS;
        
        // configurations
        $this->configs = array_merge($this->configs, $configs);
        ini_set('session.hash_bits_per_character', 6);
        ini_set('session.hash_function', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.name', 'HORUS_SESSID');
        ini_set('session.save_path', (empty($this->config['horus.temp_dir']) ? $this->config('horus.temp_dir') : session_save_path()));
        
        // load some files
        require $this->basedir . 'Functions.php';
        require $this->basedir . 'Loader.php';
        
        // register some defaults
        $this->loader   =   new Horus_Loader(); $this->loader->addVendor('Horus', $this->basedir);
        $this->events   =   new Horus_Events();
        $this->router   =   new Horus_Router($this->config('horus.enable_simulator'));
        $this->view     =   new Horus_View($this->config('horus.views_dir'), $this->config('horus.views_ext'));
        $this->db       =   new Horus_DB();
        
        // error & exception handler
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));
        
        // some headers
        headeri('Content-Type: text/html; charset=UTF-8', true);
        headeri('X-Powered-By: Horus/'.self::VERSION, true);
        
        // signleton
        self::$instance = $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Class Signleton
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
        if(is_array($key))
            foreach($key as $k => &$v) $this->configs[$k] = $v;
        elseif(is_null($value) and !is_null($key))
            return isset($this->configs[$key]) ? $this->configs[$key] : null;
        elseif(is_null($key))
            return $this->configs;
        else
            $this->configs[$key] = $value;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Horus ErrorHandler
     * 
     * Will Convert Errors to Exceptions
     * 
     * @param int       $errno
     * @param string    $errstr
     * @param string    $errfile
     * @param string    $errline
     * @return void
     */
    public function errorHandler($errno, $errstr = '', $errfile = '', $errline = '')
    {
        $x = func_get_args();
        // is $errno [in the range of] error_reporting() ?
        if($errno & error_reporting())
            echo $this->horusTemplate('Horus Caught an error', 
            '<p style="text-align:left;">
                <b>Message: </b>'.strip_tags($x[1]).'<br />
                <b>File: </b>'.$x[2].'<br />
                <b>Line: </b>'.$x[3].'<br />
                <b>Help: </b>   <a target="_blank" title="Search Google For Help" href="http://google.com/search?q=PHP '.$x[1].'">Google</a> | 
                                <a target="_blank" title="Search Yandex For Help" href="http://yandex.com/yandsearch?text=PHP '.$x[1].'">Yandex</a> |
                                <a target="_blank" title="Search Bing For Help" href="http://bing.com/search?q=PHP '.$x[1].'">Bing</a> <br />
            </p>
        ', 'body{text-align:center; margin: 10%} a{text-decoration: none; color: blue} a:hover{color: red}');
        else
            return ;
    }
    
    // --------------------------------------------------------------------
    
     /**
      * Exception Handler
      * 
      * @param Exception $e
      * @return void
      */
     public function exceptionHandler(Exception $e)
     {
        $x = array(
            0,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        
        echo $this->horusTemplate('Horus Caught an error', 
            '<p style="text-align:left;">
                <b>Message: </b>'.strip_tags($x[1]).'<br />
                <b>File: </b>'.$x[2].'<br />
                <b>Line: </b>'.$x[3].'<br />
                <b>Help: </b>   <a target="_blank" title="Search Google For Help" href="http://google.com/search?q=PHP '.$x[1].'">Google</a> | 
                                <a target="_blank" title="Search Yandex For Help" href="http://yandex.com/yandsearch?text=PHP '.$x[1].'">Yandex</a> |
                                <a target="_blank" title="Search Bing For Help" href="http://bing.com/search?q=PHP '.$x[1].'">Bing</a> <br />
            </p>
        ', 'body{text-align:center; margin: 10%} a{text-decoration: none; color: blue} a:hover{color: red}');
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
    public function horusTemplate($title, $body, $more_style = '')
    {
        $tpl = '<!DOCTYPE HTML>
                <html>
                    <head>
                        <meta charset="UTF-8" />
                        <title> %s </title>
                        <style>
                            *{virtecal-position: middle}
                            body{margin:0; padding: 20px; font-family:Andalus}
                            h1{color:#555}
                            %s
                        </style>
                    </head>
                    <body>
                        <h1> %s </h1>
                        %s
                    </body>
                </html>';
        return vsprintf($tpl, array($title, $more_style, $title, $body));
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
        return (object)array(
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
     * Run
     * 
     * Run Horus Application
     * 
     * @return void
     */
    public function run()
    {
        // error reporting
        $this->config('horus.mode') !== 'dev' ? error_reporting(0) : null;
        
        // control the output buffer and enable gzip if required
        flush_buffer();
        $this->config('horus.enable_gzip') == true ? ob_start('ob_gzhandler') : null;
        ob_start();
        
        // apply before-dispatch-routes events here
        $this->events->dispatch('horus.before_dispatch');
        
        // run all routes
        $x = $this->router->dispatch();
        
        // if no route dispatched , show 404
        if($x === false)
            stop(404, $this->errDocs()->e404);
        
        // apply after-dispatch-routes events here
        $this->events->dispatch('horus.after_dispatch');
        
        // get the output
        $output = ob_get_clean();
        
        // enable html-fixer ?
        if($this->config('horus.fix_html') == true and $this->config('horus.fix_xml') == false) {
            $dom = new DOMDocument; libxml_use_internal_errors(true);
            @$dom->loadHTML($output);
            $output = $dom->saveHTML(); unset($dom);
        }
        
        // enable xml-fixer ?
        if($this->config('horus.fix_xml') == true and $this->config('horus.fix_html') == false) {
            $dom = new DOMDocument; libxml_use_internal_errors(true);
            @$dom->loadXML($output);
            $output = $dom->saveXML(); unset($dom);
        }
        
        // enable compressor ?
        if($this->config('horus.minify_output') == true) {
            $output = preg_replace('/\s+/', ' ', $output);
        }
        
        // apply output-events
        $o = $this->events->dispatch('horus.output', array($output));
        if(!empty($o) and $o !== false) $output = $o; unset($o);
        
        // finalize the output and send it
        echo $output;

       restore_error_handler();
       restore_exception_handler();
       @ob_end_flush();
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __call($name, $args)
    {
        return  (isset($this->vars[$name]) and !empty($this->vars[$name]))
                ? call_user_func_array($this->vars[$name], $args)
                : null;
    }
    
    // --------------------------------------------------------------------
    
    
    /** @ignore */
    public static function __callStatic($name, $args)
    {
        return  isset(self::$vars[$name]) 
                ? call_user_func_array(self::$vars[$name], $args)
                : null;
    }
    
    // --------------------------------------------------------------------
    

    /** @ignore */
    public function __set($name, $value)
    {
        $this->vars[$name] = $value;
    }
    
    // --------------------------------------------------------------------
    

    /** @ignore */
    public function __get($name)
    {
        return (isset($this->vars[$name]) ? $this->vars[$name] : null);
    }
}
