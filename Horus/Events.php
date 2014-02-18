<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.2.0
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
     * @param int           $priority       the event priority
     * @return bool
     */
    function listen($tag, $callback, $priority = 0)
    {
        if(!is_callable($callback)) return false;
        $this->events[$tag][(int)$priority][] = $callback;
        ksort($this->events[$tag]);
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
        
        foreach((array)$this->events[$tag] as $priority)
        {
            if(!empty($priority))
            {
                foreach((array)$priority as $callback)
                {
                    $result = call_user_func_array($callback, $eventArgs);
                }
            }
        }
        return $result;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get Events
     * 
     * Get array of events of certain tag
     * 
     * @param string $tag
     * @return array
     */
    function getEvents($tag)
    {
        return isset($this->events[$tag]) ? (array)$this->events[$tag] : array();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Clear Events
     * 
     * @param string $tag
     * @return void
     */
    function flushEvents($tag)
    {
        unset($this->events[$tag]);
    }
}