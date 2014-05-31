<?php
/**
 * Horus - a micro PHP 5 framework
 * 
 * Horus is a minmalmicro framework that aims to do what the coder
 * want in a simple nice way without a hassel, you will notice that
 * you can do what other frameworks can but in a simple way and
 * avoiding it's complex .
 * "Be simple & smart"
 * 
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     5.0
 * @package     Horus
 * @filesource
 */

// -------------------------------

/**
 * Container
 * 
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
    protected $data = array();

    /**
     * Get stored value
     * @param   string  $key
     * @param   string  $default
     * @return  mixed
     */
    function _get($key, $default = null)
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
    function _set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if the store has a certain key
     * @param   string $key
     * @return  bool
     */
    function has($key)
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
    function count()
    {
        return count($this->data);
    }

    /** @ignore */
    function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /** @ignore */
    function serialize()
    {
        return json_encode($this->data);
    }

    /** @ignore */
    function unserialize($d)
    {
        return json_decode($d, true);
    }

    /** @ignore */
    function __toString()
    {
        return serialize($this);
    }
}

// -------------------------------

/**
 * SQL
 * 
 * A simple yet powerful sql framework that extends PDO
 * and adds some new features to make code simple .
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    4.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_SQL extends PDO
{
    /** @ignore */
    protected $stmnt, $driver, $database;

    /** @ignore */
    function __construct(){}

    /**
     * Construtor
     * @param string    $dns
     * @param string    $username
     * @param string    $password
     * @param array     $driver_options
     * @return Object
     */
    function connect($dns, $username = null, $password = null, array $driver_options = array())
    {
        try 
        {
            parent::__construct($dns, $username, $password, $driver_options);
            $this->_setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            list($this->driver) = explode(':', $dns, 2);
            if(preg_match('/dbname(.*?)=(.+)/i', $dns, $m)) {
                $this->database = trim($m[2]);
            }
            return $this;
        } catch(PDOException $e) {
            throw new Exception($e->getMessage());
            return false;
        }
    }

    /**
     * Create new mysql connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    function mysql($host, $dbname, $username = null, $password = null)
    {
        return $this->connect("mysql:host={$host}; dbname={$dbname}", $username, $password);
    }

    /**
     * Create new mariaDB connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    function mariaDB($host, $dbname, $username = null, $password = null)
    {
        $c = $this->connect("mysql:host={$host}; dbname={$dbname}", $username, $password);
        $this->driver = 'mariaDB';
        return $c;
    }

    /**
     * Connect to sqlite file
     * @param   string  $filename
     * @param   bool    $persistent
     * @return  object
     */
    function sqlite($filename, $persistent = false)
    {
        $filename = ($filename == ':temp:') ? (realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'horus-temp-db.sqlite') : $filename;
        $c =  $this->connect("sqlite:{$filename}",null,null,array(PDO::ATTR_PERSISTENT => (bool) $persistent));
        $this->query('pragma synchronous = off;');
        
        return $c;
    }

    /**
     * Create new postgresql connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    function pgsql($host, $dbname, $username = null, $password = null)
    {
        return $this->connect("pgsql:host={$host};dbname={$dbname};user={$username};password={$password}");
    }

    /**
     * Create new ms-sqlserver connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    function mssql($host, $dbname, $username = null, $password = null)
    {
        return $this->connect("mssql:host={$host};dbname={$dbname}", $username, $password);
    }

    /**
     * Create new oracle connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    function oracle($host, $dbname, $username = null, $password = null)
    {
        return $this->connect("oci:dbname=//{$host}/{$dbname}", $username, $password);
    }

    /**
     * Gets the current used driver
     * @return string
     */
    function driver()
    {
        return strtolower($this->driver);
    }

    /**
     * Gets the current used database
     * @return string
     */
    function database()
    {
        return $this->database;
    }

    /**
     * Execute sql statement
     * This is prepare + execute
     * @param   string    $statement
     * @param   mixed     $inputs
     * @return  False | object
     */
    function query($statement, $inputs = null)
    {
        try 
        {
            $this->stmnt = $this->prepare($statement);
            if(!$this->stmnt) return FALSE;
            return ((bool) ( $this->stmnt->execute((array) $inputs) ) ? $this : false);
        } catch(PDOException $e) {
            throw new Exception($e->getMessage());
            return false;
        }
    }

    /**
     * Basic insert/replace statement
     * @param   string  $table
     * @param   array   $inserts
     * @return  dbquery
     */
    function insert($table, array $inserts, $replace = false)
    {
        // insert or replace
        $s = $replace ? 'REPLACE' : 'INSERT';
        
        // multiple inserts
        if(isset($inserts[1]) and is_array($inserts[1])) {
            $cols   =   implode(', ', (array) $inserts[0]); array_shift($inserts);
            $inputs =   array();
            $vals   =   implode(', ', array_fill(1, count($inserts), '('.implode(', ', array_fill(1, count($inserts[0]), '?')).')'));
            
            foreach($inserts as &$i) {
                $inputs = ($inputs) + ($i);
            }
            
            return $this->query("$s INTO $table($cols) VALUES $vals", $inputs);
        }
        
        // single insert
        else {
            $cols = implode(', ', array_keys($inserts));
            $inputs = array_values($inserts);
            $vals = implode(', ', array_fill(1, count($inputs), '?'));
            
            return $this->query("$s INTO $table($cols) VALUES ($vals)", $inputs);
        }
    }

    /**
     * Basic Update statement
     * @param   string    $table
     * @param   array     $updates
     * @param   string    $where
     * @param   mixed     $inputs
     * @return  dbquery
     */
    function update($table, array $updates, $where = null, $inputs = null)
    {
        $i = (array) array_values($updates);
        $vals = array();
        
        foreach($updates as $k => $v) {
            $vals[] = "$k = ?";
        }
        
        $vals = implode(', ', $vals);
        $inputs = (($i) + ((array) $inputs));
        $where = empty($where) ? null : "WHERE $where ";
        
        return $this->query("UPDATE $table SET $vals $where", $inputs);
    }

    /**
     * Basic delete statement
     * @param   string  $table
     * @param   string  $where
     * @param   mixed   $inputs
     * @return  dbquery
     */
    function delete($table, $where = null, $inputs = null)
    {
        $where = empty($where) ? null : "WHERE $where";
        return $this->query("DELETE FROM $table $where ", (array) $inputs);
    }

    /**
     * Set charset name
     * @param   string name
     * @return  bool
     */
    function charset($name)
    {
        return $this->query("SET CHARSET {$name}");
    }

    /**
     * Gets database tables
     * @param   string  $dbname
     * @return  array
     */
    function tables($dbname = null)
    {
        $dbname = empty($dbname) ? $this->database :$dbname;
        return $this->query("SELECT table_name FROM INFORMATION_SCHEMA.tables WHERE table_schema = ?", $dbname)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Gets database table columns
     * @param   string  $table_name
     * @param   string  $dbname
     * @return  array
     */
    function columns($table_name, $dbname = null)
    {
        $dbname = empty($dbname) ? $this->database :$dbname;
        return $this->query("SELECT column_name FROM INFORMATION_SCHEMA.columns WHERE table_schema = ? and table_name = ?", array($dbname, $table_name))->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Check if table(s) exists or not
     * @param   mixed  $table_name
     * @param   string  $dbname
     * @return  bool
     */
    function table_exists($table_name, $dbname = null)
    {
        $tables = $this->tables($dbname);
        return sizeof(array_intersect($tables, (array) $table_name)) === sizeof((array) $table_name);
    }

    /**
     * Check if table column(s) exists or not
     * @param   string  $table_name
     * @param   mixed   $column
     * @param   string  $dbname
     * @return  bool
     */
    function column_exists($table_name, $column, $dbname = null)
    {
        $columns = $this->columns($table_name, $dbname);
        return sizeof(array_intersect($columns, (array) $column)) === sizeof((array) $column);
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
 * Horus SQL Based Table Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal
 * @since       4.1
 * @copyright   2014 Mohammed Al-Ashaal
 */
class Horus_SQL_Table
{
    /**
     * @var string
     */
    var $table; 

    /**
     * @var resource
     */
    var $db;

    /**
     * Class Constructor
     * @param   resource    $db
     * @param   string      $table [optional]
     * @return  object
     */
    function __construct(Horus_SQL $db, $table = null)
    {
        $this->db = $db;
        $this->table = $table;
    }

    /**
     * Build a new table object
     * @param   string  $name
     * @return  object
     */
    function using($name)
    {
        return new self($this->db, $name);
    }

    /**
     * Add new entry
     * @param   array   $inserts
     * @return  LastInsertId | FALSE
     */
    function add(array $inserts)
    {
        return $this->insert($this->table, $inserts);
    }

    /**
     * Replace an entry
     * @param   array   $replaces
     * @return  LastInsertId | FALSE
     */
    function rep(array $replaces)
    {
        return $this->insert($this->table, $replaces, TRUE);
    }

    /**
     * Delete an entry
     * @param   string  $where
     * @param   mixed   $inputs
     * @return  bool
     */
    function del($where, $inputs)
    {
        return $this->delete($this->table, $where, $inputs);
    }

    /**
     * Edit an entry
     * @param   array   $edits
     * @param   string  $where
     * @param   mixed   $inputs
     * @return  bool
     */
    function edit(array $edits, $where = null, $inputs = null)
    {
        return $this->update($this->table, $edits, $where, $inputs);
    }

    /**
     * Get an entry
     * @param   string  $where
     * @param   mixed   $inputs
     * @param   int     $fetch_style
     * @return  mixed
     */
    function get_one($where, $inputs, $fetch_style = PDO::FETCH_ASSOC)
    {
        if($this->query("SELECT * FROM {$this->table} WHERE $where LIMIT 1", $inputs))
            return $this->fetch((int) $fetch_style);
        else return FALSE;
    }

    /**
     * Get all entries
     * @param   string  $where
     * @param   mixed   $inputs
     * @param   string  $more_sql
     * @param   mixed   $more_inputs
     * @return  query_object
     */
    function get_all($where = null, $inputs = null, $more_sql = null, $more_inputs = null)
    {
        $where = empty($where) ? null : " WHERE $where ";
        return $this->query("SELECT * FROM {$this->table} $where $more_sql", array_merge((array) $inputs, (array) $more_inputs));
    }

    /**
     * Count entries
     * @param   string    $where
     * @param   mixed     $inputs
     * @return  float
     */
    function count($where = null, $inputs = null)
    {
        $where = empty($where) ? null : " WHERE $where ";
        return (float) $this->query("SELECT COUNT(*) FROM {$this->table} AS COUNT $where", $inputs)->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Optimize current table
     * @return bool
     */
    function optimize()
    {
        return $this->query("OPTIMIZE TABLE {$this->table}");
    }

    /**
     * Drop current table
     * @return bool
     */
    function drop()
    {
        return $this->query("DROP TABLE {$this->table}");
    }

    /**
     * Truncate current table
     * @return bool
     */
    function truncate()
    {
        return $this->query("TRUNCATE TABLE {$this->table}");
    }

    /**
     * Gets current table columns
     * @return  array
     */
    function cols()
    {
        return $this->columns($this->table);
    }

    /**
     * Check if the table exists (only if the $column is null)
     * or it will check if the provide column is exists or not.
     * @param   mixed   $column
     * @return  bool
     */
    function exists($column = null)
    {
        if(empty($column)) return $this->table_exists($this->table);
        else return $this->column_exists($this->table, $column);
    }

    /** @ignore */
    function __call($a, $b)
    {
        return call_user_func_array(array($this->db, $a), $b);
    }
}

// -------------------------------

/**
 * Router
 * 
 * A smart simple light router with better routing
 * michanism .
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
Class Horus_Router
{
    /**
     * @var array
     */
    protected $routes       =   array();

    /**
     * @var array
     */
    protected $shortcuts    =   array();

    /**
     * @var string
     */
    protected $base_uri     =   "/";

    /**
     * Constructor
     * @param   int     $simultor
     * @return  void
     */
    function __construct($simultor = FALSE)
    {
        // check $simulteor
        $simultor = $simultor == 2 ?  !isset($_SERVER['STOP_SIMULATOR']) : (bool) $simultor;

        // setting some vars
        $_SERVER['SERVER_URL'] = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . rtrim($_SERVER['SERVER_NAME'], '/') . '/' ;
        $_SERVER['SCRIPT_URL'] = $_SERVER['SERVER_URL'].ltrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/', '/');
        $_SERVER['SCRIPT_URI'] = $_SERVER['SCRIPT_URL'];
        $_SERVER['SCRIPT_NAME'] = '/' . ltrim($_SERVER['SCRIPT_NAME'], '/');
        $_SERVER['REQUEST_URI'] = '/' . ltrim($_SERVER['REQUEST_URI'], '/');

        // simulate ?
        if($simultor == true) {
            $_SERVER['SCRIPT_URI'] .= basename($_SERVER['SCRIPT_NAME']) . '/';
            
            if(!isset($_SERVER['PATH_INFO'])) {
                header("Location: {$_SERVER['SCRIPT_URI']}", TRUE, 302);
            }
        }

        // start preparing for fixing server { path_info }
        $_SERVER['PATH_INFO'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $_SERVER['PATH_INFO'] = trim(urldecode(strip_tags($_SERVER['PATH_INFO'])), "\0");

        // we want only our url not the path too ( /path/to/horus/url/url/url )
        if(stripos($_SERVER['PATH_INFO'], $_SERVER['SCRIPT_NAME']) === 0) {
            $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], strlen($_SERVER['SCRIPT_NAME']));
        } elseif(stripos($_SERVER['PATH_INFO'], dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        // REGEXP shortcuts
        $this->shortcut(array(
            '{num}'      =>  '([0-9\.,]+)',
            '{alpha}'    =>  '([a-zA-Z]+)',
            '{alnum}'    =>  '([a-zA-Z0-9\.]+)',
            '{str}'      =>  '([a-zA-Z0-9-_\.]+)',
            '{any}'      =>  '(.+)',
            '{*}'        =>  '?|(.*?)'
        ));
    }

    /**
     * Methods overloading
     */
    function __call($method, $args)
    {
        $args[] = str_replace('_', '|', $method);
        return call_user_func_array(array($this, '_route'), $args);
    }

    /**
     * Add regex shortcut(s)
     * @param  mixed    $key
     * @param  string   $value
     */
    public function shortcut($key, $value = null)
    {
        $key = !is_array($key) ? array($key => $value) : $key;
        
        foreach($key as $k => &$v) {
            $this->shortcuts[$k] =  $v;
        }
    }

    /**
     * Remove regex shortcut
     * @param  string    $key
     */
    public function unshortcut($key)
    {
        foreach( (array) $key as $k ) {
            unset($this->shortcuts[$k]);
        }
    }

    /**
     * Create a group of routes
     * @param  string       $base_uri
     * @param  callback     $callback
     */
    function with($base_uri, $callback)
    {
        // must be a valid callback
        if(!is_callable($callback)) {
            throw new InvalidArgumentException('With callback must be a valid callback');
        }

        // store the old base and set a new one
        // then apply the callback, at end reset
        // the base again with the old one .
        $old = $this->base_uri;
        $this->base_uri = $base_uri;
        call_user_func($callback);
        $this->base_uri = $old;
    }

    /**
     * Request an internal route and return it's output
     * as string .
     * @param   string  $resource_uri
     * @param   string  $method
     * @param   array   $params
     * @return  string
     */
    function request($resource_uri, $method = 'GET', array $params = array())
    {
        $_SERVER['REQUEST_METHOD'] = $method =  strtoupper($method);

        if($method == 'POST'):
            $_POST = ($_POST) + ($params);
        elseif($method == 'GET'):
            $_GET = ($_GET) + ($params);
        else:
            $_REQUEST['__INPUT__'] =  ($_REQUEST['__INPUT__']) + ($params);
        endif;

        ob_start();
            $this->dispatch($resource_uri);
        return ob_get_clean();
    }

    /**
     * Dispatch uri based routes and print it's output .
     * This method will use the {PATH_INFO} if the 
     * $resource_uri is empty, but what happens here ?
     * - loop over the registered routes 
     * - choose the routes which there method are [current_request_method, ANY] .
     * 
     * @param   string       $resource_uri
     * @return  int
     */
    function dispatch($resource_uri = Null)
    {
        $resource_uri   =   empty($resource_uri) ? $_SERVER['PATH_INFO'] : $resource_uri;
        $cmethod        =   strtoupper($_SERVER['REQUEST_METHOD']);
        $state          =   0;

        foreach( new ArrayIterator($this->routes) as $m => $r ):
            if(in_array($m, array($cmethod, 'ANY'), FALSE)):
                foreach($r as $p => &$c):
                    if( ($args = $this->_matches($resource_uri, $p)) !== FALSE ):
                        call_user_func_array($c, $args);
                        ++ $state;
                    endif;
                endforeach;
            endif;
        endforeach;
        return $state;
    }

    /**
     * Determin if current requested path matches {$pattern}[s]
     * @param   string $pattern
     * @return  bool
     */
    function is($pattern)
    {
        $bu = $this->_prepare($this->base_uri);
        foreach((array) $pattern as $p) {
            $p = str_ireplace
            (
                array_keys($this->shortcuts),
                array_values($this->shortcuts),
                $this->_prepare($bu.ltrim($p,'/'), '/')
            );
            // matches or not ?
            if($this->_matches($_SERVER['PATH_INFO'], $p) !== FALSE)
                return TRUE;
        }
        return FALSE;
    }

    /**
     * Add routes
     * @param   string      $pattern
     * @param   callback    $callback
     * @param   string      $method
     * @return  void
     */
    protected function _route($pattern, $callback, $method = 'ANY')
    {
        // must be a valid callback
        if(!is_callable($callback)) {
            throw new InvalidArgumentException('Routing callback must be a valid callback');
        }

        // handle multiple patterns ?
        if(is_array($pattern)) {
            foreach( $pattern as &$pt ) {
                call_user_func_array(array($this, __FUNCTION__), array($pt, $callback, $method));
            }
            return ;
        }

        // prepare the base_uri
        $base_uri = $this->_prepare($this->base_uri);

        // prepare the pattern
        $pattern = $base_uri . $pattern;
        $pattern = str_ireplace
        (
            array_keys($this->shortcuts),
            array_values($this->shortcuts),
            $this->_prepare($pattern, '/')
        );

        // prepare the supported methods
        $methods = (array) explode('|',  strtoupper($method));
        $methods = empty($methods) ? array('ANY') : $methods;

        // add map
        foreach( $methods as &$method )
        {
            $this->routes[strtoupper($method)][$pattern]  =  $callback;
        }
    }

    /**
     * Check whether a pattern matches a uri or not
     * @param   string $resource_uri
     * @param   string $pattern
     * @return  FALSE | Array
     */
    protected function _matches($resource_uri, $pattern)
    {
        if(preg_match("/^{$pattern}$/", $this->_prepare($resource_uri), $matches)) {
            array_shift($matches);
            $matches[] = pathinfo($resource_uri, PATHINFO_EXTENSION);
            return $matches;
        }
        return FALSE;
    }

    /**
     * Prepare a string (uri, ... etc)
     * @param   string $string
     * @param   string $escape
     * @return  string
     */
    protected function _prepare($string, $escape = "")
    {
        $string = rtrim(ltrim($string, '/'), '/');
        $string = preg_replace('/\/+/', '/', $string);
        $string = addcslashes(empty($string) ? '/' : "/$string/", $escape);
        return $string;
    }
}

// -------------------------------

/**
 * Horus kernel
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
Class Horus extends Horus_Container
{
    /**
     * For simulator
     * @const integer
     */
    const AUTO  =   2;

    /**
     * The output stored here
     * @var string
     */
    var $output = null;

    /**
     * events/hooks array
     * @var array
     */
    var $events = array();

    /**
     * Horus instance stored here
     * @var object
     */
    protected static $instance;

    /**
     * Constructor
     * @param   bool $simulate
     * @return  void
     */
    function __construct($simulate = self::AUTO)
    {
        // get the output buffer
        // ad it to our output, then clean & start new
        $this->output = ob_get_clean();
        ob_start();

        // some core methods
        $this->router       =   new Horus_Router($simulate);
        $this->sql          =   new Horus_SQL;
        $this->sql_table    =   new Horus_SQL_Table($this->sql);

        // some php ini settings
        ini_set('session.hash_function',    1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly',  1);
        ini_set('session.name', '__HORUS__');

        // set some constants
        defined('ROUTE')    or define('ROUTE', rtrim($_SERVER['SCRIPT_URI'], '/') . '/', true);
        defined('URL')      or define('URL', rtrim($_SERVER['SCRIPT_URL'], '/') . '/', true);
        define('DS', DIRECTORY_SEPARATOR, true);
        define('HORUS_START', microtime(1), true);
		define('Horus_Version', (float) '5.0', true);

        // some headers
        header("Content-Type: text/html; charset=UTF-8",    TRUE);
        header("X-Powered-By: Horus/".Horus_Version,   TRUE);

        // some other methods
        $this->e404 = create_function
        ('', '
            @ob_get_clean();
            die("
                <title>404 not found</title>
                <h1>Not found</h1> 
                <p> The requested URL was not found on this server . </p>
            ");
        ');

        // force _POST, _GET to be arrays if they are not
        $_GET   =   (array) $_GET;
        $_POST  =   (array) $_POST;

        // read any other request method params
        parse_str(file_get_contents('php://input'), $_REQUEST['__INPUT__']);

        // store the instance
        self::$instance = $this;
    }

    /**
     * Run the horus application
     * @return void
     */
    function run()
    {
        // only run if not .
        (!$this->ok) or ($this->ok = true);

        // dispatch all routes [between events]
        $this->trigger('horus.dispatch.before');
        $this->router->dispatch() or $this->e404();
        $this->trigger('horus.dispatch.after');

        // get the output
        // prepare then only send if the request is not head
        $this->output .= ob_get_clean();
        $this->trigger('horus.output.before', array($this));
        (strtoupper($_SERVER['REQUEST_METHOD']) == 'HEAD') or print $this->output;
        $this->trigger('horus.output.after', array($this));

        // end
        ob_get_level() < 1 or ob_end_flush();
        exit;
    }

    /**
     * Get horus instance
     * @return  object
     */
    static function instance()
    {
        return self::$instance;
    }

    /**
     * Listen an event
     * @param   string      $tag
     * @param   callback    $callback
     * @param   integer     $prority
     * @return  void
     */
    function listen($tag, $callback, $prority = 0)
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
    function trigger($tag, $params = null)
    {
        if(isset($this->events[$tag])) {
            $filtered = null;
            foreach(new ArrayIterator($this->events[$tag]) as $p => $callbacks) {
                  foreach($callbacks as $id => &$callback) {
                        if(is_callable($callback)) {
                            $filtered = call_user_func_array($callback, (((array) $params) + (array($filtered))));
                            unset($this->events[$tag][$p][$id]);
                        }
                  }
            }
            return $filtered;
        }
        return null;
    }

    /**
     * Access any input key from any request method
     * @param   mixed   $needle
     * @param   mixed   $default
     * @return  mixed
     */
    function input($needle = '*', $default = FALSE)
    {
        
        $inputs = ($_GET) + ($_POST) + ($_REQUEST['__INPUT__']);

        if($needle == '*') return $inputs;
        else return isset($inputs[$needle]) ? $inputs[$needle] : $default;
    }

    /**
     * Register a driectory [and extension] to be
     * autoloaded .
     * @param   string $path
     * @param   string $extension
     * @return  lambada
     */
    function autoload($path, $extension = 'php')
    {
        $GLOBALS['__ext']   =   '.' . ltrim($extension, '.');
        $GLOBALS['__path']  =   realpath($path) . DS;
        spl_autoload_register($func = create_function('$class', '
            $path = $GLOBALS["__path"];
            $extension = $GLOBALS["__ext"];
            $class = rtrim(ltrim(str_replace(array("\\\\", "/", "_"), DS, $class), DS), DS);
            if(!is_file($file = $path . $class . $extension)) {
                $file = $path . $class . DS . basename($class) . $extension;
            }
            if(is_file($file)) include_once $file;
        '));
        return $func;
    }

    /**
     * Debug (show errors) or not ?
     * @param   bool $state
     * @return  void
     */
    function debug($state = TRUE)
    {
        if($state)
            error_reporting(E_ALL);
        else
            error_reporting(0);
    }
}

// -------------------------------
