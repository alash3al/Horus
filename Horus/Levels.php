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
 * Horus Levels Class
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    2.1.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Levels
{
    /** @ignore*/
    protected $key = 'ulevel';
    /** @ignore*/
    protected $levels = array();
    /** @ignore*/
    protected $status = false;
    
    // ---------------------------------------------
    
    /**
     * SET/GET the session user key
     * 
     * @param string $key
     * @return string
     */
    function key($key = null)
    {
        return is_null($key) ? $this->key : $this->key = $key;
    }
    
    // ---------------------------------------------
    
    /**
     * Define new user level(s)
     * 
     * @param mixed $levels
     * @return void
     */
    function define($levels)
    {
        foreach((array) $levels as $l){
            $this->levels[$l] = count($this->levels) + 1;
        }
    }
    
    // ---------------------------------------------
    
    /**
     * UnDefined user level(s)
     * 
     * @param mixed $levels
     * @return void
     */
    function undefine($levels)
    {
        if($levels == '*') {
            return $this->levels = array();
        }
        
        foreach((array) $levels as $l) {
            unset($this->levels[$l]);
        }
    }
    
    // ---------------------------------------------
    
    /**
     * Allow some level(s) for a page
     * 
     * @param mixed $levels
     * @return bool
     */
    function allow($levels)
    {
        if(empty($_SESSION[$this->key])) {
            return $this->status = false;
        }
        
        if($levels == '*') {
            $levels = $this->levels;
        }
        
        if(!in_array($_SESSION[$this->key], (array) $levels)) {
            return $this->status = false;
        }
        
        return $this->status = true;
    }
    
    // ---------------------------------------------
    
    /**
     * Check whether the user is allowed or not
     * 
     * @return bool
     */
    function verify()
    {
        return (bool) $this->status;
    }
}