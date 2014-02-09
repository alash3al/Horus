<?php
/**
 * Horus - a micro PHP 5 framework
 *
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     1.0.0
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

if(!function_exists('flush_buffer'))
{
    /**
     * Flush the output buffer
     * 
     * @return void
     */
    function flush_buffer()
    {
        if(ob_get_level() > 0)
            ob_clean();
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
        if(headers_sent() == true) flush_buffer();
        header((string)$string, (bool)$replace, (int)$http_response_code);
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
                headeri('Location: ' . $to, true, $using);
        endswitch;
        
        exit(0);
    }
}

// --------------------------------------------------------------------

if(!function_exists('http_cache'))
{
    /**
     * Cache web pages over http
     * 
     * @param integer $lastfor  cache ttl
     * @return void
     */
    function http_cache($lastfor = 3600)
    {
        $time = gmdate('D, d M Y H:i:s ', time()) . 'GMT';
        if(!isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) 
        {
            $expires = gmdate('D, d M Y H:i:s ', time() + $lastfor) . 'GMT';
            headeri('Last-Modified: ' . gmdate('D, d M Y H:i:s ', time()) . 'GMT');
            headeri('Cache-Control: public');
            headeri('Expires: '. $expires);
        } 
        else 
        {
            $expires = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) + $lastfor;
            
            if(time() > $expires) 
            {
                $expires = gmdate('D, d M Y H:i:s ', time() + $lastfor) . 'GMT';
                headeri('Last-Modified: ' . gmdate('D, d M Y H:i:s ', time()) . 'GMT');
                headeri('Cache-Control: public');
                headeri('Expires: '. $expires);
            }
            else 
            {
                headeri('HTTP/1.1 304 NOT MOIFIED', true, 304);
                exit();
            }
        }
    }
}

// --------------------------------------------------------------------

if(!function_exists('stop'))
{
    /**
     * Stop horus and set http status
     * 
     * @param int       $http_status_code
     * @param string    $message
     * @return void
     */
    function stop($http_status_code, $message = null)
    {
        headeri('HTTP/1.1 ' . status_code((int) $http_status_code), true, (int) $http_status_code);
        exit($message);
    }
}

// --------------------------------------------------------------------

if(!function_exists('status_code'))
{
    /**
     * Get http status code message
     * 
     * @param mixed $code
     * @return
     */
    function status_code($code)
    {
		$codes =  array(
                        //Informational 1xx
                        100 => '100 Continue',
                        101 => '101 Switching Protocols',
                        //Successful 2xx
                        200 => '200 OK',
                        201 => '201 Created',
                        202 => '202 Accepted',
                        203 => '203 Non-Authoritative Information',
                        204 => '204 No Content',
                        205 => '205 Reset Content',
                        206 => '206 Partial Content',
                        //Redirection 3xx
                        300 => '300 Multiple Choices',
                        301 => '301 Moved Permanently',
                        302 => '302 Found',
                        303 => '303 See Other',
                        304 => '304 Not Modified',
                        305 => '305 Use Proxy',
                        306 => '306 (Unused)',
                        307 => '307 Temporary Redirect',
                        //Client Error 4xx
                        400 => '400 Bad Request',
                        401 => '401 Unauthorized',
                        402 => '402 Payment Required',
                        403 => '403 Forbidden',
                        404 => '404 Not Found',
                        405 => '405 Method Not Allowed',
                        406 => '406 Not Acceptable',
                        407 => '407 Proxy Authentication Required',
                        408 => '408 Request Timeout',
                        409 => '409 Conflict',
                        410 => '410 Gone',
                        411 => '411 Length Required',
                        412 => '412 Precondition Failed',
                        413 => '413 Request Entity Too Large',
                        414 => '414 Request-URI Too Long',
                        415 => '415 Unsupported Media Type',
                        416 => '416 Requested Range Not Satisfiable',
                        417 => '417 Expectation Failed',
                        418 => '418 I\'m a teapot',
                        422 => '422 Unprocessable Entity',
                        423 => '423 Locked',
                        //Server Error 5xx
                        500 => '500 Internal Server Error',
                        501 => '501 Not Implemented',
                        502 => '502 Bad Gateway',
                        503 => '503 Service Unavailable',
                        504 => '504 Gateway Timeout',
                        505 => '505 HTTP Version Not Supported'
						);
                        
        return isset($codes[$code]) ? $codes[$code] : false;
    }
}

// --------------------------------------------------------------------

if(!function_exists('cURL'))
{
    /**
     * Quick cURL Usage
     * 
     * @param string $url           the url 
     * @param string $curl_options  array of curl options, default is empty
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