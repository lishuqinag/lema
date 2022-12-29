<?php

namespace Test\App;

use App\Service\AppLogger;
use PHPUnit\Framework\TestCase;


class DemoTest extends TestCase
{

    public function test_foo()
    {
        $logger  = new AppLogger('think-log');
        $logger->info("hello world!");
    }

    public function test_get_user_info()
    {
        echo 'Hello word';exit;
    }
}
