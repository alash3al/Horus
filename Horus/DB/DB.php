<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.3.0
 * @package     Horus
 * @filesource
 */
 
// -------------------------------------------------------------------


/**
 * Horus DB Class
 * 
 * Just extends PDO and adds some features
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_DB extends PDO
{
    /** @ignore */
    protected $stmnt;
    
    /** @ignore */
    function __construct(){}
    
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
        try {
            parent::__construct($dns, $username, $password, $driver_options);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
     * Create new sqlite connection
     * 
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    function sqlite($filename)
    {
        $filename = ($filename == ':temp:') ? (realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . uniqid() . '.sqlite') : $filename;
        
        return $this->connect("sqlite:{$filename}");
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
     * Execute sql statement
     * 
     * @param string    $statement
     * @param mixed     $inputs
     * @return  bool
     */
    function query($statement, $inputs = null)
    {
        try {
            $this->stmnt = $this->prepare($statement);
            return (bool)$this->stmnt->execute((array) $inputs);
        } catch(PDOException $e) {
            throw new Exception($e->getMessage());
            return false;
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * @ignore
     */
    public function __call($name, $args)
    {
        if(is_callable(array($this->stmnt, $name))) {
            return call_user_func_array(array($this->stmnt, $name), $args);
        }
    }
}