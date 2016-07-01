# Horus 14 "Core"
Horus 14 is a new version of Horus Framework, this version has been build from scratch to be up-to-date with my new simplicity and also to use the latest PHP7 features .

---

# Quick Overview
```php
<?php

	// load Core "Horus14"
	require "Horus.php";

	(new Horus)->on("/", function(){
		// the first param is an array of key => value
		// for the header field => value
		// the value may be an array "will be appended"
		// the second param "optional" to set the response status code .
		$this->header([
			"x-powered-by" => "Horus/15"
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
	$app = new Horus([
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

	// group routes ?
	$app->group("/api/", function(){
		$this->on("POST /post/", function(){
			// ...
		});
	});

	// display php templates,
	// and also pass some vars as its context
	$var = "test";
	$app->tpl("tpl.php", ["var1" => $var]);

	// but i need to get the tpl as string,
	// don't display !
	$var = "test";
	$app->tpl("tpl.php", ["var1" => $var], true);
	
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
```
