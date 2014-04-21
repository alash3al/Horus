<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/version-4.0.0.html
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     4.0.0
 * @package     Horus
 * @filesource
 */
 
// -------------------------------------------------------------------

/**
 * Horus Events Class
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    3.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Events
{
    /** @ignore */
    protected $listeners = array();
    
    /**
     * Register new event
     * 
     * @param string    $event
     * @param callback  $listener
     * @return void
     */
    function listen($event, $listener, $priority = 0)
    {
        if(!isset($this->listeners[$event])) {
            $this->listeners[$event] = array();
        }
        
        if($priority < 1) {
            $priority = count($this->listeners[$event]) + 1;
        }
        
        $this->listeners[$event] = array_merge(
            (array) array_slice($this->listeners[$event], 0, (int) ($priority -1)),
            array($listener),
            (array) array_slice($this->listeners[$event], (int) ($priority - 1))
        );
    }
    
    /**
     * Trigger/Fire an event
     * 
     * @param string    $event
     * @param mixed     $params
     * @param bool      $reverse
     * @return void
     */
    function trigger($event, $params = null)
    {
        if($this->has($event)) {
            $filtered = null;
            foreach($this->listeners[$event] as $id => &$callback) {
                if(is_callable($callback)) {
                    $filtered = call_user_func_array($callback, array_merge((array) $params, array($filtered)));
                    unset($this->listeners[$event][$id]);
                }
            }
            return $filtered;
        }
        return null;
    }
    
    /**
     * Check if an event is exists
     * 
     * @param string $event
     * @return bool
     */
    function has($event)
    {
        return isset($this->listeners[$event]);
    }
    
    /**
     * Get all events
     * 
     * @return array
     */
    function get($event = null)
    {
        return empty($event) ? (array) $this->listeners : (array) @$this->listeners[$event];
    }
        
    /**
     * Remove an event
     * 
     * @param mixed $event
     * @return void
     */
    function remove($event, $listner = null)
    {
        if(!isset($this->listeners[$event])) $this->listeners[$event] = array();
        if(empty($listner))unset($this->listeners[$event]);
        else foreach($this->listeners[$event] as $id => &$l)
                if($l == $listner)
                    unset($this->listeners[$event][$id]);
    }
}