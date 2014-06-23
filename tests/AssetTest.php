<?php

error_reporting(E_ALL);

include 'vendor/autoload.php';
include 'tests/resources/BaseTest.php';

class AssetTest extends BaseTest {

    protected $folder;

    //--------------------------------------------------------------------

    public function __construct ()
    {
        $this->folders = [ dirname(__FILE__) .'/resources/' ];

        $this->setVerboseErrorHandler();
    }

    //--------------------------------------------------------------------

    public function testCanLoadClass ()
    {
        var_dump($aa);
        $asset = new \Bonfire\Assets\Asset('jquery.js');
        $this->assertTrue(get_class($asset) == 'Bonfire\Assets\Asset');
    }

    //--------------------------------------------------------------------
    
}