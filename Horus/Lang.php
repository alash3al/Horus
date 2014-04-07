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
 * Language manager class
 * 
 * Good class to help you in your multi-lang application(s)
 * just give the languges directory and language exstension
 * and start your application translation easily .
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.2.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Lang
{
    /** @ignore */
    protected $langs_dir;
    /** @ignore */
    protected $langs_ext;
    /** @ignore */
    protected $lang = array();
    
    // ------------------------------------------------------------
    
    /**
     * setup settings
     * 
     * @param string $langs_dir         the directory of languages files
     * @param string $langs_extension   the extension of the languages
     * @return object
     */
    function setup($langs_dir, $langs_extension)
    {
        $this->langs_dir = realpath($langs_dir) . DIRECTORY_SEPARATOR;
        $this->langs_ext = '.' . ltrim($langs_extension, '.');
        return $this;
    }
    
    // ------------------------------------------------------------
    
    /**
     * load a language file
     * 
     * @param string $language language filename
     * @return bool
     */
    function load($language)
    {
        if(file_exists($file = $this->langs_dir . $language . $this->langs_ext))
        {
            $this->lang = array_merge($this->lang, (array) require($file));
            return true;
        }
        
        return false;
    }
    
    // ------------------------------------------------------------
    
    /**
     * add new keys => translations to the language array
     * 
     * @param array $trans
     * @return void
     */
    function add(array $trans)
    {
        $this->lang = array_merge($this->lang, $trans);
    }
    
    // ------------------------------------------------------------
    
    /**
     * Get a key/line from language array
     * 
     * @param string $key
     * @return mixed
     */
    function get($key)
    {
        return isset($this->lang[$key]) ? $this->lang[$key] : false;
    }
    
    // ------------------------------------------------------------
    
    /**
     * Translate a subject using the provided language
     * 
     * @param string $subject
     * @return string
     */
    function translate($subject)
    {
        return @str_ireplace
        (
            array_keys($this->lang),
            array_values($this->lang),
            $subject
        );
    }
    
    // ------------------------------------------------------------
}