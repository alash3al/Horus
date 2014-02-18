<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.2.0
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
    CONST VERSION   = '1.2.0';
    /** @ignore */
    CONST DS        = DIRECTORY_SEPARATOR;
    
    /** @ignore */
    protected $basedir;
    /** @ignore */
    protected $vars;
    /** @ignore */
    protected static $instance;
    /** @ignore */
    protected $configs = array
    (
        'horus.use_lang'                    =>  false,
        'horus.use_view'                    =>  false,
        'horus.use_db'                      =>  false,
        'horus.langs_dir'                   =>  null,
        'horus.langs_ext'                   =>  null,
        'horus.http_version'                =>  '1.1',
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
        'horus.enable_simulator'            =>  true,
        'horus.views_dir'                   =>  null,
        'horus.views_ext'                   =>  'tpl',
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
        
        // the base directory
        $this->basedir = dirname(__FILE__) . self::DS;
        
        // ------------------------------
        
        // configurations
        $this->configs = array_merge($this->configs, $configs);
        
        // ------------------------------
        
        // some php ini settings
        ini_set('session.hash_function',            $this->config('horus.session_hash_function'));
        ini_set('session.use_only_cookies',         $this->config('horus.session_use_only_cookies'));
        ini_set('session.use_strict_mode',          $this->config('horus.session_use_strict_mode'));
        ini_set('session.name',                     $this->config('horus.session_name'));
        ini_set('session.save_path',                $this->config('horus.temp_dir'));
        ini_set('session.cookie_httponly',          $this->config('horus.session_http_only'));
        
        // ------------------------------
        
        // load some files
        require $this->basedir . 'Loader.php';
        
        // ------------------------------
        
        // register some defaults
        $this->loader   =   new Horus_Loader(); $this->loader->addVendor('Horus', $this->basedir);
        $this->events   =   new Horus_Events();
        $this->http     =   new Horus_Http((bool) $this->config('horus.http_version'));
        $this->router   =   new Horus_Router((bool) $this->config('horus.enable_simulator'));
            
        if($this->config('horus.use_view') == true) {
            $this->view     =   new Horus_View($this->config('horus.views_dir'), $this->config('horus.views_ext'));
        }
            
        if($this->config('horus.use_db') == true) {
            $this->db       =   new Horus_DB();
        }  
       
        if($this->config('horus.use_lang')) {
            $this->lang     =   new Horus_Lang($this->config('horus.langs_dir'), $this->config('horus.langs_ext'));
        }
        
        // ------------------------------     
        
        // load some files
        require $this->basedir . 'Functions.php';
        
        // ------------------------------
        
        // some headers
        $this->http->header('Server: Horus', true);
        $this->http->header('X-Powered-By: Horus/'.self::VERSION, true);
        $this->http->header('Content-Type: text/html; charset=UTF-8', true);
        
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
     * Run
     * 
     * Run Horus Application
     * 
     * @return void
     */
    public function run()
    {
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
        $this->events->dispatch('horus.before_dispatch');
        $x = $this->router->dispatch();
        $this->events->dispatch('horus.after_dispatch');
        if($x == false) $this->http->halt(404, $this->errDocs()->e404);   unset($x);
        $output .= ob_get_clean();
        
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
        ob_start();
        echo $this->events->dispatch('horus.output', array($output));
        $x = ob_get_clean();
        $output = empty($x) ? $output : $x; unset($x);
        
        // ------------------------------
        
        $this->http->send($output);
        
        // ------------------------------
        
        // clean any buffer if still exists
        if(ob_get_level() > 0);
            ob_end_flush();     // end flush = clean + send
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
    protected function horusTemplate($title, $body, $more_style = '')
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
    
    /** @ignore */
    public function __call($name, $args)
    {
        return  (isset($this->vars[$name]) and !empty($this->vars[$name]))
                ? call_user_func_array($this->vars[$name], $args)
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
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __isset($name)
    {
        return isset($this->vars[$name]);
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __unset($name)
    {
        unset($this->vars[$name]);
    }
    
}