### #Horus 12
An imporved layer for any php application

### #Overview
In php world, there are many and many frameworks, but there are no simple and standalone
layers that is just a wrapper for any web application, and this is Horus .  
Horus is a very simple layer/foundation for any "any" web application, it will help you in 
rapid apps developments .  

```php
<?php

    require("Horus.php");

    (new \Horus\App)->on('/', function($request, $response){
        $response->send("hello world");
        $response->end();
    })->on('GET|POST /test', function($request, $response){
        $response->end("What is this ?");
    });

?>
```

### #Contents

- **Download**
- **Prorotype**     
- **App**    
- **Request**  
- **Response** 

### #Download

> `composer require alash3al/horus`  

> `git clone https://github.com/alash3al/Horus.git`  

> `https://github.com/alash3al/Horus/archive/master.zip`  


### #Prototype
> just an improved `stdClass` that supports closure  

```php
    require "Horus.php";

    $proto = new \Horus\Prototype;

    $proto->key = "value";
    $proto->func = function() use($proto) {
        print $proto->key;
        return $proto;
    };
```

### #App
> Our main class that handles request and response ..  

```php

    require "Horus.php";

    // you can optionally create layers "middlewares"
    // layers are just array of callbacks
    // executed each request .
    // e.g: you can create a layer that add
    // some properties to the 'req' object.
    $layers = [
        function($req, $res, $app){},
        function($req, $res, $app){},
        // and so ...
    ];

    $app = new \Horus\App($layers);

    // you register a callback for a uri
    // when the user access it, it will be distpatched
    $app->on('/index', function($req, $res){
        $res->send("index");
        // we don't need any other route
        // so we will end the app
        $res->end();
    });

    // for a certain method ?
    $app->on('POST /test', function($req, $res){
        $res->end("post request on '/test'");
    });

    // multiple methods?
    $app->on('POST|GET /t', function($req, $res){
        $res->end('will work for "POST /t" and "GET /t"');
    });

    // unknown/dynamic params ?
    $app->on('/user/?/settings', function($req, $res, $id){
        // yes, the dynamic args, will be passed with the "$req, $res"
        $res->end("you want the settings of the user {$id}");
    });

    // more ?
    $app->on('/user/?/set/?', function($req, $res, $id, $section){
        $res->end("you want the settings of {$section} for the user {$id}");
    });

    // wildcards ?
    $app->on('/test/*', function($req, $res, $path){
        $res->end("you are in 'test/{$path}'");
    });

    // group of routes ?
    $app->group("/group", function($req, $res, $app){
        // group/page-1
        $app->on('/page-1', function(){});
        // you can create nested groups too .
    });

    // vhosts ?
    $app->vhost("user-*.my.com", function($req, $res, $app, $uid){
        // you can apply routes here too
    });

	// lets put this at the end of our routes
	// so if "PHP" didn't find the right route
	// it will display '404'
	$app->on('/*', function($req, $res){
		$res->end("404 not found");
	});
    
``` 

### #Request

> Manage the input easily  

```php
    // instead of _GET
    // a wrapper
    // ?k=v
    $req->query->k; //> "v";
    // ?k[k1][k2]=v2
    $req->k->k1->k2; //> "v2"

    // for _POST, PUT, DELETE, or any request body
    // POST -> x=v&b=c
    $req->body->x; //> v;

    // $_COOKIE ?
    // $_COOKIE['key']
    $req->cookies->key;

    // get the current request method
    $req->method;

    // the hostname of the host headers
    // for example.com:8080
    // hostname is example.com
    $req->hostname;

    // the request path "/x/y/z/"
    // it also the same as "PATH_INFO"
    $req->path; //> x/y/z

    // headers ?
    $req->headers->host; //> returns the host headers
    $req->headers->user_agent //> returns the user agent value
    // and so ...
```

### #Response

> Useful methods for handling the output 

```php
    // Response object

    // set a http header [will replace any header with the same field]
    // this is case in-sensitive
    // it return $this, usefull for chaining
    $res->set('content-type', 'text/html');
    // multiple ?
    $res->set([
        'x-header-1'    =>  'value',
        'x-header-2'    =>  'value2'
    ]);

    // don't want to replace ?
    // just use append
    // also returns $this
    $res->append('x-header3', 'value3');
    $res->append([
        /*
            same as set
            array of key value paris
        */
    ]);

    // set the status code
    $res->status(404)->set('x-code', '404 not found');

    // write to the client
    $res->send('just a message');
    // or json ?
    // json will also send the content-type: application/json
    $res->send([
        'x' => 'y'
    ]);

    // json too ..
    // + content-type: application/json
    $res->json([
        // ...
    ]);

    // jsonp
    // + content-type: application/json
    $res->jsonp([
        // ..
    ]); //> default jsonp callback is 'callback'

    // change it ?
    $res->jsonp([], 'myfunction');

    // setcookie
    $res->cookie('name', 'value', [/* options here */]);
    /*
        cookie options
        'domain'    =>  empty by default
        'path'      =>  directory of the main index file
        'expires'   =>  zero by default
        'secure'    =>  horus detect it
        'httpOnly'  =>  true by default
    */

    // cache web page for a $ttl
    // cache for 10 seconds
    // using last-modified
    $res->cache(10);

    // cache using expires after 10 seconds from now
    $res->expires(time() + 10);

    // render file ?
    $res->render('filename.html');
    // you can pass vars
    $res->render('filename.html', ['var' => 'c']);
    // you can use multiple files
    $res->render([
        'file1.html',
        'file2.html'
    ], [
        'var1'  =>  'val1'
    ]);

    // redirect ? "temporarily"
    $res->redirect('/target.php');

    // redirect ? "permanent"
    $res->redirect("/to.php", true);

    // get a direct url for a local file ?
    $app->urlFor("/assets/style.css");
    // by default it starts with "http",
    // but you want to get a secure url "starts with https" ?
    $app->urlFor("/assets/style.css", true);

    // get a url for an internal route ?
    // for "/user/1/settings"
    $app->routeFor("user/1/settings");
    // it will return a clean and simple url
    // but if your server does not support rewriting
    $app->routeFor("user/1/settings", false);
    // will use "index.php/" as a proxy
    // do you want a secure clean url ?
    $app->routeFor("user/1/settings", true, true);

    // clear the output
    $res->clear();

    // now the time to end the response
    $res->end();
    // or end with some text
    $res->end("By");
    // or end with some text and status code
    $res->end("404 not found", 404);
```
