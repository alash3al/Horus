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
 * Horus Common Helpers
 * 
 * This contains some mixed useful helpers
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
 
// -------------------------------------------------------------------

if(!function_exists('dump'))
{
    /**
     * Dump variable in human readable way 
     * 
     * @param mixed $var
     * @param bool $var_dump
     * @return
     */
    function dump($var, $var_dump = false)
    {
        echo '<pre>';
        
        if($var_dump == true)
            var_dump($var);
        else
            print_r($var);
        
        echo '</pre>';
    }
}

// --------------------------------------------------------------------

if(!function_exists('headeri'))
{
    /**
     * Improved header function
     * 
     * @param string    $string
     * @param bool      $replace
     * @param int       $http_response_code
     * @return void
     */
    function headeri($string, $replace = false, $http_response_code = null)
    {
        Horus::getInstance()->http->status((int) $http_response_code);
        Horus::getInstance()->http->header($string, $replace);
    }
}

// --------------------------------------------------------------------

if(!function_exists('go'))
{
    /**
     * Redirect to ...
     * 
     * @param string    $to     the uri to redirect to
     * @param integer   $using  [302, 301, js, html]
     * @return void
     */
    function go($to, $using = 302)
    {
        if(headers_sent()) 
            return call_user_func_array(__FUNCTION__, array($to, 'html'));
        
        switch(strtolower($using)):
            case 'html':
                echo('<meta http-equiv="refresh" content="0; URL='.$to.'">');
                break;
            case 'js':
                echo('<script type="text/javascript">window.location="'.$to.'";</script>');
                break;
            default:
                Horus::getInstance()->http->redirect($to, $using);
        endswitch;
        
        exit(0);
    }
}

// --------------------------------------------------------------------

if(!function_exists('cURL'))
{
    /**
     * Quick cURL Usage
     * 
     * @param string $url               the url 
     * @param string $curl_options      array of curl options, default is empty
     * @return array('content', 'info')
     */
    function cURL($url, array $curl_options = array())
    {
        if(!function_exists('curl_init'))
            return array('content' => @file_get_contents($url, false), 'info' => array());
        
        $ch = curl_init($url);
        
        if(curl_errno($ch)) return false;
        
        if(empty($curl_options))
            $curl_options = array(CURLOPT_RETURNTRANSFER => true);
        
        curl_setopt_array($ch, $curl_options);
        
        $r['content']  = curl_exec($ch);
        $r['info'] = curl_getinfo($ch);
        
        curl_close($ch);
        
        return $r;
    }
}

// -------------------------------------------------------------------

if(!function_exists('session_init'))
{
    /**
     * session_init()
     * 
     * @param integer   $lifetime       session lifetime
     * @param bool      $regenerate_id  regenrate the session id ?
     * @return void
     */
    function session_init($lifetime = 0, $regenerate_id = true)
    {
        if($lifetime < 1) {
            $lifetime = 900;
        }
        
        if(session_id() !== '') {  
            return null;
        }
        
        session_set_cookie_params((int)$lifetime);
        session_start();
        
        if($regenerate_id == true) {
            @session_regenerate_id(true);
        }
        
        if(empty($_SESSION['{horus.session_ttl}'])) {
            $_SESSION['{horus.session_ttl}'] = time() + (int)$lifetime;
        }
        
        $_SESSION['{horus.session_endAfter}'] = $_SESSION['{horus.session_ttl}'] - time() ;
        
        if(time() >= $_SESSION['{horus.session_ttl}']) {
            session_end();
        }
    }
}
 
// -------------------------------------------------------------------

if(!function_exists('session_end'))
{
    /**
     * Destroy session
     * 
     * @return void
     */
    function session_end()
    {
        if(session_id() == '') {
            return;
        }
        
        session_unset();
        session_destroy();
    }
}

// -------------------------------------------------------------------

if(!function_exists('server'))
{
    /**
     * Deal with $_SERVER array
     * 
     * @param string $key
     * @return mixed
     */
    function server($key = null)
    {
        $key = strtoupper(str_replace(array('.', '-', ' ', '/'), '_', $key));
        return (empty($key) ? $_SERVER : @$_SERVER[$key]);
    }
}

// -------------------------------------------------------------------

if(!function_exists('isHttps'))
{
    /**
     * Is the current request under https ?
     * 
     * @return bool
     */
    function isHttps()
    {
        return (bool)(isset($_SERVER['HTTPS']) and strtolower($_SERVER['HTTPS']) !== 'off');
    }
}
 
// -------------------------------------------------------------------

if(!function_exists('isAjax'))
{
    /**
     * Is the current request is ajax ?
     * 
     * @return bool
     */
    function isAjax()
    {
        return (bool)(
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) and 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
    }
}
 
// -------------------------------------------------------------------

if(!function_exists('isApache'))
{
    /**
     * Is the server is apache ?
     * 
     * @return bool
     */
    function isApache()
    {
        return function_exists('apache_get_version');
    }
}
 
// -------------------------------------------------------------------

if(!function_exists('isCli'))
{
    /**
     * Is the script running under Command Line ?
     * 
     * @return bool
     */
    function isCli()
    {
        return (bool)(strtolower(php_sapi_name()) === 'cli' or defined('STDIN'));
    }
}
 
// -------------------------------------------------------------------

if(!function_exists('isCgi'))
{
    /**
     * Is the script running under Command Line ?
     * 
     * @return bool
     */
    function isCgi()
    {
        return (bool)(stripos(php_sapi_name(), 'cgi', 0) !== false);
    }
}
 
// -------------------------------------------------------------------

if(!function_exists('uri'))
{
    /**
     * Generate a routed uri
     * 
     * @param string $to the location
     * @return string
     */
    function uri($to = null)
    {
        return rtrim(server('script uri'), '/') . '/' . ltrim($to, '/');
    }
}
 
// -------------------------------------------------------------------

if(!function_exists('asset'))
{
    /**
     * Generate direct url to an asset file [css, img, ... etc]
     * 
     * @param string $asset the asset filename
     * @return string
     */
    function asset($asset = null)
    {
        return rtrim(server('script url'), '/') . '/' . ltrim($asset, '/');
    }
}

// -------------------------------------------------------------------

if(!function_exists('random_str'))
{
    /**
     * Generate random string with certain length
     * 
     * @param integer $length
     * @return string
     */
    function random_str($length = 5)
    {
        return (string) substr(sha1(md5(microtime().uniqid())), 0, (int)$length);
    }
}

// -------------------------------------------------------------------

if(!function_exists('random_serial'))
{
    /**
     * Generate random serial number
     * 
     * @param integer   $serials_count
     * @param integer   $blocks_count
     * @param integer   $block_size
     * @param string    $separaor
     * @return string
     */
    function random_serial($serials_count = 1, $blocks_count = 5, $block_size = 5, $separaor = '-')
    {
        if($serials_count < 1) $serials_count = 1;
        
        $serials = array();
        
        for($i = 1; $i <= (int)$serials_count; ++$i)
        {
            $x = sha1(uniqid().time().microtime()).sha1(uniqid().time().microtime());
            $x = implode($separaor, array_slice(str_split($x, (int) $block_size), 0, (int) $blocks_count));
            $serials[] = strtoupper($x);
            unset($x);
        }
        
        if($serials_count === 1) return $serials[0];
        else return $serials;
    }
}

// -------------------------------------------------------------------

if(!function_exists('limit_words'))
{
    /**
     * Limit words number in a subject
     * 
     * @param string    $subject
     * @param int       $offset
     * @param int       $limit
     * @param string    $ends
     * @return string
     */
    function limit_words($subject, $offset, $limit, $ends = ' ...')
    {
        return implode(' ', array_slice(explode(' ', $subject),(int) $offset,(int) $limit)) . $ends;
    }
}

// -------------------------------------------------------------------

if(!function_exists('array_insert'))
{
    /**
     * insert array into array
     * 
     * insert an array or key into a certain position 
     * in another array
     * 
     * @param   array
     * @param   array
     * @param   int 
     * @return  array
     */
    function array_insert(array $into, $new, $position)
    {
        return (array) array_merge
        (
            (array) array_slice($into, 0, $position),
            (array) $new,
            (array) array_slice($into, $position)
        );
    }
}

// -------------------------------------------------------------------

