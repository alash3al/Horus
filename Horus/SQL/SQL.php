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
 * Horus SQL Based DBMS Class
 * 
 * Just extends PDO and adds some features
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_SQL extends PDO
{
    /** @ignore */
    protected $stmnt, $driver, $database;
    
    /** @ignore */
    function __construct(){}

    // -------------------------------------------------------

    /**
     * Construtor
     * 
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
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

    // -------------------------------------------------------

    /**
     * Create new mysql connection
     * 
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

    // -------------------------------------------------------

    /**
     * Create new mariaDB connection
     * 
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

    // -------------------------------------------------------
    
    /**
     * Create new sqlite connection
     * 
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    function sqlite($filename, $persistent = false)
    {
        $filename = ($filename == ':temp:') ? (realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'horus-temp-db.sqlite') : $filename;
        $c =  $this->connect("sqlite:{$filename}",null,null,array(PDO::ATTR_PERSISTENT => (bool) $persistent));
        $this->query('pragma synchronous = off;');
        
        return $c;
    }
    
    // -------------------------------------------------------
    
    /**
     * Create new postgresql connection
     * 
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
    
    // -------------------------------------------------------
    
    /**
     * Create new ms-sqlserver connection
     * 
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
    
    // -------------------------------------------------------
    
    /**
     * Create new oracle connection
     * 
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

    // --------------------------------------------------------------------

    /**
     * Gets the current used driver
     * 
     * @return string
     */
    function driver()
    {
        return strtolower($this->driver);
    }

    // --------------------------------------------------------------------

    /**
     * Gets the current used database
     * 
     * @return string
     */
    function database()
    {
        return $this->database;
    }

    // --------------------------------------------------------------------
    
    /**
     * Execute sql statement
     * 
     * This is prepare + execute
     * 
     * @param string    $statement
     * @param mixed     $inputs
     * @return  False | object
     */
    function query($statement, $inputs = null)
    {
        try 
        {
            $this->stmnt = $this->prepare($statement);
            return ((bool) ( $this->stmnt->execute((array) $inputs) ) ? $this : false);
        } catch(PDOException $e) {
            throw new Exception($e->getMessage());
            return false;
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Basic insert/replace statement
     * 
     * @param string $table
     * @param array $inserts
     * @return dbquery
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
                $inputs = array_merge($inputs, $i);
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
    
    // --------------------------------------------------------------------
    
    /**
     * Basic Update statement
     * 
     * @param string $table
     * @param array $updates
     * @param string $where
     * @param mixed $inputs
     * @return dbquery
     */
    function update($table, array $updates, $where = null, $inputs = null)
    {
        $i = array_values($updates);
        $vals = array();
        
        foreach($updates as $k => $v) {
            $vals[] = "$k = ?";
        }
        
        $vals = implode(', ', $vals);
        $inputs = array_merge($i, (array) $inputs);
        $where = empty($where) ? null : "WHERE $where ";
        
        return $this->query("UPDATE $table SET $vals $where", $inputs);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Basic delete statement
     * 
     * @param string $table
     * @param string $where
     * @param mixed $inputs
     * @return dbquery
     */
    function delete($table, $where = null, $inputs = null)
    {
        $where = empty($where) ? null : "WHERE $where";
        return $this->query("DELETE FROM $table $where ", (array) $inputs);
    }

    // -----------------------------------

    /**
     * Set charset name
     * 
     * @param   string name
     * @return  bool
     */
    function charset($name)
    {
        return $this->query("SET CHARSET {$name}");
    }

    // -----------------------------------
    
    /**
     * Gets database tables
     * 
     * @param   string  $dbname
     * @return  array
     */
    function tables($dbname = null)
    {
        $dbname = empty($dbname) ? $this->database :$dbname;
        return $this->query("SELECT table_name FROM INFORMATION_SCHEMA.tables WHERE table_schema = ?", $dbname)->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Gets database table columns
     * 
     * @param   string  $table_name
     * @param   string  $dbname
     * @return  array
     */
    function columns($table_name, $dbname = null)
    {
        $dbname = empty($dbname) ? $this->database :$dbname;
        return $this->query("SELECT column_name FROM INFORMATION_SCHEMA.columns WHERE table_schema = ? and table_name = ?", array($dbname, $table_name))->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Check if table(s) exists or not
     * 
     * @param   string  $table_name
     * @param   string  $dbname
     * @return  bool
     */
    function table_exists($table_name, $dbname = null)
    {
        $tables = $this->tables($dbname);
        return sizeof(array_intersect($tables, (array) $table_name)) === sizeof((array) $table_name);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Check if table column(s) exists or not
     * 
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
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    public function __call($name, $args)
    {
        if(is_callable(array($this->stmnt, $name))) {
            return call_user_func_array(array($this->stmnt, $name), $args);
        }
    }
}