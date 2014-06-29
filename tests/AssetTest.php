<?php

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
        $asset = new \Bonfire\Assets\Asset('jquery.js');
        $this->assertTrue(get_class($asset) == 'Bonfire\Assets\Asset');
    }

    //--------------------------------------------------------------------

    public function testGetContentSetContentLoop ()
    {
        $asset = new \Bonfire\Assets\Asset('jquery.js');
        $content = 'This is my content.';

        $asset->setContent($content);
        $this->assertTrue($content == $asset->getContent());
    }

    //--------------------------------------------------------------------

    public function testGetContentDoesNotThrowExceptionWithBadFile ()
    {
        $asset = new \Bonfire\Assets\Asset('nonexistingfile');
        $this->assertNull( $asset->getContent() );
    }

    //--------------------------------------------------------------------

    public function testGetTypeRecognizesJavascript ()
    {
        $asset = new \Bonfire\Assets\Asset('jquery.js', ['./tests/resources/']);
        $this->assertEquals($asset->getType(), 'javascript');
    }

    //--------------------------------------------------------------------

    public function testGetTypeRecognizesCSS ()
    {
        $asset = new \Bonfire\Assets\Asset('test.css', ['./tests/resources/']);
        $this->assertEquals($asset->getType(), 'stylesheet');
    }

    //--------------------------------------------------------------------

    public function testGetTypeRecognizesDoubleExtensions ()
    {
        $asset = new \Bonfire\Assets\Asset('jquery.min.js', ['./tests/resources/']);
        $this->assertEquals($asset->getType(), 'javascript');
    }

    //--------------------------------------------------------------------

    public function testGetExtensionGetsProperDoubleExtensions ()
    {
        $asset = new \Bonfire\Assets\Asset('jquery.min.js', ['./tests/resources/'], ['.min.js' => [] ]);
        $this->assertEquals($asset->getExtension('jquery.min.js'), '.min.js');
    }

    //--------------------------------------------------------------------

    public function testGetContentReturnsNullOnBadFile ()
    {
        $asset = new \Bonfire\Assets\Asset('missingfile.js', ['./tests/resources/']);
        $this->assertNull($asset->getContent());
    }

    //--------------------------------------------------------------------

    public function testGetContentReturnsValidJSContent ()
    {
        $jquery = file_get_contents('tests/resources/jquery.js');

        $asset = new \Bonfire\Assets\Asset('jquery.js', ['./tests/resources/']);
        $this->assertEquals($asset->getContent(), $jquery);
    }

    //--------------------------------------------------------------------

    public function testGetContentReturnsValidCSSContent ()
    {
        $file = file_get_contents('tests/resources/test.css');

        $asset = new \Bonfire\Assets\Asset('test.css', ['./tests/resources/']);
        $this->assertEquals($asset->getContent(), $file);
    }

    //--------------------------------------------------------------------
}