<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.4.0
 * @package     Horus
 * @filesource
 */
 
// -------------------------------------------------------------------


/**
 * Events
 * 
 * Horus Events Listener / Dispatcher System [Hooks]
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Events
{
    /** @ignore */
    protected $events = array();
    
    /**
     * Listen
     * 
     * Add new event
     * 
     * @param string        $tag            tag/category of the event
     * @param callback      $callback       the event callback
     * @param int           $position       the event position
     * @return bool
     */
    function listen($tag, $callback, $position = 0)
    {
        if(!is_callable($callback)) {
            return false;
        }
    
        if(!isset($this->events[$tag])) {
            $this->events[$tag] = array();
        }
        
        $this->events[$tag] = $this->insert($this->events[$tag], (array) $callback, ($position-1) < 0 ? 0 : $position-1);
        
        return true;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Dispatch
     * 
     * Run events under certain tag/category
     * 
     * @param string    $tag        tag/categry to dispatch
     * @param array     $eventArgs  arguments to pass to the tag events
     * @return void
     */
    function dispatch($tag, array $eventArgs = array())
    {
        if(!isset($this->events[$tag])) return null;
        
        $result = null;
                                              
        foreach((array)$this->events[$tag] as $callback)
        {
            $result = call_user_func_array($callback, $eventArgs);
        }
        
        return $result;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch all events tags
     * 
     * @return array
     */
    function tags()
    {
        return (array) array_keys($this->events);
    }
    
    // --------------------------------------------------------------------
    
    /** @ignore */
    protected function insert(array $into, $new, $position)
    {
        return (array) array_merge
        (
            (array) array_slice($into, 0, $position),
            (array) $new,
            (array) array_slice($into, $position)
        );
            
    }
}