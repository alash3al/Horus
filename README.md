# Horus 15
Horus 15 is a new light and simple version of Horus Framework .

---

# Quick Overview
```php
<?php

	// load Horus
	require "Horus.php";

	(new \Horus\App)->on("/", function(){
		// the first param is an array of key => value
		// for the header field => value
		// the value may be an array "will be appended"
		// the second param "optional" to set the response status code .
		$this->header([
			"x-powered-by" => "Horus"
		], 200);
		echo "Hello World";
	});

```

---

# API Summary
```php

<?php

	require "Horus.php";

	// Initialize Horus with optional configurations
	$app = new \Horus\App([
		// Force all urls/cookies to be secured "https"
		// Note, Horus will try to detect whether the request is done over 
		// https or not, but this will force it to be yes or no 
		// "just if you need"
		"secure" => true,

		// Force horus to generate routes with "/index.php/" in urls
		// this settings is only if your server doesn't support
		// url rewriting
		"index" => "/index.php/"
	]);

	// See the following examples for the usage of the router
	// handle a request that needs the see "Hello GET" to be displayed
	// under only GET method
	$app->on("GET /test", function(){
		echo "Hello GET";
	});
	
	// Display Hello JSON just for a POST request
	$app->on("POST /json", function(){
		$this->header(["content-type" => "application/json"]);
		echo json_encode([
			"message" => "Hello JSON"
		]);
	});

	// named params ?
	$app->on("/post/([a-z]+)/([0-9]+)", function($word, $num){
		echo $word . " : " . $num;
	});

	// execute multiple functions/layers ?
	// each function will be executed after the previous one is ended
	// if a function returned false, the chain will be break,
	// the result of each function will be the last param of the next function in the chain .
	$app->on("/page/([^/]+)", [
		function($page){
			echo "layer 1 <br />";
			return "test"; // or return false to cancel the execution of the next function
		},
		function($page, $result){
			echo $result;
		}
	]);
	
	
	// access the configs
	// you can also create/access anything inside horus
	// because it extends stdClass and adds __call() to it
	$app->configs;

	// callback
	$app->func = function(){
		// your can access horus using
		// $this from here too !
		echo "ok";
	};

	// call it
	$app->func();

	// check whether the request is under https or not
	// it will consider the request secure if you forced it 
	// from the configurations
	$app->secure();

	// i need to get full url for "/assets/css/style.css"
	$app->url("/assets/css/style.css");

	// it will return a url with scheme "http or https" and hostname
	// if you want to change the host ?
	// also if you want to change the scheme from http to https "secure"
	// the last param is for secure or not "https or not"
	// the last param will be automatically set for you using
	// $app->secure()
	$app->url("/assets/css/style.css", "my.domain", false);

	// but if you want to get a url to an internal route ?
	// just change the ->url to ->route
	// also you can change the resulting host and scheme "secure or not"
	$app->route("/post/", "my.domain", false);

	// get Horus instance ?
	Horus::getInstance();

	// how about autoloads ?
	$app->autoload("./vendor/");
	// now play with your libraries !
	// or multiples sources:
	$app->autoload(["./vendor", "./components"]);

	// you can create your own object container using horus new stdClass
	// or extending it .
	$app->obj = new \App\stdClass;
	$app->obj->test = function(){
		// ...
	};
```
