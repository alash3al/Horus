<?php

class Horus_Levels
{
    protected $key = 'ulevel';
    protected $levels = array();
    protected $status = false;
    
    // ---------------------------------------------
    
    function key($key = null)
    {
        return is_null($key) ? $this->key : $this->key = $key;
    }
    
    // ---------------------------------------------
    
    function define($levels)
    {
        foreach((array) $levels as $l){
            $this->levels[$l] = count($this->levels) + 1;
        }
    }
    
    // ---------------------------------------------
    
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
    
    function get($levels)
    {
        if($levels == '*') {
            $levels = array_keys($this->levels);
        }
        
        if(!is_array($levels)) {
            return @$this->levels[$levels];
        }
        
        $l = array();
        
        foreach($levels as $level) {
            @$l[] = $this->levels[$level];
        }
    }
    
    // ---------------------------------------------
    
    function allow($levels)
    {
        if(empty($_SESSION[$this->key])) {
            return $this->status = false;
        }
        
        if($levels == '*') {
            $levels = $this->levels;
        }
        
        $levels = (array) $levels;
        $allowed = array();
        
        foreach($levels as &$level) {
            @$allowed[] = $this->levels[$level];
        }
        
        if(!in_array($_SESSION[$this->key], (array) $allowed)) {
            return $this->status = false;
        }
        
        return $this->status = true;
    }
    
    // ---------------------------------------------
    
    function verify()
    {
        return (bool) $this->status;
    }
}