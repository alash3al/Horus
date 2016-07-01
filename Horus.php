<?php
/**
 * Horus "Core" - be simple :)
 * 
 * This version will only work on PHP 7 and higher .
 *
 * @package     Horus
 * @copyright   2014 - 2016 (c) Horus
 * @author      nfinity <nfinity.space>
 * @license     MIT LICENSE
 * @version     v14
 */
Class Horus extends stdClass {
    /**
     * An instance of this class
     * @var Horus
     */
    private static $instance;

    /**
     * Whether to break the routes chain or not
     * @var bool
     */
    private $breakRoutes;

    /**
     * The parent of the current route
     * @var string
     */
    private $parent;

    /**
     * Constructor
     *
     * @param   string  $index
     */
    public function __construct(array $configs = []) {
    	static::$instance = $this;
        $this->configs = array_merge(["index" => "/", "secure" => null], $configs);
    	$_SERVER["PATH_INFO"] = explode("?", $_SERVER["REQUEST_URI"])[0] ?? $_SERVER["REQUEST_URI"];
    	$strip = "/";
    	if ( stripos($_SERVER["PATH_INFO"], $_SERVER["SCRIPT_NAME"]) === 0 ) {
    		$strip = $_SERVER["SCRIPT_NAME"];
    	} else if ( stripos($_SERVER["PATH_INFO"], dirname($_SERVER["SCRIPT_NAME"])) === 0 ) {
    		$strip = dirname($_SERVER["SCRIPT_NAME"]);
    	}
    	$_SERVER["PATH_INFO"] = preg_replace("~/+~", "/", "/" . substr($_SERVER["PATH_INFO"], strlen($strip)) . "/");
        $this->parent = "/";
    }

    /** @ignore */
    public function __call($name, $args) {
    	if ( isset($this->{$name}) ) {
            if ( $this->{$name} instanceof Closure ) {
                return call_user_func_array($this->{$name}->bindTo($this), $args);
            }
    		return call_user_func_array($this->{$name}, $args);
    	}
    	throw new Exception("Undefined method {$name}");
    }

    /** @ignore */
    public function __set($name, $val) {
    	if ( $val instanceof Closure ) {
    		$this->{$name} = $val->bindTo($val);
    	} else {
    		$this->{$name} = $val;
    	}
    }

    /**
     * Return horus instance
     * 
     * @return  Horus
     */
    public static function getInstance() {
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
        foreach ( $field as $f => $v ) {
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
    			$filenames = [
    				$dir . $ds . $class . ".php",
    				$dir . $ds . $class . $ds . basename($class) . ".php"
    			];
                $found = true;
    			foreach ( $filenames as $filename ) {
    				if ( ! is_file($filename) ) {
    					$found = false;
    				} else {
                        $found = true;
    					require_once $filename;
                        return ;
    				}
    			}
                if ( ! $found ) {
                    throw new Exception("Cannot find the class '{$class_orig}'");
                }
    		});
    	}
    	return $this;
    }

    /**
     * Whether the current request maybe under https or not
     * 
     * @return  bool
     */
    public function secure() {
        if ( null !== $this->configs->status && $this->configs->status ) {
            $_SERVER["HTTPS"] = "on";
        }
        if ( isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] !== "off") ) {
            return true;
        }
        return isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && (strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"]) == "https");
    }

    /**
     * Return an url for a local path
     * 
     * @param   string  $path
     * @param   array   $options
     * @param   bool    $secure
     * @return  string
     */
    public function url($path = "", $host = "", $secure = null) {
        $secure = null === $secure ? $this->secure() : $secure;
        $scheme = $secure ? "https://" : "http";
        $host = $host ?? $_SERVER["SERVER_NAME"];
        return $scheme . $host . preg_replace("~/+~", "/", "/" . $path);
    }

    /**
     * Return an url for a local route
     * 
     * @param   string  $path
     * @param   array   $options
     * @param   bool    $secure
     * @return  string
     */
    public function route($path = "", $host = "", $secure = null) {
        return $this->url($this->configs->index . "/" . $path, $host, $secure);
    }

    /**
     * Listen on the requested uri
     * 
     * @param   string      $pattern
     * @param   Callback    $cb
     * @return  $this
     */
    public function on(string $pattern, callable $cb) {
    	if ( $this->breakRoutes === true ) {
    		return $this;
    	}
    	$parts = explode(" ", $pattern, 2);
        if ( sizeof($parts) < 2 ) {
            $method = $_SERVER["REQUEST_METHOD"];
        } else {
            $method = $parts[0];
            $pattern = $parts[1];
        }
        $pattern = preg_replace("~/+~", "/", "/" . $this->parent . "/" . $pattern . "/");
    	if ( ! preg_match("~^{$method}$~i", $_SERVER["REQUEST_METHOD"]) ) {
    		return $this;
    	} else if ( ! preg_match("~^{$pattern}$~", $_SERVER["PATH_INFO"], $m) ) {
    		return $this;
    	}
    	array_shift($m);
    	if ( call_user_func_array($cb->bindTo($this), $m) !== true ) {
            // the callback didn't return true "continue"
            // but returned none-true "stop" .
    		$this->breakRoutes = true;
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
     * Output the specified filename(s) to the browser and optionally pass vars to it/them
     * 
     * @param   string  $tpls
     * @param   array   $ctx
     * @param   bool    $return
     * @return  $this|string
     */
    public function tpl($tpls, array $ctx = [], $return = false) {
        if ( $return ) {
            ob_start();
        }
    	extract($ctx, EXTR_OVERWRITE);
    	foreach ( (array) $tpls as $v ) {
    		if ( ! is_file($v) ) {
    			throw new Exception("Template file cannot be found '{$v}'");
    		}
    		require $v;
    	}
        if ( $return ) {
            return ob_get_clean();
        }
    	return $this;
    }
}
