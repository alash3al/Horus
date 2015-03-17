    require "Horus.php";

    use Horus\Horus;

    $app = new Horus;

    $app->all('/index', function(){
        print "Hello World";
    });

    $app->run();
