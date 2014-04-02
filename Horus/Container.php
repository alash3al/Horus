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
 * Horus Smart Container
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    3.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Container implements ArrayAccess, Countable, IteratorAggregate
{
    protected $vars = array();
    
    function set($key, $value)
    {
        $this->vars[$key] = $value;
    }
    
    function get($key)
    {
        return isset($this->vars[$key]) ? $this->vars[$key] : null;
    }
    
    function call($key, array $args = array())
    {
        return isset($this->vars[$key]) ? call_user_func_array($this->vars[$key], $args) : null;
    }
    
    function exists($key)
    {
        return isset($this->vars[$key]);
    }
    
    function remove($key)
    {
        unset($this->vars[$key]);
    }
    
    function __set($key, $value)
    {
        return $this->set($key, $value);
    }
    
    function __get($key)
    {
        return $this->get($key);
    }
    
    function __isset($key)
    {
        return $this->exists($key);
    }
    
    function __unset($key)
    {
        return $this->remove($key);
    }
    
    function __call($name, $args)
    {
        return $this->call($name, $args);
    }
    
    function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }
    
    function offsetGet($key)
    {
        return $this->get($key);
    }
    
    function offsetExists($key)
    {
        return $this->exists($key);
    }
    
    function offsetUnset($key)
    {
        return $this->remove($key);
    }
    
    function count()
    {
        return count($this->vars);
    }
    
    function getIterator()
    {
        return new ArrayIterator($this->vars);
    }
}