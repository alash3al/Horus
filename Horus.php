<?php
/**
 * Horus (xpress) - the most powerful tiny php5 framework
 * 
 * @package     Horus
 * @copyright   2015 (c) Horus
 * @author      Mohammed Al Ashaal
 * @license     MIT LICENSE
 * @version     13.0
 */
class Horus extends stdClass
{
    /**
     * An instance of this class
     * @var Horus
     */
    protected static $instance;

    /**
     * Horus configurations container
     * @var object
     */
    public $config;

    /**
     * Return horus instance
     * 
     * @return  Horus
     */
    public static function instance()
    {
        return static::$instance;
    }

    /**
     * Construct
     * 
     * @param   array   $config
     */
    public function __construct(array $config = [])
    {
        // enable output buffering
        ob_start();

        // configs
        $this->config = (object) array_merge
        (
            [
                'secure' => false,
                'base' => '/',
                'path_info.strip' => $_SERVER['SCRIPT_NAME'],
                'output.handler' => null
            ],

            $config
        );

        // query string
        $this->query = json_decode(json_encode($_GET));

        // cookies
        $this->cookies = json_decode(json_encode($_COOKIE));

        // request body
        $this->body = call_user_func(function()
        {
            $return = null;

            if ( ! is_null($a = json_decode(file_get_contents('php://input'))) ) {
                $return = &$a;
            }

            elseif ( ($a = @simplexml_load_string(file_get_contents('php://input'))) ) {
                $return = &$a;
            }

            else {
                parse_str(file_get_contents('php://input'), $return);
                $return = json_decode(json_encode($return));
            }

            return $return;
        });

        // request vars
        $this->request = json_decode(json_encode(array_merge((array) $this->query, (array) $this->body)));

        // strip from path_info
        $strip_path_info = $this->config->{'path_info.strip'};

        // fix the virtual path
        $_SERVER['PATH_INFO'] = call_user_func(function($uri) use($strip_path_info)
        {
            $path = preg_replace('/\/+/', '/', ('/' . explode('?', $uri, 2)[0] . '/'));
            $script_name = preg_replace('/\/+/', '/', ('/' . ltrim($strip_path_info, '/')));
            $return = $path;

            if ( stripos($path, $script_name) === 0 )
                $return = substr($path, strlen($script_name));
            elseif ( stripos($path, dirname($script_name)) === 0 )
                $return = substr($path, strlen(dirname($script_name)));

            return preg_replace('/\/+/', '/', ('/' . $return . '/'));
        }, $_SERVER['REQUEST_URI']);

        // the instance
        static::$instance = $this;
    }

    /**
     * Set http header(s)
     * 
     * @param   mixed   $field
     * @param   string  $value
     * @return  $this
     */
    public function set($field, $value = null)
    {
        if ( is_array($field) ) {
            foreach ( $field as $f => $v )
                $this->set($f, $v);
            return $this;
        }

        $field = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($field))));

        header(sprintf("%s: %s", $field, $value), true);

        return $this;
    }

    /**
     * Append http header(s)
     * 
     * @param   mixed   $field
     * @param   string  $value
     * @return  $this
     */
    public function append($field, $value = null)
    {
        if ( is_array($field) ) {
            foreach ( $field as $f => $v )
                $this->append($f, $v);
            return $this;
        }

        $field = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($field))));

        header(sprintf("%s: %s", $field, $value), false);

        return $this;
    }

    /**
     * Remove header field(s)
     * 
     * @param   mixed   $field
     * @return  $this
     */
    public function remove($field)
    {
        foreach ( (array) $field as $f ) {
            $f = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($f))));
            header_remove($f);
        }

        return $this;
    }

    /**
     * Set/Get the http status code
     * 
     * @param   int     $code
     * @return  $this
     */
    public function status($code)
    {
        http_response_code((int) $code);

        return $this;
    }

    /**
     * Output a message to the browser
     * 
     * @param   string     $message
     * @return  $this
     */
    public function send($message)
    {
        echo $message;

        return $this;
    }

    /**
     * Output json to the browser
     * 
     * @param   mixed   $message
     * @return  $this
     */
    public function json($message)
    {
        $this->set('content-type', 'application/json; charset=UTF-8')->send(json_encode($message));
        return $this;
    }

    /**
     * Output json to the browser
     * 
     * @param   string  $message
     * @return  $this
     */
    public function jsonp($message, $cb = 'cb')
    {
        $this->set('content-type', 'application/javascript; charset=UTF-8')->send(sprintf('%s(%s)', $cb, json_encode($message)));
        return $this;
    }

    /**
     * Clear the output
     * 
     * @return  $this
     */
    public function clear()
    {
        ob_clean();
        return $this;
    }

    /**
     * Send a cookie to the borwser
     * 
     * @param   string  $name
     * @param   string  $value
     * @param   array   $options
     * @return  $this
     */
    public function cookie($name, $value = "", array $options = [])
    {
        $options    =   array_merge(
        [
            'domain'    =>  null,
            'path'      =>  '/',
            'expires'   =>  0,
            'secure'    =>  (bool) $this->config->secure,
            'httpOnly'  =>  true
        ], array_change_key_case($options, CASE_LOWER));

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
     * Output the specified filename(s) to the browser and optionally pass vars to it/them
     * 
     * @param   string  $filename
     * @param   array   $scope
     * @return  $this
     */
    public function render($filename, array $scope = [])
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
     * Redirect to the specified '$target'
     * 
     * @param   string  $target
     * @param   bool    $permanent
     * @return  $this
     */
    public function redirect($target, $permanent = false)
    {
        $code = $permanent ? 301 : 302;

        $this->set('location', $target)->clear()->status($code)->end();

        return $this;
    }

    /**
     * Ends the application with data, http headers and status code
     * 
     * @param   string  $data
     * @param   int     $status
     * @param   array   $headers
     * @return  void
     */
    public function end($data = null, $status = null, array $headers = [])
    {
        if ( null !== $status ) {
            $this->status($status);
        }

        if ( [] !== $headers ) {
            $this->set($headers);
        }

        if ( null !== $data ) {
            $this->send($data);
        }

        $output = ob_get_clean();

        if ( is_callable($this->config->{'output.handler'}) ) {
            $output = call_user_func($this->config->{'output.handler'}, $output);
        }

        die($output);
    }

    /**
     * Listen on the requested uri
     * 
     * @param   string      $pattern
     * @param   Closure     $listener
     * @return  $this
     */
    public function on($pattern, Closure $listener)
    {
        $listener = $listener->bindTo($this);
        $parts = explode(' ', $pattern, 2);

        if ( ! isset($parts[1]) ) {
            $method = $_SERVER['REQUEST_METHOD'];
            $pattern = $parts[0];
        }

        else {
            $method = $parts[0];
            $pattern = $parts[1];
        }

        $method = explode('|', strtolower($method));
        $pattern = "~^" . (preg_replace('/\/+/', '/', ('/' . str_replace(['/:*', '/:?'], ['/?(.*)', '/([^\/]+)'], $pattern) . '/'))) . "$~";

        if ( in_array(strtolower($_SERVER['REQUEST_METHOD']), $method) && preg_match($pattern, $_SERVER['PATH_INFO'], $args) ) {
            array_shift($args);
            call_user_func_array($listener, $args);
        }

        return $this;
    }

    /**
     * Rewrite from `old` to `new`
     * 
     * @param   string  $old
     * @param   string  $new
     * @return  $this
     */
    public function rewrite($old, $new)
    {
        $old = "/^".(addcslashes(preg_replace('/\/+/', '/', '/' . ($old) . '/'), '/'))."$/";

        if ( preg_match($old, $_SERVER['PATH_INFO']) ) {
            $_SERVER['PATH_INFO'] = preg_replace('/\/+/', '/', '/' . (preg_replace($old, $new, $_SERVER['PATH_INFO'])) . '/');
        }

        return $this;
    }

    /**
     * Listen on hostname and handle custome virtual-hosts
     * 
     * @param   string      $pattern
     * @param   Closure     $listener
     * @return  $this
     */
    public function vhost($pattern, Closure $listener)
    {
        $listener = $listener->bindTo($this);
        $host = (empty($_SERVER['SERVER_NAME']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);

        if ( preg_match("/^{$pattern}$/", $host, $m) ) {
            array_shift($m);
            call_user_func_array($listener, $m);            
         }

        return $this;
    }

    /**
     * Return an url for a local path
     * 
     * @param   string  $path
     * @param   string  $host
     * @return  string
     */
    public function url($path = '', $host = null)
    {
        if ( empty($host) ) {
            $host = (empty($_SERVER['SERVER_NAME']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
        }        

        return  sprintf
        (
            // the required template
            "%s://%s%s",

            // schema 'http/https'
            ($this->config->secure ? 'https' : 'http'),

            // host
            $host,

            // the path
            preg_replace('/\/+/', '/', '/' . trim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/' . $path)
        );
    }

    /**
     * Return an url for a local path
     * 
     * @param   string  $path
     * @param   string  $host
     * @return  string
     */
    public function route($path = '', $host = null)
    {
        if ( empty($host) ) {
            $host = (empty($_SERVER['SERVER_NAME']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
        }

        return preg_replace
        (
            '/\/+$/',
            '/',
            sprintf
            ( 
                // the required template
                "%s://%s%s",
    
                // schema 'http/https'
                ($this->config->secure ? 'https' : 'http'),
    
                // host
                $host,
    
                // path
                preg_replace('/\/+/', '/', '/' . ( dirname($_SERVER['SCRIPT_NAME']) . '/' . $this->config->base ) . '/' . $path)
            )
        );
    }

    /**
     * Make class in the specified dir(s) be autoloaded
     * 
     * @param   string|array    $dir
     * @return  $this
     */
    public function autoload($dir)
    {
        foreach ( (array) $dir as $d )
        {
            spl_autoload_register(function($class) use($d)
            {
                $ds = DIRECTORY_SEPARATOR;
                $class = trim(str_replace(['\\'], $ds, $class), '\\_/');
                $name = basename($class);
                $prop = [ '.php', $ds . $name . '.php' ];

                foreach ( $prop as $p )
                {
                    if ( is_file($file = $d . $ds. $class . $p) )
                    {
                        include_once($file);
                        return ;
                    }
                }
            });
        }

        return $this;
    }

    /** @ignore */
    public function __call($name, $args)
    {
        return  isset($this->{$name}) && is_callable($this->{$name})
                ? call_user_func_array($this->{$name}, $args)
                : null
        ;
    }

    /** @ignore */
    public function __set($k, $v)
    {
        if ( $v instanceof Closure )
            $this->{$k} = $v->bindTo($this);
        else
            $this->{$k} = $v;
    }
}
