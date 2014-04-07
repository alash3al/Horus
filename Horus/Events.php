<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     3.0.0
 * @package     Horus
 * @filesource
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
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
    function listen($event, $listener)
    {
        $this->listeners[$event][] = $listener;
    }
    
    /**
     * Trigger/Fire an event
     * 
     * @param string    $event
     * @param mixed     $params
     * @param bool      $reverse
     * @return void
     */
    function trigger($event, $params = null, $reverse = false)
    {
        if($this->has($event)) {
            if( (bool) $reverse) {
                $this->listeners[$event] = array_reverse($this->listeners[$event]);
            }
            
            foreach($this->listeners[$event] as $id => &$callback) {
                if(is_callable($callback)) {
                    call_user_func_array($callback, (array) $params);
                    unset($this->listeners[$event][$id]);
                }
            }
        }
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
    function get()
    {
        return (array) $this->listeners;
    }
        
    /**
     * Remove an event
     * 
     * @param mixed $event
     * @return void
     */
    function remove($event)
    {
        unset($this->listeners[$event]);
    }
}