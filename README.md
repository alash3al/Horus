Horus Framework v11
===================

> Horus Framework is a micro PHP framework for creating web applications or APIs .  
> It contains a very flexible `Protoype` class helps you convert any class to a full flexible one .  
> It has no overhead on the performance because it just a wrapper for php's default functions .  
> It gives you a full flexible url `Router` that supports subdomain routing, group routing and named regex .  
> It's API is highly inspired by the popular `nodejs` framework `expressjs` .


## Requirements
* PHP >= `PHP 5.3`
* Any standard web server e.g: `Apache`, `Nginx` ... etc

## Download
* Github `git clone https://github.com/alash3al/Horus.git`
* Composer `"alash3al/horus": "11.0"`

## Quick start
open `index.php` and you will see this
```php
    require "Horus.php";

    use Horus\Horus;

    $app = new Horus;

    $app->all('/index', function(){
        print "Hello World";
    });

    $app->run();
```

Horus assumes that you already enabled your url rewrite module of your webserver,
but if you cannot use any url rewrite, just tell horus to force it

```php
include "Horus";

$_SERVER['HORUS_FORCE_REWRITE'] = true;

// ... and continue 
```

For subdomain routing, you should set your base server domain
$_SERVER['SERVER_NAME'] = 'mysite.com';

### Documentation
**See** (http://alash3al.github.io/Horus)
