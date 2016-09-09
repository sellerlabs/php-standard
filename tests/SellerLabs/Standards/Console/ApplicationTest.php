<?php
use SellerLabs\Nucleus\Testing\TestCase;
use SellerLabs\Standards\Console\Application;

/**
 * Class ApplicationTest
 *
 * @author Mark Vaughn <mark@roundsphere.com>
 */
class ApplicationTest extends TestCase
{
    public function testConstruct()
    {
        $application = new Application();
    }
}
