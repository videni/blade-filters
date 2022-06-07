<?php

namespace Pine\BladeFilters\Tests;

use Pine\BladeFilters\BladeFiltersTest;
use PHPUnit\Framework\TestCase;
use Pine\BladeFilters\BladeFilterParser;
use Pine\BladeFilters\Exceptions\SyntaxException;

class BladeFilterParserTest extends TestCase
{
    /** @test */
    public function test_filter_with_named_arguments()
    {
        $input = '"css/carousel.css" | theme_asset_url | stylesheet_tag:media="screen and (max-width: 600px)",preload=$preload->test->a';

        $parser = new BladeFilterParser();

        $filter = $parser->parse($input);

        $this->assertEquals($filter['prefiltered'], '"css/carousel.css"');
        $this->assertTrue(count($filter['filters']) == 2);
    }

     /** @test */
     public function test_filter_with_no_arguments()
     {
         $input = '"css/carousel.css" | theme_asset_url';

         $parser = new BladeFilterParser();

         $filter = $parser->parse($input);

         $this->assertEquals($filter['prefiltered'], '"css/carousel.css"');
         $this->assertTrue(count($filter['filters']) == 1);
     }

     /** @test */
     public function test_filter_with_missing_arguments()
     {
        $this->expectException(SyntaxException::class);

         $input = '"css/carousel.css" | theme_asset_url:';

         $parser = new BladeFilterParser();

         $filter = $parser->parse($input);

         $this->assertEquals($filter['prefiltered'], '"css/carousel.css"');
         $this->assertTrue(count($filter['filters']) == 1);
     }
}
