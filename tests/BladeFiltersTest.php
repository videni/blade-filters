<?php

namespace Pine\BladeFilters\Tests;

use Pine\BladeFilters\BladeFilters;
use Pine\BladeFilters\Exceptions\MissingBladeFilterException;
use Illuminate\Support\Str;

class BladeFiltersTest extends TestCase
{
    /** @test */
    public function a_string_can_be_filtered()
    {
        $this->get('/blade-filters/string')->assertSee(Str::slug('string test'));
    }

    /** @test */
    public function a_variable_can_be_filtered()
    {
        $this->get('/blade-filters/variable')->assertSee(Str::slug('variable test'));
    }

    /** @test */
    public function a_function_can_be_filtered()
    {
        $this->get('/blade-filters/function')->assertSee(Str::slug('function test'));
    }

    /** @test */
    public function a_risky_string_can_be_filtered()
    {
        $this->get('/blade-filters/risky-string')->assertSee(
            Str::start(Str::finish('risky|string:test', '|'), ':')
        );
    }

    /** @test */
    public function a_bitwise_operator_string_can_be_filtered()
    {
        $result = Str::upper('a' | 'b');

        $this->get('/blade-filters/bitwise')->assertSee($result);
    }

    /** @test */
    public function a_string_can_be_chain_filtered()
    {
        $text = '   long and Badly Formatted text....way too long';

        $this->get('/blade-filters/chain')->assertSee(
            Str::limit(Str::title(BladeFilters::trim($text)), 10)
        );
    }

    /** @test */
    public function a_string_can_be_wrapped_and_multiline()
    {
        $this->get('/blade-filters/wrapped')
            ->assertSee(
                '<h1>' . Str::title('this is a title') . '</h1>', false
            )->assertSee(
                '<a href="' . Str::slug('this is a link') . '">Link</a>', false
            );
    }

    /** @test */
    public function it_throws_exception_when_missing_filter()
    {
        $result = $this->get('/blade-filters/missing-filter');

        $this->assertInstanceOf(MissingBladeFilterException::class, $result->exception);

        $this->assertEquals($result->exception->getMessage(), 'Blade filter this_filter_does_not_exist not exists');
    }

    /** @test */
    public function at_curly_brace_js_syntax_ignored()
    {
        $this->get('/blade-filters/ignore-js')
            ->assertSee('<h1>{{ val.title | title }}</h1>', false);
    }

    /** @test */
    public function a_filter_can_use_a_variable_as_argument()
    {
        $view = view("blade-filters::variable-as-argument", ['separator' => '_']);

        $this->assertStringContainsString('variable_as_filter_argument_test', $view->render());
    }

    /** @test */
    public function a_filter_can_ignore_unknown_filter_argument()
    {
        $this->get('/blade-filters/unknown-argument')
        ->assertSee(Str::slug('string test'));
    }
}
