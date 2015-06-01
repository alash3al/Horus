<?php
/**
 * Horus - a PHP 5 micro framework
 * 
 * @package     Horus
 * @copyright   2014 (c) Mohammed Al Ashaal
 * @author      Mohammed Al Ashaal <http://is.gd/alash3al>
 * @link        http://alash3al.github.io/Horus
 * @license     MIT LICENSE
 * @version     12
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
 * The above copyright notice and this permission notice must be
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
namespace Horus;

use \stdClass;

version_compare(PHP_VERSION, '5.4', '<') && die('The minimum required php version is 5.4');

/**
 * Prototype
 *
 * @package     Horus
 * @author      Mohammed Al Ashaal
 * @since       11.0.0
 * 
 * @extends     stdClass
 */
class Prototype extends stdClass
{
    /**
     * Call an internal closure
     * 
     * @param   string  $name
     * @param   array   $args
     * @return  mixed
     */
    public function __call($name, $args)
    {
        return isset($this->{$name}) && is_callable($this->{$name}) ? call_user_func_array($this->{$name}, $args) : null;
    }
}

/**
 * Request
 *
 * @package     Horus
 * @author      Mohammed Al Ashaal
 * @since       11.0.0
 * 
 * @extends     Prototype
 */
class Request extends Prototype
{
    /**
     * The current request method
     * @var string
     */
    public $method;

    /**
     * The current virtual path used in routing
     * @var string
     */
    public $path;

    /**
     * The current query object
     * @var object
     */
    public $query;

    /**
     * The current body object
     * @var object
     */
    public $body;

    /**
     * The cookies object
     * @var object
     */
    public $cookies;

    /**
     * The request hostname 'hostname:port'
     * @var string
     */
    public $hostname;

    /**
     * The server-name 'server-side configurations'
     * @var string
     */
    public $servername;

    /**
     * Constructor
     */
    public function __construct()
    {
        $uri = trim($_SERVER['REQUEST_URI'], '/');
        $me = trim($_SERVER['SCRIPT_NAME'], '/');

        if ( stripos($uri, $me) !== false ) {
            $this->path = substr($uri, strlen($me));
        } else {
            $this->path = $uri;
        }

        if ( strpos($this->path, '?') !== false ) {
            $this->path = trim(strstr($this->path, '?'), '?');
        }

        $this->path = preg_replace('/\/+/', '/', ('/' . $this->path . '/'));

        $_SERVER['PATH_INFO'] = $this->path;

        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->query = json_decode(json_encode($_GET));
        $this->body = json_decode(json_encode($_POST));
        $this->cookies = json_decode(json_encode($_COOKIE));
        $this->hostname = str_replace('/:(.*)$/', "", $_SERVER['HTTP_HOST']);
        $this->servername = empty($_SERVER['SERVER_NAME']) ? $this->hostname : $_SERVER['SERVER_NAME'];
        $this->headers = call_user_func(function(){
            $r = new stdClass;
            foreach ( $_SERVER as $k => $v ) {
                if ( stripos($k, 'http_') !== false ) {
                    $r->{strtolower(substr($k, 5))} = $v;
                }
            }
            return $r;
        });
    }
}

/**
 * Response
 *
 * @package     Horus
 * @author      Mohammed Al Ashaal
 * @since       11.0.0
 * 
 * @extends     Prototype
 */
class Response extends Prototype
{
    /**
     * Constructor
     * 
     * @param   Request     $req
     */
    public function __construct(Request $req)
    {
        $this->req = $req;
    }

    /**
     * Set new header field(s)
     * 
     * @param   string|array    $field
     * @param   string          $value
     * @return  $this
     */
    public function set($field, $value = "")
    {
        if ( is_array($field) ) {
            foreach ( $field as $f => $v ) {
                $this->set($f, $v);
            }
            return $this;
        }

        $field  =   str_replace(' ', '-', ucwords(strtolower(str_replace(['-', '_'], ' ', $field))));
        $value  =   is_array($value) ? implode('; ', $value) : $value;

        header("{$field}: {$value}", true);

        return $this;
    }

    /**
     * Append new header(s) for a registered header field
     * 
     * @param   string|array    $field
     * @param   string          $value
     * @return  $this
     */
    public function append($field, $value = "")
    {
        if ( is_array($field) ) {
            foreach ( $field as $f => $v ) {
                $this->append($f, $v);
            }
            return $this;
        }

        $field  =   str_replace(' ', '-', ucwords(strtolower(str_replace(['-', '_'], ' ', $field))));
        $value  =   is_array($value) ? implode('; ', $value) : $value;

        header("{$field}: {$value}", false);

        return $this;
    }

    /**
     * Remove header(s)
     * 
     * @param   string|array    $field
     * @return  $this
     */
    public function remove($field)
    {
        if ( is_array($field) ) {
            foreach ( $field as $f ) {
                $this->remove($f);
            }
            return $this;
        }

        header_remove($field);
        return $this;
    }

    /**
     * Set the current status code
     * 
     * @param   integer     $code
     * @return  $this
     */
    public function status($code)
    {
        http_response_code((int) $code);
        return $this;
    }

    /**
     * Output a message/json to the browser
     * 
     * @param   string|array|object     $message
     * @return  $this
     */
    public function send($message)
    {
        if ( is_array($message) || is_object($message) ) {
            return $this->json($message);
        }
        echo $message;
        return $this;
    }

    /**
     * Output a json data to the browser
     * 
     * @param   string|array|object     $data
     * @return  $this
     */
    public function json($data)
    {
        $this->set('content-type', 'application/json; charset=UTF-8');
        echo json_encode($data);
        return $this;
    }

    /**
     * Output a jsonp response
     * 
     * @param   string|array|object     $data
     * @param   string                  $callback
     * @return  $this
     */
    public function jsonp($data, $callback = 'callback')
    {
        $this->set('content-type', 'application/javascript; charset=UTF-8');
        printf('%s(%s);', $callback, json_encode($data));
        return $this;
    }

    /**
     * Send attachment headers
     * 
     * @param   string  $filename
     * @return  $this
     */
    public function attachment($filename)
    {
        $this->set('content-disposition', "attachmanet; filename='{$filename}'");
        $this->set('content-type', 'application/octet-stream');
        return $this;
    }

    /**
     * Force the brwoser to download a file
     * 
     * @param   string  $filepath
     * @param   string  $filename
     * @return  $this
     */
    public function download($filepath, $filename = null)
    {
        if ( ! is_file($filepath) ) {
            return $this;
        }

        $filename = $filename ?: basename($filepath);

        $this->attachment($filename);
        $this->set('content-length', filesize($filepath));

        $file = fopen($filepath, "r");

        fpassthru($file);
        fclose($file);

        return $this;
    }

    /**
     * Send a cookie to the borwser
     * 
     * @param   string  $name
     * @param   string  $value
     * @param   array   $options
     * @return  $this
     */
    public function cookie($name, $value = null, array $options = [])
    {
        $options    =   array_merge(
        [
            'domain'    =>  null,
            'path'      =>  '/' . trim(dirname($_SERVER['SCRIPT_NAME']), '/'),
            'expires'   =>  0,
            'secure'    =>  false,
            'httpOnly'  =>  true
        ], $options);

        setcookie (
            $name,
            $value,
            (int) $options['expires'], 
            $options['path'],
            $options['domain'],
            $options['secure'], 
            $options['httpOnly']
        );

        return $this;
    }

    /**
     * Cache the current page over http for a $ttl
     * 
     * @param   integer     $ttl
     * @return  $this
     */
    public function cache($ttl)
    {
        $last_mod = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? (int) strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;

        if ( (time() - $last_mod) < $ttl ) {
            $this->status(304)->clear()->end();
            return $this;
        }

        $this->set('last-modified', gmdate('D, d M Y H:i:s T', time() + $ttl));

        return $this;
    }

    /**
     * Send 'Expires' header
     * 
     * @param   integer     $when
     * @return  $this
     */
    public function expires($when)
    {
        $this->set('expires', gmdate('D, d M Y H:i:s T', $when));
        return $this;
    }

    /**
     * Output the specified filename(s) to the browser and optionally pass vars to it/them
     * 
     * @param   string  $filename
     * @param   array   $scope
     * @return  $this
     */
    public function render($filename, array $scope = [])
    {
        extract($scope, EXTR_OVERWRITE|EXTR_REFS);

        foreach ( (array) $filename as $_____ ) {
            if ( is_file($_____) ) {
                require $_____;
            }
        }

        return $this;
    }

    /**
     * Redirect to the specified '$target'
     * 
     * @param   string  $target
     * @param   bool    $permenant
     * @return  $this
     */
    public function redirect($target, $permenant = false)
    {
        $code = $permenant ? 301 : 302;

        $this->set('location', $target)->clear()->status($code)->end();

        return $this;
    }

    /**
     * Return a url for a local real file
     * 
     * @param   string  $local_path
     * @param   bool    $secure
     * @return  string
     */
    public function urlFor($local_path, $secure = false)
    {
        return sprintf('%s://%s/%s',
            ($secure ? 'https' : 'http'),
            $this->req->servername,
            preg_replace('/\/+/', '/', ltrim(trim($local_path), '/'))
        );
    }

    /**
     * Return a url for a local virtual path "route"
     * 
     * @param   string  $local_route
     * @param   bool    $clean
     * @param   bool    $secure
     * @return  string
     */
    public function routeFor($local_route, $clean = true, $secure = false)
    {
        return sprintf('%s://%s%s%s',
            ($secure ? 'https' : 'http'),
            $this->req->servername,
            ($clean ? '/' : ('/' . trim($_SERVER['SCRIPT_NAME'], '/') . '/')),
            preg_replace('/\/+/', '/', ltrim(trim($local_route), '/'))
        );
    }

    /**
     * Clear the output
     * 
     * @return  $this
     */
    public function clear()
    {
        ob_clean();
        return $this;
    }

    /**
     * End the response cycle and optionally send some output
     */
    public function end($data = null, $status = null)
    {
        (null !== $status) && $this->status($status);
        (null !== $data) && $this->send($data);
        ob_end_flush();
        exit;
    }
}

/**
 * App
 *
 * @package     Horus
 * @author      Mohammed Al Ashaal
 * @since       11.0.0
 * 
 * @extends     Prototype
 */
class App extends Prototype
{
    /** @ignore */
    protected $req;

    /** @ignore */
    protected $res;

    /** @ignore */
    protected $parent;

    /**
     * Constructor
     * 
     * @param   array   $layers
     */
    public function __construct(array $layers = [])
    {
        $this->locals = new Prototype;
        $this->req = new Request;
        $this->res = new Response($this->req);
        $this->parent = '/';

        foreach ( $layers as $l ) {
            $l($this->req, $this->res, $this);
        }
    }

    /**
     * Register uri(s) listener
     * 
     * @param   string|array    $uri
     * @param   callable        $listener
     * @return  $this
     */
    public function on($uri, callable $listener)
    {
        if ( is_array($uri) ) {
            foreach ( $uri as $u ) {
                $this->on($u, $listener);
            }
            return $this;
        }

        $method = $this->req->method;
        $uri = trim($uri);

        if ( strpos($uri, ' ') !== false ) {
            list($method, $uri) = array_map('trim', explode(' ', $uri));
        }

        $method = explode('|', strtoupper($method));

        if ( in_array($this->req->method, $method) && (($args = $this->is($uri)) !== false) ) {
            call_user_func_array($listener, array_merge([$this->req, $this->res], $args));
        }

        return $this;
    }

    /**
     * Group some listners under parent listener for the specified parent uri
     * 
     * @param   string|array    $parent
     * @param   callable        $listener
     * @return  $this
     */
    public function group($parent, callable $listener)
    {
        if ( is_array($parent) ) {
            foreach ( $parent as $p ) {
                $this->group($p, $listener);
            }
            return $this;
        }

        if ( $this->is($parent, false) !== false ) {
            $old = $this->parent;
            $this->parent = stripcslashes($this->prepare($parent));
            $listener($this->req, $this->res, $this);
            $this->parent = $old;
        }

        return $this;
    }

    /**
     * Listen for certain virtual host(s)
     * 
     * @param   string|array    $hostname
     * @param   array           $listener
     * @return  $this
     */
    public function vhost($hostname, callable $listener)
    {
        if ( is_array($hostname) ) {
            foreach ( $hostname as $h ) {
                $this->vhost($h, $listener);
            }
            return $this;
        }

        $hostname = str_replace('*', '([^\.]+)', trim($hostname));

        if ( preg_match("/^{$hostname}$/i", $this->req->hostname, $m) ) {
            array_shift($m);
            call_user_func_array($listener, array_merge([$this->req, $this->res, $this], $m));
        }

        return $this;
    }

    /** @ignore */
    protected function prepare($uri)
    {
        return str_replace(['/?', '/*'], ['/([^\/]+)', '/(.*)'], preg_replace('/\\\+/', '\\', addcslashes(preg_replace('/\/+/', '/', $this->parent . '/' . $uri . '/'), '/')));
    }

    /** @ignore */
    protected function is($uri, $strict = true)
    {
        $uri = $this->prepare($uri);
        $uri = ("/^" . $uri . ($strict ? '$' : "") . "/");

        if ( preg_match($uri, $this->req->path, $m) ) {
            array_shift($m);
            return $m;
        }

        return false;
    }
}
