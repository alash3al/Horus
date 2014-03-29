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

/**
 * Horus ORM
 * 
 * Simple Object Rational Mapper
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.3.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_DB_ORM
{
    /** @ignore */
    protected $db;
    /** @ignore */
    public $table;
    /** @ignore */
    protected $sql;
    /** @ignore */
    protected $inputs = array();
    /** @ignore */
    protected $vars = array();
    
    // -------------------------------------------------------
    
    /**
     * Constructor
     * 
     * @param object $horus_db instance of Horus_DB
     * @return
     */
    function __construct(Horus_DB $horus_db)
    {
        $this->db = $horus_db;
    }
    
    // -------------------------------------------------------
    
    /**
     * Choose the table to work with
     * 
     * @param string $table
     * @return object
     */
    function on($table)
    {
        $this->table = $table;
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Single insert statement
     * 
     * @param array $cols_values    array of column => value
     * @return object
     */
    function insert(array $cols_values)
    {
        $this->reset();
        
        $into = implode(', ', array_keys($cols_values));
        $inputs = array_values($cols_values);
        $values = implode(', ', array_fill(1, count($inputs), '?'));
        
        $this->sql = "INSERT INTO {$this->table} ({$into}) VALUES({$values})";
        $this->inputs($inputs);
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * multiple insert statement
     * 
     * @param array $cols_values    first row of array contains array of columns, others arrays of values
     * @return object
     */
    function minsert(array $cols_values, $sqlite = false)
    {
        $this->reset();
        
        $into = implode(', ', $cols_values[0]);
        array_shift($cols_values);
        
        if($sqlite == true) {
            $values = implode(' UNION ', array_fill(1, count($cols_values), ' SELECT '.implode(', ', array_fill(1, count($cols_values[1]), '?'))));
        } else {
            $values = ' VALUES ' . implode(', ', array_fill(1, count($cols_values), '('.implode(', ', array_fill(1, count($cols_values[1]), '?')).')'));
        }
        
        $inputs = array();

        foreach($cols_values as &$v) {
            $inputs = array_merge($inputs, $v);
        }
        
        $this->sql = "INSERT INTO {$this->table} ({$into}) {$values}";
        $this->inputs($inputs);
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Update statement
     * 
     * @param array $cols_values array of column => value
     * @return object
     */
    function update(array $cols_values)
    {
        
        $this->reset();
        
        $tmp = array();
        
        foreach($cols_values as $k => &$v) {
            $tmp[] = "{$k} = ?";
        } 
        
        $tmp = implode(', ', $tmp);
        
        $this->sql = "UPDATE {$this->table} SET {$tmp}";
        $this->inputs(array_values($cols_values));
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Delete statement
     * 
     * @return object
     */
    function delete()
    {
        $this->reset();
        
        $this->sql = "DELETE FROM {$this->table} ";
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Select statement
     * 
     * @param mixed $columns   array or string of columns to select
     * @return object
     */
    function select($columns = '*')
    {
        $this->reset();
        
        $columns = implode(', ', (array) $columns);
        $this->sql = "SELECT {$columns} FROM {$this->table} ";
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Where statement
     * 
     * @param string $stmnt
     * @param array $inputs
     * @return
     */
    function where($stmnt, $inputs = null)
    {
        $this->sql .= " WHERE {$stmnt} ";
        $this->inputs((array) $inputs);
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Order By statement
     * 
     * @param string $by
     * @param string $type
     * @return object
     */
    function order($by, $type = 'DESC')
    {
        $this->sql .= " ORDER BY {$by} {$type} ";
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Limit statement
     * 
     * @param int $start
     * @param int $end
     * @return object
     */
    function limit($start, $end = null)
    {
        if(is_null($end)) {
            $this->sql .= " LIMIT {$start} ";
        } else {
            $this->sql .= " LIMIT {$start}, {$end} ";
        }
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Add new inputs (binds)
     * 
     * @param mixed $inputs
     * @return object
     */
    function inputs($inputs)
    {
        $this->inputs = array_merge($this->inputs, (array) $inputs);
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Custome sql statement
     * 
     * @param string $sql
     * @param mixed $inputs
     * @return object
     */
    function sql($sql, $inputs = null)
    {
        $this->sql .= " {$sql} ";
        $this->inputs((array) $inputs);
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Reset every thing
     * 
     * @return object
     */
    function reset()
    {
        $this->sql = null;
        $this->inputs = null;
        $this->inputs = array();
        
        return $this;
    }
    
    // -------------------------------------------------------
    
    /**
     * Execute the generated statement
     * 
     * @return bool
     */
    function end()
    {
        $result = (bool) $this->query($this->sql, (array) $this->inputs);
        $this->reset();
        
        return (bool) ($result == true and $this->rowCount() > 0);
    }
    
    // -------------------------------------------------------
    
    /**
     * Show generated sql statement
     * 
     * @return string
     */
    function getSQL()
    {
        return $this->sql;
    }
    
    // -------------------------------------------------------
    
    /**
     * get arrat of the generated bound values
     * 
     * @return array
     */
    function getInputs()
    {
        return (array) $this->inputs;
    }
    
    // -------------------------------------------------------
    
    /** @ignore */
    function __call($name, $args)
    {
        if(isset($this->vars[$name])) {
            return call_user_func_array($this->vars[$name], $args);
        }
        
        return call_user_func_array(array($this->db, $name), $args);
    }
    
    // -------------------------------------------------------
    
    /** @ignore */
    function __set($name, $value)
    {
        $this->vars[$name] = $value;
    }
    
    // -------------------------------------------------------
    
    /** @ignore */
    function __isset($name)
    {
        return isset($this->vars[$name]);
    }
    
    // -------------------------------------------------------
    
    /** @ignore */
    function __unset($name)
    {
        unset($this->vars[$name]);
    }
    
    // -------------------------------------------------------
}
