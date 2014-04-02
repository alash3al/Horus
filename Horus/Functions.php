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
        
        if($var_dump == true) {
            var_dump($var);
        } else {
            print_r($var);
        }
        
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
     * @param integer   $using  [302, 301, js:after-int, html:after-int]
     * @return void
     */
    function go($to, $using = 302)
    {
        if(headers_sent()) 
            return call_user_func_array(__FUNCTION__, array($to, 'html'));
        
        @list($using, $after) = (array) explode(':', $using);
        
        switch(strtolower($using)):
            case 'html':
                echo('<meta http-equiv="refresh" content="'.(int) $after.'; URL='.$to.'">');
                break;
            case 'js':
                echo('<script type="text/javascript">setTimeout(function(){window.location="'.$to.'";}, '.(((int) $after)*1000).');</script>');
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
        
        if(empty($curl_options)) {
            $curl_options = array(CURLOPT_RETURNTRANSFER => true);
        }
        
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
    function session_init($lifetime = 0)
    {
        $regenerate_id = (bool) horus()->config('session_regenerate_id');
        
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
        $rand = '';
        
        $loops = 1;
        
        if($length > 32) {
            $loops = (int) ceil($length/32);
        }
        
        for($i = 0; $i < $loops; ++$i) {
            $rand .= md5(uniqid(rand(0, 100), true));
        }
        
        return (string) $length === '*' ? $rand : substr(str_shuffle($rand), 0, (int)$length);
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
        $strlen = (((int)$blocks_count) * ((int)$block_size));
        
        for($i = 1; $i <= (int)$serials_count; ++$i)
        {
            $x = random_str($strlen, $blocks_count);
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

if(!function_exists('horus'))
{
    /**
     * Get Horus instance
     * 
     * @param string $using
     * @return Horus object
     */
    function horus($using = null)
    {
        return is_null($using) ? Horus::getInstance() : Horus::getInstance()->{$using};
    }
}

// -------------------------------------------------------------------

if(!function_exists('array_start'))
{
    /**
     * Get the first elemnt in the array
     * 
     * @param array $array
     * @return mixed
     */
    function array_start(array $array)
    {
        return reset($array);
    }
}

// -------------------------------------------------------------------

if(!function_exists('array_end'))
{
    /**
     * Get the last element in the array
     * 
     * @param array $array
     * @return mixed
     */
    function array_end(array $array)
    {
        return end($array);
    }
}

// -------------------------------------------------------------------

if(!function_exists('mdefine'))
{
    /**
     * Define multiple constants if not defined
     * 
     * @param array $defines    array of keys => values
     * @return void
     */
    function mdefine(array $defines)
    {
        foreach($defines as $k => &$v) {
            defined($k) or define($k, $v);
        }
    }
}

// -------------------------------------------------------------------

if(!function_exists('mempty'))
{
    /**
     * Check var(s) if they are empty
     * 
     * @return bool
     */
    function mempty()
    {
        foreach(func_get_args() as $var) {
            if(empty($var)) {
                return true;
            }
        }
        
        return false;
    }
}

// -------------------------------------------------------------------

if(!function_exists('halt'))
{
    /**
     * Shortcut to { horus('http')->halt(...) }
     * 
     * @param integer $code
     * @param mixed $message
     * @return void
     */
    function halt($code = 200, $message = null)
    {
        horus()->http->halt((int) $code, $message);
    }
}

// -------------------------------------------------------------------

if(!function_exists('password_hash'))
{
    defined('PASSWORD_BCRYPT') or define('PASSWORD_BCRYPT', 1);
    defined('PASSWORD_DEFAULT') or define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
    
    /**
     * Creates a password hash
     * 
     * @param string    $password   The user's password
     * @param integer   $algo       A password algorithm constant denoting the algorithm to use when hashing the password
     * @param array     $options    An associative array containing options
     * @return string | false
     */
    function password_hash($password, $algo = PASSWORD_DEFAULT, array $options = array())
    {
        if(!function_exists('crypt')) return false;
        
        $defaults = array
        (
            'salt' => random_str(22),
            'cost' => 10,
        );
        
        $options = array_merge($defaults, $options); unset($defaults);
        
        if($options['cost'] > 22) $options['cost'] = 22;
        if($options['cost'] < 5)  $options['cost'] = 5;
        
        switch($algo):
            case PASSWORD_BCRYPT:
                $format = sprintf('$2y$%0d$%s', $options['cost'], $options['salt']);
                break;
            default:
                return false;
        endswitch;

        return crypt($password, $format);
    }
}

// -------------------------------------------------------------------

if(!function_exists('password_get_info'))
{
    /**
     * Returns information about the given hash
     * 
     * @param string $hash      A hash created by password_hash()
     * @return array | false
     */
    function password_get_info($hash)
    {
        $algo = 0;
        $info = array();
        
        if(stripos($hash, '$2y$', 0) !== false) {
            $algo = PASSWORD_BCRYPT;
        }
        
        switch($algo):
            case PASSWORD_BCRYPT:
                $info['algo']       =   PASSWORD_BCRYPT;
                $info['algoname']   =   'bcrypt';
                $info['options']    =   array();
                list($info['options']['cost'], $info['options']['salt']) = sscanf($hash, '$2y$%02d$%s');
                break;
            default:
                return false;
        endswitch;
        
        return $info;
    }
}

// -------------------------------------------------------------------

if(!function_exists('password_needs_rehash'))
{
    /**
     * Checks if the given hash matches the given options
     * 
     * @param string    $hash       A hash created by password_hash()
     * @param integer   $algo       A password algorithm constant denoting the algorithm to use when hashing the password
     * @param array     $options    An associative array containing options
     * @return bool
     */
    function password_needs_rehash($hash, $algo = PASSWORD_DEFAULT, array $options = array())
    {
        $info = password_get_info($hash);
        $defaults = array
        (
            'cost' => 10
        );
        $options = array_merge($defaults, $options);
        
        
        if($info == false) return true;
        else return (bool) !(
            $algo === $info['algo'] and
            $options['cost'] === $info['options']['cost']
        );
    }
}

// -------------------------------------------------------------------

if(!function_exists('password_verify'))
{
    /**
     * Verifies that a password matches a hash
     * 
     * @param string    $password   The user's password
     * @param string    $hash       A hash created by password_hash()
     * @return
     */
    function password_verify($password, $hash)
    {
        return (bool) ( crypt($password, $hash) === $hash );
    }    
}

// -------------------------------------------------------------------

if(!function_exists('paginate'))
{
    /**
     * Tiny Smart Pagination Function 
     * 
     * @param integer   $data_size
     * @param integer   $current_page
     * @param integer   $limit
     * @param string    $link_format
     * @param integer   $max_links
     * @return array | false
     */
    function paginate($data_size, $current_page, $limit = 5, $link_format = '?p=%d', $max_links = 5)
    {
        if($data_size < 1)  return false;
        if($limit < 1) $limit = 1;
        
        $limit              =   $limit;
        $r                  =   array();
        $r['pages_count']   =   ceil( $data_size/$limit );
        $current_page       =   $current_page <= 0 ? 1 : $current_page;
        $current_page       =   $current_page > $r['pages_count'] ? $r['pages_count'] : $current_page;
        $r['start']         =   ($current_page*$limit) - 1;
        $r['limit']         =   $limit;
        
        $nxt = $current_page + 1;
        $prev = $current_page - 1;
        
        if($nxt > $r['pages_count']) $nxt = false;
        if($prev < 1) $prev = false;
        if($max_links > $r['pages_count']) $max_links = $r['pages_count'];
        
        $r['links']['next'] =   !$nxt ? false : sprintf($link_format, $nxt);
        $r['links']['prev'] =   !$prev  ? false : sprintf($link_format, $prev);
        $r['links']['first']=   sprintf($link_format, 1);
        $r['links']['last'] =   sprintf($link_format, $r['pages_count']);
        $r['links']['current']= sprintf($link_format, $current_page);
        
        $x = 1;
        $c = ceil($r['pages_count']/$max_links);
        
        for($i = 1; $i <= $max_links;  ++$i) {
           if($x > $r['pages_count']) break;
           if($current_page >= $max_links) $x = ($current_page+$i) - 1;
             
           $r['links'][$x] = sprintf($link_format, $x); 
           ++$x;
        }
        
        unset($x, $i, $prev, $nxt);
        return $r;
    }
}

// -------------------------------------------------------------------

if(!function_exists('maili'))
{
    /**
     * Mail() improved function
     * 
     * @param string    $from
     * @param string    $to
     * @param string    $subject
     * @param string    $message
     * @param string    $name
     * @param array     $headers
     * @return bool
     */
    function maili($from, $to, $subject, $message, $name = '',array $headers = array())
    {
        $hdrs = array
        (
            'From'          =>  sprintf('%s %s', $name, $from),
            'X-Mailer'      =>  sprintf('PHP/Horus/%s', phpversion().'/'.Horus::VERSION),
            'MIME-Version'  =>  '1.0',
            'Content-type'  =>  'text/html; charset=UTF-8'
        );
        
        $hdrs = array_merge($hdrs, $headers);
        $headers = null;
        
        foreach($hdrs as $k => &$v) {
            $headers .= sprintf('%s: %s %s', $k, $v, PHP_EOL); 
        }
        
        $to = implode(', ', (array) $to);
        
        return (bool) @mail($to, $subject, $message, $headers);
    }
}

// -------------------------------------------------------------------

if(!function_exists('session_started'))
{
    function session_started()
    {
        return (bool) (session_id() !== '');
    }
}

