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
        $importer->setDestination('customers');
        $importer->setSource(app_path('/Widgets/Tests/customers.csv'));
        $importer->configureFields([
            'Name'=>['field'=>'name','validators'=>'required|max:255'],
            'Email'=>['field'=>'email','validators'=>'required|email'],
            'Telefon'=>['field'=>'telefon','validators'=>'max:10']

        ]);

        $result = $importer->process();
        $this->assertTrue($result);
    }
}