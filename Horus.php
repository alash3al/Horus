<?php (strtolower(basename($_SERVER['SCRIPT_NAME'])) == strtolower(basename(__FILE__))) && exit('No direct access allowed');

/**
 * Horus Framework
 *
 * @package		Horus Framework
 * @author		HorusLiteDev Team,
 * @copyright	        Copyright (c) 2014, Horus Framework.
 * @license		GPL-v3
 * @link		http://alash3al.github.io/Horus
 * @since		Version 2.0
 * @filesource
 */

// -------------------------------------

if ( ! function_exists('vd') )
{
    /**
     * Alias of var_dump
     * @return void
     */
    function vd($expression)
    {
        return call_user_func_array('var_dump', func_get_args());
    }
}

// -------------------------------------

if ( ! function_exists('pr') )
{
    /**
     * Alias of print_r
     * @return  bool
     */
    function pr($expression, $return = false)
    {
       $pr = ("<pre>" . print_r($expression, true) . "</pre>");
       return $return ? $pr : print $pr;
    }
}

// -------------------------------------

if ( ! function_exists('using') )
{
    /**
     * Useful for something like this using(new Class)->xxx()
     * @param   mixed $var
     * @return  mixed
     */
    function using($var)
    {
        return $var;
    }
}

// -------------------------------------

if ( ! function_exists('errors') )
{
    /**
     * errors (show errors) or not ?
     * @param   bool $state
     * @return  int
     */
    function errors($state = TRUE)
    {
        if($state) {error_reporting(E_ALL);@ini_set('display_errors', 1);}
        else error_reporting(0);
        return error_reporting();
    }
}

// -------------------------------------

if ( ! function_exists('settings') )
{
    /**
     * Application Settings manager
     * @param   [string  $key]
     * @param   [mixed   $value]
     * @return  mixed
     */
    function settings($key = null, $value = null)
    {
        static $sets = array();
        !empty($sets) or ($sets = &$GLOBALS['$horus.settings']);

        // set-one
        if ( ! empty($key) && !is_array($key) && ! empty($value) )
            return $sets[$key] = $value;
        elseif ( is_array($key) )
            return $sets = array_merge($sets, $key);
        // get-one
        elseif ( ! empty($key) && is_null($value) )
            return isset($sets[$key]) ? $sets[$key] : null;
        // get-all
        elseif ( empty($key) && empty($value) )
            return $sets;
        // del
        elseif ( ! empty($key) && $value === "" )
            unset($sets[$key]);
    }
}

// -------------------------------------

if ( ! function_exists('server') )
{
    /**
     * $_SERVER array manager
     * @param   [mixed $k]
     * @param   [mixed $v]
     * @return  mixed
     */
    function server($k = null, $v = null)
    {
        // a reference to $_SERVER array
        static $s = array();
        // assign the _SERVER array if not assigned yet
        !empty($s) or ($s = &$_SERVER);
        // set-many
        if ( ! empty($k) && is_array($k) ) {
            foreach ( $k as $x => &$y )
                call_user_func_array(__FUNCTION__, array($x, $y));
            return ;
        }
        // prepare the key
        $k = strtoupper(str_replace(array(' ', '.', '-', '/'), '_', $k));
        // set-one
        if ( !empty($k) && !empty($v) )
            return $s[$k] = $v;
        // get-one
        elseif ( !empty($k) && is_null($v) )
            return (isset($s[$k]) ? $s[$k] : null);
        // get-all
        elseif ( empty($k) )
            return $s;
        // del
        elseif ( !empty($k) && $v === "" )
             unset($s[$k]);
    }
}

// -------------------------------------

if ( ! function_exists('scope') )
{
    /**
     * Global Frame Scope
     * @param   [string  $key]
     * @param   [mixed   $value]
     * @return  mixed
     */
    function scope($key = null, $value = null)
    {
        // global scope handler
        static $scope = array();
        // assign the GLOBALS SCOPE array if not assigned yet
        !empty($scope) or ($scope = &$GLOBALS['$horus.scope']);
        // set-one
        if ( ! empty($key) && !is_array($key) && ! empty($value) )
            return $scope[$key] = &$value;
        elseif ( is_array($key) )
            return $scope = array_merge($scope, $key);
        // get-one
        elseif ( ! empty($key) && is_null($value) )
            return isset($scope[$key]) ? $scope[$key] : null;
        // get-all
        elseif ( empty($key) && empty($value) )
            return $scope;
        // del
        elseif ( ! empty($key) && $value === "" )
            unset($scope[$key]);
    }
}

// -------------------------------------

if ( ! function_exists('call') )
{
    /**
     * AutoMap to call_user_func & call_user_func_array
     * @param   callable $callable
     * @param   [mixed    $args]
     * @return  mixed
     */
    function call($callable, $args = null)
    {
        $args = (func_num_args() > 2) ? array_slice(func_get_args(), 1) : (array) $args;
        return call_user_func_array($callable, $args);
    }
}

// -------------------------------------

if ( ! function_exists('tpls') )
{
    /**
     * Load template file
     * @param   string $filename
     * @return  void
     */
    function tpls($filename, array $vars = array())
    {
        extract($vars, EXTR_SKIP|EXTR_REFS);
        foreach ( explode(',', $filename) as $file )
            if ( is_file($file = settings('tpls.base') . DS . trim($file) . '.' . ltrim(settings('tpls.ext'))) )
                 include( $file );
            else
                trigger_error("the template file '{$file}' not found");
    }
}

// -------------------------------------

if ( class_exists('PDO') )
{
    if ( ! function_exists('pdo_connect') )
    {
        /**
         * Connect to any PDO supported driver
         * @param   string  $dsn
         * @param   [string  $username]
         * @param   [string  $password]
         * @param   [array   $driver_options]
         * @return  PDO
         */
        function pdo_connect($dsn, $username = null, $password = null, array $driver_options = array())
        {
            return new PDO($dsn, $username, $password, $driver_options);
        }
    }

    // ----------------------------

    if ( ! function_exists('pdo_connect_mysql') )
    {
        /**
         * Connect to mysql server
         * @param   string  $host
         * @param   string  $dbname
         * @param   string  $username
         * @param   string  $password
         * @param   array   $driver_options
         * @return  PDO
         */
        function pdo_connect_mysql($host, $dbname, $username = null, $password = null, array $driver_options = array())
        {
            return pdo_connect("mysql:host={$host};dbname={$dbname}", $username, $password, $driver_options);
        }
    }

    // ----------------------------

    if ( ! function_exists('pdo_connect_pgsql') )
    {
        /**
         * Connect to postgre server
         * @param   string  $host
         * @param   string  $dbname
         * @param   string  $username
         * @param   string  $password
         * @param   array   $driver_options
         * @return  PDO
         */
        function pdo_connect_pgsql($host, $dbname, $username = null, $password = null, array $driver_options = array())
        {
            return pdo_connect("pgsql:host={$host};dbname={$dbname};user={$username};password={$password}", $driver_options);
        }
    }

    // ----------------------------

    if ( ! function_exists('pdo_connect_sqlite') )
    {
        /**
         * Connect to sqlite db
         * @param   string  $db
         * @param   array   $driver_options
         * @return  PDO
         */
        function pdo_connect_sqlite($db, array $driver_options = array())
        {
            return pdo_connect("sqlite:{$db}", $driver_options);
        }
    }

    // ----------------------------

    if ( ! function_exists('pdo_query') )
    {
        /**
         * Runs a PDO Query
         * @param   PDO     $pdo
         * @param   string  $query_string
         * @param   [mixed   $query_inputs]
         * @return  bool
         */
        function pdo_query(PDO $pdo, $query_string, $query_inputs = null)
        {
            $query_inputs = is_array($query_inputs) ? $query_inputs : array_slice(func_get_args(), 2);
            $prep = $pdo->prepare($query_string);
            $prep->execute((array) $query_inputs);
            return $prep;
        }
    }

    // ----------------------------

    if ( ! function_exists('pdo_num_rows') )
    {
        /**
         * Returns number of rows affetcted by last SQL statement
         * @param   PDOStatement $pdo_statement
         * @return  int
         */
        function pdo_num_rows(PDOStatement $pdo_statement)
        {
            return $pdo_statement->rowCount();
        }
    }

    // ----------------------------

    if ( ! function_exists('pdo_insert_id') )
    {
        /**
         * Returns the ID of last inserted row
         * @param   PDO $pdo
         * @return  string
         */
        function pdo_insert_id(PDO $pdo)
        {
            return $pdo->lastInsertId();
        }
    }

    // ----------------------------

    if ( ! function_exists('pdo_fetch') )
    {
        /**
         * Fetch the next row of a result set
         * @param   PDOStatement    $pdo_statement
         * @param   [integer         $fetch_style]
         * @return  mixed
         */
        function pdo_fetch(PDOStatement $pdo_statement, $fetch_style = PDO::FETCH_ASSOC)
        {
            return $pdo_statement->fetch($fetch_style);
        }
    }

    // ----------------------------

    if ( ! function_exists('pdo_fetch_all') )
    {
        /**
         * Returns an array containing all of result set rows
         * @param   PDOStatement    $pdo_statement
         * @param   [integer         $fetch_style]
         * @return  mixed
         */
        function pdo_fetch_all(PDOStatement $pdo_statement, $fetch_style = PDO::FETCH_ASSOC)
        {
            return $pdo_statement->fetchAll($fetch_style);
        }
    }
}

// -------------------------------------

if ( ! function_exists('hdr') )
{
    /**
     * header() alternative
     * @param   [string  $k]
     * @param   [string  $v]
     * @param   [bool    $replace]
     * @param   [integer $code]
     * @return  void
     */
    function hdrs($k = null, $v = null, $replace = true, $code = 200)
    {
        static $hdrs = array();
        !empty($hdrs) or ($hdrs = &$GLOBALS['$horus.headers']);

        // prepare the key
        $k = str_replace(array(' ', '.', '_'), '-', ucwords(str_replace('-', ' ', strtolower(trim($k)))));

        // set
        if ( (!empty($k) && !empty($v) && !isset($hdrs[$k])) || (!empty($k) && !empty($v) && $replace) )
            $hdrs[$k] = array($v, $replace, $code);
        // get
        elseif ( !empty($k) && is_null($v) )
            return isset($hdrs[$k]) ? $hdrs[$k] : null;
        // all
        elseif ( empty($k) )
            return $hdrs;
        // del
        elseif ( !empty($k) && $v === "" )
            {$hdrs[$k] = array("", true, $code); unset($hdrs[$k]);}
    }
}

// -------------------------------------

if ( ! function_exists('status') )
{
    /**
     * SET/GET Current http status code
     * @param   [integer $new]
     * @return  integer
     */
    function status($new = null)
    {
        static $code = 200;
        if ( ! empty($new) )
            ($code = $new) && hdrs('x-horus-status', 'a horus response', true, $new);
        return $code;
    }
}

// -------------------------------------

if ( ! function_exists('out') )
{
    /**
     * Write output to the output frame
     * @param   [mixed $args]
     * @return  mixed
     */
    function out($args = null)
    {
        static $out = "";
        !empty($out) or ($out = &$GLOBALS['$horus.output']);

        $args = func_get_args();

        // set [override - append]
        // get
        if ( ! empty($args) ) {
            if ( $args[sizeof($args) - 1] === true ) {
                array_pop($args);
                $out = implode('', $args);
            } else $out .= implode('', $args);
        } else return $out;
    }
}

// -------------------------------------

if ( ! function_exists('respond') )
{
    /**
     * Respond to a browser request
     * @param   string      $method
     * @param   string      $pattern
     * @param   callable    $callable
     * @return  mixed
     */
    function respond($method, $pattern, $callable)
    {
        // tell horus that router has been used
        server('router.used', 1);

        // the array used in the router to know the callable
        // and the type of it .
        $exec       =   array( 'callable' => null, 'type' => 'default' );

        // a class name ?
        if ( is_string($callable) && class_exists($callable) )
            ($exec['callable'] = $callable) && ($exec['type'] = 'class.str');
        // a class instance ?
        elseif ( ! is_callable($callable) && is_object($callable) && is_a($callable, get_class($callable)) )
            ($exec['callable'] = $callable) && ($exec['type'] = 'class.obj');
        // a file path ?
        elseif ( ! is_callable($callable) && is_file($callable) )
            $exec['callable'] = create_function('', "\$argv = func_get_args(); include({$callable})");
        // a normal php callback ?
        elseif ( is_callable($callable) )
            $exec['callable'] = $callable;
        // Ooops !, i cannont detect more :\
        elseif ( ! is_callable($callable) )
            trigger_error('Wait, invalid callable for "'.$method.'" for "'.$pattern.'"');

        // force method(s) be separated with ',',
        // also prepare the pattern for our operations,
        // then create array with the needles,
        // at end check if current method is allowed in the route .
        $method     =   implode(',', (array) $method);
        $pattern    =   implode('|', (array) $pattern);
        $pattern    =   rtrim(server('router.pattern.group'), '/\\').'/'.ltrim($pattern, '\\/');
        $pattern    =   addcslashes(call(scope('$horus.slashes'), preg_replace('/\/+/', '/', $pattern)), './');
        $pattern    =   str_ireplace(array_keys($GLOBALS['$horus.patterns']), array_values($GLOBALS['$horus.patterns']), $pattern);
        $pattern    =   ($exec['type'] == 'default') ? "^{$pattern}$" : "^{$pattern}";
        $ok         =   (stripos($method, 'any') !== false) || (stripos($method, server('request.method')) !== false);

        if ( ($ok !== false) && preg_match("/{$pattern}/", server('router.haystack'), $m) ):

            // remove the first value
            // and start new output buffer
            array_shift($m);
            ob_start();

            // what the callable returns ? [default true]
            // this will help us to optionally end the application
            // when this callable ended or wait for another .
            // only false will make the app continue
            $end = true;

            // apply the callable if it a default one
            // then set the router status to 1 'ok'
            if ( $exec['type'] == 'default' ):

                $end = call_user_func_array($exec['callable'], $m);
                server('router.status', 1);

            // it must be a class, get/construct(pass the regex result) it,
            // then get the haystack parts and remove the route-pattern,
            // then set the default method 'index' if not set,
            // then extract the method arguments,
            // applay an event to the method [extensible],
            // then only run the callback if it is ok and don't start with '_',
            // and set the router status to ok or no .
            else:

                $class  =   ($exec['type'] == 'class.obj') ? $exec['callable'] : new $exec['callable']($m);
                $parts  =   (explode('/', rtrim(ltrim(preg_replace("/{$pattern}/", '', server('router.haystack')), '/'), '/')));
                $method =   empty($parts[0]) ? 'index' : $parts[0];
                $method =   trigger('horus.router.method', $method, $method);
                $parts  =   array_slice($parts, 1);

                if ( $method{0} !== '_' && is_callable(array($class, $method)) ):
                    $end = call_user_func_array(array($class, $method), $parts);
                    server('router.status', 1);
                else:
                    server('router.status', 0);
                endif;

            endif;

            // catch the output --> send it to the output frame,
            // and [[optionally]] finish the php script execution
            out(ob_get_clean());
            (((bool) $end) === false) or finish(false);

        endif;
    }
}

// -------------------------------------

if ( ! function_exists('group') )
{
    /**
     * Route group of routes
     * @param   string   $pattern
     * @param   callable $callable
     * @return  void
     */
    function group($pattern, $callable)
    {
        $old        =   server('router.pattern.group');
        $pattern    =   str_ireplace(array_keys($GLOBALS['$horus.patterns']), array_values($GLOBALS['$horus.patterns']), $pattern);

        server('router.pattern.group', call(scope('$horus.slashes'), rtrim($old, '/\\').'/'.ltrim($pattern, '/\\')));
        call($callable);
        server('router.pattern.group', $old);
    }
}

// -------------------------------------

if ( ! function_exists('bind') )
{
    /**
     * Bind an action to an event
     * @param   string      $event
     * @param   callable    $action
     * @param   integer     $priority
     * @return  void
     */
    function bind($event = null, $action = null, $priority = 0)
    {
        static $events = array();
        !empty($events) or ($events = &$GLOBALS['$horus.events']);

        if ( empty($event) ) return $events;
        elseif ( ! empty($event) && !empty($action) ) {
            $events[$event][$priority][] = $action;
            ksort($events[$event]);
        }
    }
}

// -------------------------------------

if ( ! function_exists('trigger') )
{
    /**
     * trigger an event an applay all of its actions
     * @param   string  $event
     * @param   [mixed   $args]
     * @param   [mixed   $default]
     * @return  mixed
     */
    function trigger($event, $args = null, $default = null)
    {
        $args = array_merge((array) $args, array($default));

        foreach ( bind() as $e => $priorities )
            foreach ( $priorities as $p => &$events )
                foreach ( $events as $i => &$e )
                    $default = call_user_func_array($e, $args);
        return $default;
    }
}

// -------------------------------------

if ( ! function_exists('fire') )
{
    /**
     * Fire an action/event from event queue
     * @param   string      $event
     * @param   [callable   $action]
     * @return  void
     */
    function fire($event, $action = null)
    {
        if ( empty($action) )
            unset($GLOBALS['$horus.events'][$event]);

        foreach ( bind() as $e => $priorities )
                foreach ( $priorities as $p => &$events )
                    foreach ( $events as $i => &$a )
                        if ( $action == $a )
                            unset($GLOBALS['$horus.events'][$e][$p][$i]);
    }
}

// -------------------------------------

if ( ! function_exists('autoload') )
{
    /**
     * Register given path as spl_autoload_register() implementation
     * @param   string $path
     * @param   string $extension
     * @return  lambada
     */
    function &autoload($path, $extension = 'php')
    {
        $params = '$class, $g = '.var_export(array( realpath($path) . DS, "." . ltrim($extension, '.') ), true);

        spl_autoload_register($func = create_function($params, '
            list($path, $extension) = $g;
            $class = rtrim(ltrim(str_replace(array("\\\\", "/", "_"), DS, $class), DS), DS);

            if(!is_file($file = $path . $class . $extension))
                $file = $path . $class . DS . basename($class) . $extension;
            if(is_file($file))
                include_once $file;
        '));

        return $func;
    }
}

// -------------------------------------

if ( ! function_exists('go') )
{
    /**
     * Multi redirect method
     * @since   version 1.0
     * @param   string  $to
     * @param   integer $using
     * @return  void
     */
    function go($to, $using = 302)
    {
        $scheme =   parse_url($to, PHP_URL_SCHEME);
        $to     =   $scheme ? $to : url('@route/%s', $to);

        if(headers_sent()) 
            return call_user_func_array(__FUNCTION__, array($to, 'html'));

        @list($using, $after) = (array) explode(':', $using);

        switch(strtolower($using)):
            case 'html':
                echo('<meta http-equiv="refresh" content="'.(int) $after.'; URL='.$to.'">');
                break;
            case 'js':
                echo('<script type="text/javascript">setTimeout(function(){window.location="'.$to.'";}, '.(((int) $after)*1000).');</script>');
                break;
            default:
                hdrs("Location", $to, true, $using);
                finish(false);
                break;
        endswitch;
    }
}

// -------------------------------------

if ( ! function_exists('url') )
{
    /**
     * Format a local url
     * @param   string  $format
     * @param   mixed   $args
     * @return  string
     */
    function url($format, $args = null)
    {
        $d = array 
        (
            '%route'    =>  server('location.route'),
            '%url'      =>  server('location.url'),
            '%base'     =>  dirname(server('script.name')),
            '%scheme'   =>  server('location.scheme'),
            '%domain'   =>  server('location.domain'),
            '%auto'     =>  server('router.force') ? 'index.php' : "",
            '%path'     =>  ltrim(rtrim(server('router.haystack'), '/'), '/'),
            '%query'    =>  rtrim(server('query.string'), '&')
        );

        $format = str_ireplace(array_keys($d), array_values($d), $format);
        $format = str_replace('://', $s = '{$s}', $format);
        $format = vsprintf($format, is_array($args) ? $args : array_slice(func_get_args(), 1));
        $format = preg_replace('/\/+/', '/', $format);

        return rtrim(str_replace($s, '://', $format), '/');
    }
}

// -------------------------------------

if ( ! function_exists('start') )
{
    /**
     * Start horus
     * @param   [callable $output_callback]
     * @return  void
     */
    function start($output_callback = null)
    {
        // start new output handler if $callback is valid
        if ( is_callable($output_callback) ) {
            cls(true);
            ob_start($output_callback);
        }

        // start new output block
        ob_start();

        // for router
        scope('$horus.slashes', create_function('$v', "return ((\$r = '/'.(rtrim(ltrim(\$v, '\\\\/'), '\\\\/')).'/') == '//') ? '/' : \$r;"));

        // get the path of $REQUEST_URI
        $haystack = trim(parse_url(server('request.uri'), PHP_URL_PATH));

        // remove the dir/script_name from the haystack
        if ( stripos($haystack, $sn = server('script.name')) === 0 )
            $haystack = substr($haystack, strlen($sn));
        elseif ( stripos($haystack, $dn = dirname($sn)) === 0 )
            $haystack = substr($haystack, strlen($dn));

        // set some env vars
        server('horus.started.at', microtime(true));
        server('request method', strtolower(server('request.method')));
        server('router.used', 0);
        server('router.status', 0);
        server('router.force', (is_null($rf = server('router.force')) ? true : (bool) $rf));
        server('router.rewrited', (stripos(server('request.uri'), server('script.name') . '/') === 0));
        server('router.pattern.group', '/');
        server('router.haystack', $haystack = call(scope('$horus.slashes'), $haystack));
        server('location.scheme', is_null($s = server('location.scheme')) ? 'http' : $s);
        server('location.domain', server('server.name'));
        server('location.url', server('location.scheme') . '://' . server('location.domain') . '/' . rtrim(ltrim(dirname(server('script.name')), '/\\'), '\\/') . '/');
        server('location.route', server('location.url') . (server('router.force') ? 'index.php/' : ''));
        server('location.current', server('location.route') . ltrim($haystack, '/'));

        // special form post var to override current method name
        ! empty($_POST['HORUS_OVERRIDE_METHOD']) && (server('request.method', strtolower($_POST['HORUS_OVERRIDE_METHOD'])));

        // some php ini settings
        ini_set('session.hash_function',    1);
        ini_set('session.use_only_cookies', 1);
        session_name('HORUSSESID');

        // set some constants
        defined('DS') 		or define('DS',         DIRECTORY_SEPARATOR, true);
        defined('BASEPATH') or define('BASEPATH',   realpath(dirname($s['SCRIPT_FILENAME'])) . DS, true);
        defined('COREPATH') or define('COREPATH',   realpath(dirname(__FILE__)) . DS, true);

        // Set some environment headers
        hdrs('Content-Type', 'text/html; charset=UTF-8', TRUE);
        hdrs('X-Powered-By', 'HORUS/PHP', TRUE);
		hdrs("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0", TRUE);
		hdrs("Cache-Control", "post-check=0, pre-check=0", TRUE);
		hdrs("Pragma", "no-cache", TRUE);
        hdrs("X-Frame-Options", "SAMEORIGIN", TRUE);
        hdrs("X-XSS-Protection", "1; mode=block", TRUE);
        hdrs("X-Content-Type-Options", "nosniff", TRUE);

        // force index.php if required
        if ( server('router.force') && ! server('router.rewrited') ) {
            go('/');
            finish(false);
        }

        // global system vars
        $GLOBALS += array(
            '$horus.scope'      =>  array(),
            '$horus.settings'   =>  array(),
            '$horus.headers'    =>  array(),
            '$horus.output'     =>  "",
            '$horus.events'     =>  array(),
            '$horus.patterns'   =>  array (
                '@int'      =>  '([0-9\.,]+)',
                '@alpha'    =>  '([a-zA-Z]+)',
                '@alnum'    =>  '([a-zA-Z0-9\.\w]+)',
                '@str'      =>  '([a-zA-Z0-9-_\.\w]+)',
                '@any'      =>  '([^\/]+)',
                '@*'        =>  '?(.*)',
                '@date'     =>  '(([0-9]+)\/([0-9]{2,2}+)\/([0-9]{2,2}+))'
            )
        );

        // some settings
        settings(array(
            'tpls.base'     =>  '',
            'tpls.ext'      =>  'php',
            'e404'          =>  create_function('', 'echo "<h1>404 page not found</h1><p>the requested resource not found</p>";')
        ));

        // merge all inputs
        // but before that, get other [put,delete, .. etc]
        $in = file_get_contents('php://input', false);

        if ( is_array($x = json_decode($in, true)) )
            $in = &$x;
        else
            parse_str($in, $in);

        // merge all here
        $_REQUEST = array_merge($_GET, $_POST, $in);

        // unset some global vars
        unset 
        (
            $HTTP_COOKIE_VARS, $HTTP_SESSION_VARS, $HTTP_SERVER_VARS, 
            $HTTP_POST_VARS, $HTTP_POST_FILES, $HTTP_GET_VARS, $HTTP_ENV_VARS
        );
    }
}

// -------------------------------------

if ( ! function_exists('cls') )
{
    /**
     * Clear the browser window
     * @param   [bool    $ob_clean]
     * @return  void
     */
    function cls($ob_clean = false)
    {
        $ob_clean && @ob_clean();
        out('', true);
    }
}

// -------------------------------------

if ( ! function_exists('finish') )
{
    /**
     * finish the horus application
     * @param   [bool    $check]
     * @return  void
     */
    function finish($check = true)
    {
        // send all headers.
        foreach ( hdrs() as $k => $v )
            header(sprintf('%s: %s', $k, $v[0]), $v[1], $v[2]);

        // check the router status { if required } and 
        // if the status is not ok, call the 404 error
        if ( $check && server('router.used') == true && server('router.status') == false ){
            status(404);
            call(settings('e404'));
        }

        // catch the output buffer and append it
        // to the our output container
        out(ob_get_clean());

        // send the output to the browser of the method is not HEAD
        (server('request.method') == 'head') || print(trigger('horus.output', out(), out()));

        // end the buffering and send it
        ob_end_flush(); exit;
    }
}
