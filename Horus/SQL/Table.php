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
 * Horus SQL Based Table Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal
 * @since       4.1.0
 * @copyright   2014 Mohammed Al-Ashaal
 */
class Horus_SQL_Table
{
    var $table, $db;

    // -----------------------------------

    /**
     * Class Constructor
     * 
     * @param   resource    $db
     * @param   string      $table [optional]
     * @return  object
     */
    function __construct(Horus_SQL $db, $table = null)
    {
        $this->db = $db;
        $this->table = $table;
    }

    // -----------------------------------

    /**
     * Build a new table object
     * 
     * @param   string  $name
     * @return  object
     */
    function using($name)
    {
        return new self($this->db, $name);
    }

    // -----------------------------------

    /**
     * Add new entry
     * 
     * @param   array   $inserts
     * @return  LastInsertId | FALSE
     */
    function add(array $inserts)
    {
        return $this->insert($this->table, $inserts);
    }

    // -----------------------------------

    /**
     * Replace an entry
     * 
     * @param   array   $replaces
     * @return  LastInsertId | FALSE
     */
    function rep(array $replaces)
    {
        return $this->insert($this->table, $replaces, TRUE);
    }

    // -----------------------------------

    /**
     * Delete an entry
     * 
     * @param   string  $where
     * @param   mixed   $inputs
     * @return  bool
     */
    function del($where, $inputs)
    {
        return $this->delete($this->table, $where, $inputs);
    }

    // -----------------------------------

    /**
     * Edit an entry
     * 
     * @param   array   $edits
     * @param   string  $where
     * @param   mixed   $inputs
     * @return  bool
     */
    function edit(array $edits, $where = null, $inputs = null)
    {
        return $this->update($this->table, $edits, $where, $inputs);
    }

    // -----------------------------------

    /**
     * Get an entry
     * 
     * @param   string  $where
     * @param   mixed   $inputs
     * @param   int     $fetch_style
     * @return  mixed
     */
    function get_one($where, $inputs, $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE $where LIMIT 1", $inputs)->fetch((int) $fetch_style);
    }

    // -----------------------------------

    /**
     * Get all entries
     * 
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

    // -----------------------------------

    /**
     * Count entr(y/ies)
     * 
     * @param   string    $where
     * @param   mixed     $inputs
     * @return  float
     */
    function count($where = null, $inputs = null)
    {
        $where = empty($where) ? null : " WHERE $where ";
        return (float) $this->query("SELECT COUNT(*) FROM {$this->table} AS COUNT $where", $inputs)->fetch(PDO::FETCH_COLUMN);
    }

    // -----------------------------------

    /**
     * Optimize current table
     * 
     * @return bool
     */
    function optimize()
    {
        return $this->query("OPTIMIZE TABLE {$this->table}");
    }

    // -----------------------------------

    /**
     * Drop current table
     * 
     * @return bool
     */
    function drop()
    {
        return $this->query("DROP TABLE {$this->table}");
    }

    // -----------------------------------

    /**
     * Truncate current table
     * 
     * @return bool
     */
    function truncate()
    {
        return $this->query("TRUNCATE TABLE {$this->table}");
    }

    // -----------------------------------
    
    /**
     * Gets current table columns
     * 
     * @return  array
     */
    function cols()
    {
        return $this->columns($this->table);
    }

    // -----------------------------------

    /**
     * Check if current table is exists
     * 
     * @param   mixed   $column
     * @return  bool
     */
    function exists($column = null)
    {
        if(empty($column)) return $this->table_exists($this->table);
        else return $this->column_exists($this->table, $column);
    }

    // -----------------------------------

    /** @ignore */
    function __call($a, $b)
    {
        return @call_user_func_array(array($this->db, $a), $b);
    }
}