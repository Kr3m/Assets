<?php

include_once 'vendor/autoload.php';
include_once 'tests/resources/BaseTest.php';

class DirectivesTest extends BaseTest {

    public function __construct ()
    {
        $this->folders = [ dirname(__FILE__) .'/resources/' ];

        $this->setVerboseErrorHandler();
    }

    public function setUp ()
    {
        $this->parser = new \Bonfire\Assets\Directives\DirectivesParser();
    }

    //--------------------------------------------------------------------

    public function testScriptIsLoaded ()
    {
        $this->assertTrue(get_class($this->parser) == 'Bonfire\Assets\Directives\DirectivesParser');
    }

    //--------------------------------------------------------------------

    public function testProcessReturnsEmptyArrayOnNoData ()
    {
        $lines = [];

        $result = $this->parser->parse($lines, 'script_requires.js');

        $this->assertEquals($lines, $result);
    }

    //--------------------------------------------------------------------

    public function testStructureDirectivesFormatsCorrectly ()
    {
        $lines = [
            'include' => [
                'one' => 1,
                'two' => 2,
                'three' => 3
            ],
            'exclude' => [
                'four' => 1,
                'five' => 2,
                'six' => 3
            ],
        ];

        list($includes, $excludes) = $this->parser->structureDirectiveResults($lines);

        $this->assertEquals($includes, $lines['include']);
        $this->assertEquals($excludes, $lines['exclude']);
    }

    //--------------------------------------------------------------------

    public function testProcessDirectiveFromLineReturnsEmptiesWithNoDirective ()
    {
        $line = '//= ';

        $result = $this->parser->processDirectiveFromLine($line, 'script_requires.js');

        $this->assertEquals($result, [ [], [] ]);
    }

    //--------------------------------------------------------------------

    public function testProcessDirectiveFromLineParsesDirectives ()
    {
        $line = '//= require script_requires.js';

        $result = $this->parser->processDirectiveFromLine($line, 'script_requires.js');

//        $this->assertEquals($result, [ [], [] ]);
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Tags
    //--------------------------------------------------------------------

    public function testTranslateTagsLeavesNonTagsAlone ()
    {
        $filename = "we_dont_have_no_tags_here.php";

        $result = $this->parser->translateTags($filename);

        $this->assertEquals($filename, $result);
    }

    //--------------------------------------------------------------------

    public function testTranslateTagsRunsCustomTags ()
    {
        $this->parser->registerTag('themes', function($str) {
            if (preg_match('/{theme:[a-zA-Z]+}/', $str, $matches) !== false)
            {
                if (isset($matches[0]))
                {
                    $theme_path = 'themes/';
                    $theme = trim( str_replace('theme:', '', $matches[0]), '{} ');

                    return $theme_path . $theme .'/'. str_replace($matches[0] .'/', '', $str);
                }
            }

            return $str;
        });

        $filename = "{theme:admin}/a_file.php";
        $final = "themes/admin/a_file.php";

        $result = $this->parser->translateTags($filename);

        $this->assertEquals($final, $result);
    }

    //--------------------------------------------------------------------

}