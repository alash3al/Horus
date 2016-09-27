<?php
/**
 * Horus
 * 
 * This version will only work on PHP 7 and higher .
 *
 * @package     Horus
 * @copyright   2014 - 2016 (c) Horus
 * @author      nfinity <nfinity.space>
 * @license     MIT LICENSE
 * @version     v15
 */
namespace Horus;

/**
 * stdClass
 * 
 * An improved version of PHP stdClass .
 *
 * @package     Horus
 * @author      nfinity <nfinity.space>
 * @license     MIT LICENSE
 * @version     v1.0
 */
class stdClass extends \stdClass {
    /** @ignore */
    public function __call($name, $args) {
        if ( isset($this->{$name}) ) {
            if ( $this->{$name} instanceof \Closure ) {
                return call_user_func_array($this->{$name}->bindTo($this), $args);
            }
            return call_user_func_array($this->{$name}, $args);
        }
        throw new \Exception("Undefined method {$name}");
    }

    /** @ignore */
    public function __set($name, $val) {
        if ( $val instanceof \Closure ) {
            $this->{$name} = $val->bindTo($val);
        } else {
            $this->{$name} = $val;
        }
    }

    /**
     * Constructor
     *
     * @param   array   $data
     */
    public function __construct(array $data = []) {
        $this->import($data);
    }

    /**
     * import an array of data into our context
     *
     * @param   array   $data
     *
     * @return  $this
     */
    public function import(array $data) {
        foreach ( $data as $k => $v ) {
            $this->{$k} = $v;
        }
        return $this;
    }
}

/**
 * App
 * 
 * This version will only work on PHP7 and higher .
 *
 * @package     Horus
 * @author      nfinity <nfinity.space>
 * @license     MIT LICENSE
 * @version     v1.0
 */
class App extends stdClass {
    /**
     * An instance of this class
     * @var Horus
     */
    private static $instance;

    /**
     * Router patterns shortcuts
     * @var array
     */
    private $shortcuts;

    /**
     * the parent of current route context
     * @var string
     */
    private $parent;

    /**
     * whether there is a route matched the request or not
     * @var bool
     */
    private $found;

    /**
     * Constructor
     *
     * @param   array  $configs
     */
    public function __construct(array $configs = []) {
    	static::$instance = $this;
        $this->shortcuts = [];
        $this->configs = new stdClass(array_merge(["index" => "/", "secure" => null], $configs));
        $this->parent = "/";
    	$_SERVER["PATH_INFO"] = explode("?", $_SERVER["REQUEST_URI"])[0] ?? $_SERVER["REQUEST_URI"];
    	$strip = "/";
    	if ( stripos($_SERVER["PATH_INFO"], $_SERVER["SCRIPT_NAME"]) === 0 ) {
    		$strip = $_SERVER["SCRIPT_NAME"];
    	} else if ( stripos($_SERVER["PATH_INFO"], dirname($_SERVER["SCRIPT_NAME"])) === 0 ) {
    		$strip = dirname($_SERVER["SCRIPT_NAME"]);
    	}
    	$_SERVER["PATH_INFO"] = preg_replace("~/+~", "/", "/" . substr($_SERVER["PATH_INFO"], strlen($strip)) . "/");
    }

    /**
     * Return horus instance
     * 
     * @return  App
     */
    public static function getInstance() {
        if ( ! static::$instance instanceof App ) {
            return new App;
        }
    	return static::$instance;
    }

    /**
     * Set http header(s)
     * 
     * @param   array    $field
     * @param   integer  $status
     * @return  $this
     */
    public function header(array $headers, $status = null)  {
        $status && ($status >= 100) && http_response_code($status);
        foreach ( $headers as $f => $v ) {
            $f = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($f))));
            if ( is_array($v) ) {
                foreach ( $v as $v2 ) {
                    header(sprintf("%s: %s", $f, $v2), flase);
                }
            } else {
                header(sprintf("%s: %s", $f, $v), true);
            }
        }
        return $this;
    }

    /**
     * Whether the current request maybe under https or not
     * 
     * @return  bool
     */
    public function secure() {
        if ( null !== $this->configs->secure && $this->configs->secure ) {
            $_SERVER["HTTPS"] = "on";
        }
        if ( isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] !== "off") ) {
            return true;
        }
        return isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && (strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"]) == "https");
    }

    /**
     * return a url to the specified path
     * 
     * @return  string
     */
    public function url($path = "") {
        return (
            ($this->secure() ? "https://" : "http://") .
            $_SERVER["SERVER_NAME"] .
            preg_replace("~/+~", "/", "/" . dirname($_SERVER["SCRIPT_NAME"]) . "/" . $path)
        );
    }

    /**
     * return a route to the specified path
     * 
     * @return  string
     */
    public function route($path = "") {
        return $this->url($this->configs->index . "/" . $path);
    }

    /**
     * Make class in the specified dir(s) be autoloaded
     * 
     * @param   string|array    $src
     * @return  $this
     */
    public function autoload($src) {
    	foreach ( (array) $src as $dir ) {
    		spl_autoload_register(function($class) use($dir) {
    		    $ds = DIRECTORY_SEPARATOR;
    			$class_orig = $class;
    			$class = str_replace("\\", $ds, $class);
                $namespace=str_replace("\\",$ds,__NAMESPACE__);
    			$filenames = [
                    $dir . $ds . $class . ".php",
    				$dir . $ds . $class . $ds . basename($class) . ".php",
                    $dir . $ds . (empty($namespace) ? "" : $namespace."/") . $class . ".php",

    			];
                $found = true;
    			foreach ( $filenames as $filename ) {
    				if ( ! is_file($filename) ) {
    					$found = false;
    				} else {
                        $found = true;
    					require_once($filename);
                        return ;
    				}
    			}
                if ( ! $found ) {
                    throw new \Exception("Cannot find the class '{$class_orig}'");
                }
    		});
    	}
    	return $this;
    }

    /**
     * Create patterns shortcuts
     * 
     * @param   array   $shortcuts
     * @return  $this
     */
    public function shortcut(array $shortcuts) {
        foreach ( $shortcuts as $k => $v ) {
            $this->shortcuts[sprintf("{{%s}}", $k)] = $v;
        }
        return $this;
    }

    /**
     * Listen on the requested uri
     * 
     * @param   string                  $pattern
     * @param   callback|[]callback     $cb
     * @return  $this
     */
    public function on(string $pattern, $cb) {
        if ( $this->found ) {
            return $this;
        }
    	list($method, $pattern) = array_pad(explode(" ", $pattern, 2), -2, $_SERVER["REQUEST_METHOD"]);
        $pattern = preg_replace("~/+~", "/",  "/" . str_ireplace(array_keys($this->shortcuts), array_values($this->shortcuts), "/" . $this->parent . "/" . $pattern) . "/");
    	if ( ! preg_match("~^{$method}$~i", $_SERVER["REQUEST_METHOD"]) || ! preg_match("~^{$pattern}$~", $_SERVER["PATH_INFO"], $m) ) {
    		return $this;
    	}
    	$this->found = true;
    	array_shift($m);
        $call = function($obj, $callback, $args){
            if ( $callback instanceof \Closure ) {
                return call_user_func_array($callback->bindTo($obj), $args);
            } else {
                return call_user_func_array($callback, $args);
            }
        };
        $lastReturn = null;
    	if ( is_callable($cb) ) {
            $call($this, $cb, $m);
        } else if ( is_array($cb) ) {
            foreach ( $cb as $fn ) {
                if ( false === ($lastReturn = $call($this, $fn, array_merge($m, [$lastReturn]))) ) {
                    break;
                }
            }
        }
    	return $this;
    }

    /**
     * Group some routes under the same pattern without the need to repeate anything
     * 
     * @param   string      $pattern
     * @param   Callback    $cb
     * @return  $this
     */
    public function group($pattern, callable $cb) {
        $old = $this->parent;
        $this->parent = preg_replace("~/+~", "/", "/" . $this->parent . "/" . $pattern . "/");
        if ( preg_match("~^" . $this->parent . "~", $_SERVER["PATH_INFO"], $m) ) {
            call_user_func_array($cb->bindTo($this), $m);
        }
        $this->parent = $old;
        return $this;
    }

    /**
     * View the specified html template(s)
     * 
     * @param   []string    $tpl
     * @param   array       $ctx
     * @param   bool        $return
     * @return  $this|string
     */
    public function view($tpl, array $ctx = [], $return = false) {
        if ( $return ) {
            ob_start();
        }
        extract($ctx, EXTR_OVERWRITE);
        foreach ( (array) $tpl as $f ) {
            is_file($f) && require($f);
        }
        if ( $return ) {
            return ob_get_clean();
        }
        return $this;
    }
}


