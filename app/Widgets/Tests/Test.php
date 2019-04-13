<?php
use Imediasun\Widgets\ApiController as Import;
use Tests\TestCase;

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
        $importer->setSource(app_path('/Widgets/Tests/customers.csv'));
        $result = $importer->process();
        $this->assertTrue($result);
    }
}