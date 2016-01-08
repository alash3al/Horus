# # Horus 13 [![Build Status](https://scrutinizer-ci.com/g/alash3al/Horus/badges/build.png?b=master)](https://scrutinizer-ci.com/g/alash3al/Horus/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alash3al/Horus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alash3al/Horus/?branch=master)

A simple yet powerful micro-framework for php5 >= php5.4

# # Why a large framework ?
> Why i need a large framework when i can use a `foundation` + `composer` ! .
> Really we don't need a large framework, but we need a simple powerful modular `foundation` framework that helps me building small or large application and with the help of the `composer` ecosystem you can build a large framework .
> We should know that a `framework` is not just a folder that contains some libraries but it is a better designed modular workflow that contains the application logic easily .


# # Quick Demo


```php
<?php

	require('Horus.php');

	(new Horus)->on('/', function(){
		$this->end("Hello World");
	})->on('/page/:?', function($page){
		$this->end(sprintf('current param is %s', $page));
	})->on('POST /api/user', function(){
		$username = $this->body->username;
		$token = $this->query->token;
		// do some database operations
	})->on('/:*', function(){
		$this->end('404, cannot find any matched route !');
	});
```

# # Learn By Examples

```php

	// load the Horus File
	require('Horus.php');

	// create a new instance of Horus with default configs
	$app = new Horus;

	// or you can initialize it and set some options
	$app = new Horus
	([
		// whether your site is using ssl or not ?
		// true/false
		'secure' => true,

		// There are some methods that will helps you
		// generating some urls/routes
		// any route is about <schema>://<host>/<base>/<path>
		// and <base> is what this option affects
		// i.e: you may set it to 'index.php' if your server
		// doesn't support url-rewriting
		'base' => '/',
	
		// Do you want to apply some filters to the whole application
		// output ?, then you will use this options
		// it will has the current output as the first argument
		// and it must return any value to be the new output
		// i.e: function($output){ return trim($output); }
		'output.filter' => null,

		// sometimes you put Horus in a sub/nested-sub directories
		// and you rewrite from a virtual-url to the real path of horus index.php
		// so sometimes the SCRIPT_NAME is invalid so the PATH_INFO becomes invalid too !
		// so we introduce our new magic-config for horus
		// you will set it manually to your needs
		// for example, rewrite from "/myapi/" to "api/index.php"
		// and our main dir that will contains i.e ".htaccess" is in "docroot"
		// so we must strip "docroot/myapi/index.php" from urls
		// NOTE: "the default value for this is ($_SERVER['SCRIPT_NAME'])"
		'path_info.strip' => "docroot/myapi/index.php",
	]);

	// You can access "set/get" options of the configs directly
	// after initialization using this object "$app->configs"
	// i.e
	$app->config->secure = false;
	$app->config->base = '/';
	$app->config->{'output.filter'} = function($output){
			// remove duplicated white-spaces
			return preg_replace('/\s+/', ' ', $output);
	};
	$app->config->myOwnConfig1 = 'value1';

	// send a header ?
	// Note: this will override any previously headers with the same fields
	// Note: fields are case in-sensitive
	// Note: this method return '$this' for chaining purposes
	$app->set('content-type', 'text/html; charset=UTF-8');

	// set multiple headers at once ?
	$app->set([
		'content-type' => 'text/plain',
		'x-powered-by' => 'Horus13, by Mohammed Al Ashaal'
	]);

	// but how about appending header fields
	// i.e: sending a header field multiple times
	// with no overriding ?
	// Note: fields are case in-sensitive
	// Note: this method return '$this' for chaining purposes
	$app->append('field-name', 'value');

	// you can also do it multiple times at once
	// like $app->set([...])
	$app->append([
		// ...
	]);

	// remove header field(s) from header-list ?
	// Note: this method return '$this' for chaining purposes
	$app->remove('field-name');

	// remove multiple fields ?
	$app->remove(['field-name', 'content-type']);
	
	// set the http status code ?
	// this method is an alias for 'http_response_code()'
	// Note: this method return '$this' for chaining purposes
	$app->status(404);

	// write a message to the output buffer ?
	// Note: this method return '$this' for chaining purposes
	$app->send('this is a message');

	// you are writing a RESTful api
	// and want to send a json content
	// with json headers just in one line ?
	// Note: this method return '$this' for chaining purposes
	$app->json([
		'state' => 'ok',
		'message' => 'this is ok message'
	]);

	// Do you want to send a jsonp response and its headers ?
	// i.e 'cb("some data")'
	// Note: this method return '$this' for chaining purposes
	$app->jsonp('some data'); // outputs> cb('some data')

	// Do you want to send a jsonp response with custom callback name ?
	$app->jsonp('some data', 'mycb'); // outputs> mycb('some data')

	// clean the output ?
	// Note: this method return '$this' for chaining purposes
	$app->clear();

	// set cookie ?
	// Note: this method return '$this' for chaining purposes
	$app->cookie('name', 'value');

	// set a cookie with some options ?
	$app->cookie('name', 'value', 
	[
		// cookie domain ?
		'domain'    =>  null,

		// cookie path ? 
		'path'      =>  '/',
	
		// when it will expires ?
		'expires'   =>  0,
	
		// whether it will be sent over ssl or not
		// this is automatically set with the value of
		// $app->config->secure
		// 'secure'    =>  (bool) $this->config->secure,
	
		// whether to send it over httpOnly or not ?
		'httpOnly'  =>  true
	]);

	// render a file, i.e: 'html' and "optionally" set some vars in its scope ?
	// Note: this method return '$this' for chaining purposes
	$app->render('path/to/file.html', ['var_1' => 'value']);

	// render multiple files ?
	// Note: this method return '$this' for chaining purposes
	$app->render(['path/to/file-1.html', 'path/to/file-2.html']);

	// redirect to another url with 302 code "temp redirect" ?
	// Note: this method return '$this' for chaining purposes
	$app->redirect('/new-page');

	// redirect with 301 code "permanent redirect" ?
	$app->redirect('/new-page', true);

	// end the response ?
	// Note: this method return '$this' for chaining purposes
	$app->end();

	// end the response with some message ?
	$app->end('some data');

	// end the response with a message and status code with headers ?!
	$app->end('data', 200, ['content-type' => 'text/html']);

	// register a function that will be triggered when its pattern matches
	// the current path ?
	// Note: this method return '$this' for chaining purposes
	$app->on('/path-1', function(){
		// $this ?!!!
		// yes, any listener 'callback' can access
		// the '$app' scope using '$this'
		$this->end('we are in path-1');
	});

	// register a function that will be triggered when its pattern matches
	// the current path and a certain request method ?
	$app->on('GET /my-path', function(){
		$this->end('we are in GET /my-path');
	});

	// register a function that will be triggered when its pattern matches
	// the current path and a certain request methods ?
	$app->on('GET|POST|PUT /my-path', function(){
		$this->end('we are in GET, POST or PUT /my-path');
	});

	// named route ?
	// /:? --> means '([^\/]+)'
	// /:* --> means '?(.*)'
	$app->on('/page/:?', function($page){
		$this->end('we are in page --> ' . $page);
	})->on('/posts/:?/([0-9]+)', function($category, $num){
		$this->end('first-param: ' . $category . ' and second is: ' . $num);
	});

    // hmmmmmmmmmm, how about rewriting "aliasing" ?!! o.O
    // rewrite from '/api/v1' to '/api/v2'
    // NOTE: any rewrite operation must be before any routing operation
    $app->rewrite('/api/v1', '/api/v2');

    // rewrite with "regex" ?
    // "will rewrite from /api/v1/<anything>" to "/api/v2/<anything>"
    $app->rewrite('/api/v1/?(.+)', '/api/v2/$1'); // !!!!!! ;)

    // vhost ?
    // NOTE: if you want to use vhost, then put it before any basic routes
    // because Horus engine is using the frist matched route .
    // [basic, vhost]
    $app->vhost('([^\.]).locahost.com', function($sub){
        $this->on('/', function(){
            $this->end($sub . '.localhost.com' . ' -> index');
        });
    });

	// Do you want to get a url for a local file ?
	// its schema will be 'http(s)' based on your configurations
	// of '$app->config->secure'
	$jquery = $app->url('/assets/js/jquery.min.js');

    // a change the ->url() host ?
    $jQuery = $app->url('/assets/js/jQuery.js', 'cdn.myownhost.com');

	// Do you want to get a url for  a local route ?
	// its schema will be 'http(s)' based on your configurations
	// of '$app->config->secure' and its base also 
	// '$app->config->base'
	$my_path = $app->route('/my-path');

    // a change the ->route() host ?
    $jQuery = $app->url('/my-path', 'sub.myownhost.com');

	// register a source directory for PSR-4 based packages ?
	// Note: this method return '$this' for chaining purposes
	$app->autoload('/packages/');

	// register multiple directories ?
	$app->autoload(['/packages', '/components']);

	// Do you want to access the _GET ?
	// i.e: '?id=4&user[firstName]=Mohammed&user[lastName]=Al Ashaal'
	$id = $app->query->id;
	$firstName = $app->query->user->firstName;
	$lastName = $app->query->user->lastName;

	// Do you want to access the request body of 'POST/PUT/...'
	// like what we did with '$app->query' ?
	// it automatically parses 'JSON/XML/urlencoded' data as an object
	$app->body->{'keyName'};

	// access cookies like them too ?
	$app->cookies->{'keyName'};
 
    // access "POST/GET" (body/query) vars !!
    $app->request->{'keyName'};
 
    // you can use '$app' itself as an object container
    $app->k1 = 'v1';
    $app->mycb = function(){
        // $this !!!! ;)
        $this->end($this->k1);
    };
    $app->mycb();
```
