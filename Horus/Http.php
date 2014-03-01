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
 * Horus HTTP Class
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.1.1
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_Http
{
    protected $http_version = '1.1';
    protected $headers = array();
    protected $status = 200;
    protected $output;
    protected $length;
    protected $codes = array
    (
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',
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
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported'
    );
    
    function __construct($http_version = '1.1')
    {
        $this->http_version = $http_version;
    }
    
    // --------------------------------------------------------------------

    /**
     * Send a raw http header
     * 
     * @param string    $string
     * @param bool      $replace
     * @return void
     */
    function header($string, $replace = false)
    {
        $this->headers[$string] = $replace;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * GET/SET Status code
     * 
     * if the code is not valid, it will be '500'
     * 
     * @param int $code
     * @return
     */
    function status($code = null)
    {
        if(is_null($code))
            return $this->status;
        
        if(!isset($this->codes[$code]))
            $code = 500;
        
        if(stripos(php_sapi_name(), 'cgi') !== false)
        {
            $this->status = $code;
            $this->header('Status: '.$this->codes[$code], true);
        }
        else
        {
            $this->status = $code;
            $this->header(sprintf('HTTP/%s %s', $this->http_version, $this->codes[$code]), true);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * SET/Get the output
     * 
     * @param string $output
     * @param bool $replace
     * @return mixed
     */
    function output($output = null, $replace = false)
    {
        if(is_null($output))
            return $this->output;
        
        if($replace == true)
            $this->output = $output;
        else
            $this->output .= $output;
            
        $this->length = strlen($this->output);
        $this->header('Content-Length: '.$this->length, true);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get all sent headers
     * 
     * @return array
     */
    function headers()
    {
        return $this->headers;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Exit application with status code & message
     * 
     * @param integer   $code       any valid http status code
     * @param string    $message    message to show
     * @return coid
     */
    function halt($code, $message = '')
    {
        $this->status($code);
        $this->send($message);
        exit();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Send Headers and Output
     * 
     * @param string    $output     the output to set
     * @param bool      $return     return the final output or display it ?
     * @return mixed
     */
    function send($output = '', $return = false)
    {
        // set the output buffer
        $this->output($output, true);
        
        // loop through headers and send them
        foreach( $this->headers as $k => &$v )
        {
            header($k, $v);
        }
        
        // send the output
        if($return == true)
            return $this->output;
        else
            echo $this->output;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Redirection
     * 
     * @param string    $url    the url to redirect to
     * @param integer   $code   the http status code to send
     * @return void
     */
    function redirect($url, $code = 302)
    {
        $this->header('Location: '.$url, true);
        $this->halt($code);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cache web pages over http
     * 
     * @param integer $lastfor
     * @return void
     */
    function cache($lastfor = 3600)
    {
        $time = gmdate('D, d M Y H:i:s ', time()) . 'GMT';
        if(!isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) 
        {
            $expires = gmdate('D, d M Y H:i:s ', time() + $lastfor) . 'GMT';
            $this->header('Last-Modified: ' . gmdate('D, d M Y H:i:s ', time()) . 'GMT', true);
            $this->header('Cache-Control: public', true);
            $this->header('Expires: '. $expires, true);
        }
        else 
        {
            $expires = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) + $lastfor;
            
            if(time() > $expires) 
            {
                $expires = gmdate('D, d M Y H:i:s ', time() + $lastfor) . 'GMT';
                $this->header('Last-Modified: ' . gmdate('D, d M Y H:i:s ', time()) . 'GMT', true);
                $this->header('Cache-Control: public', true);
                $this->header('Expires: '. $expires, true);
            }
            else 
            {
                $this->header('HTTP/1.1 304 NOT MOIFIED', true);
                $this->halt(304);
            }
        }
    }
}