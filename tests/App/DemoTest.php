<?php

namespace Test\App;

use App\Service\AppLogger;
use PHPUnit\Framework\TestCase;
use App\Util\HttpRequest;
use App\App\Demo;


class DemoTest extends TestCase
{

    public function test_foo()
    {
        $demo = new Demo('test',new HttpRequest());
        $foo = $demo->foo();
        var_dump($foo);

//        $logger  = new AppLogger('think-log');
//        $logger->info("hello world!");
    }

    public function test_get_user_info()
    {
        $demo = new Demo('test',new HttpRequest());
        $getUser = $demo->get_user_info();
        var_dump($getUser);exit;
    }
}
