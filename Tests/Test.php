<?php
use Imediasun\Widgets\ApiController as Import;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {


        $importer = new Import();
        $importer->setSource('customers.csv');
        $result = $importer->process();
        $this->assertTrue($result);
    }
}