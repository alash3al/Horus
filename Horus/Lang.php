<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.3.0
 * @package     Horus
 * @filesource
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
     * Constructor
     * 
     * @param string $langs_dir         the directory of languages files
     * @param string $langs_extension   the extension of the languages
     * @return object
     */
    function __construct($langs_dir, $langs_extension)
    {
        $this->langs_dir = realpath($langs_dir) . DIRECTORY_SEPARATOR;
        $this->langs_ext = '.' . ltrim($langs_extension, '.');
    }
    
    // ------------------------------------------------------------
    
    /**
     * reConfig settings
     * 
     * @param string $langs_dir         the directory of languages files
     * @param string $langs_extension   the extension of the languages
     * @return object
     */
    function reConfig($langs_dir, $langs_extension)
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
            include_once $file;
            
            if(isset($lang)) {
                $this->lang = array_merge($this->lang, $lang);
                return true;
            }
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