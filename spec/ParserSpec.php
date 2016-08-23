<?php

namespace spec\Sassnowski\CsvSchema;

use PhpSpec\ObjectBehavior;
use Sassnowski\CsvSchema\Exceptions\UnsupportedTypeException;

class ParserSpec extends ObjectBehavior
{
    public function it_returns_the_correct_amount_of_rows()
    {
        $this->beConstructedWith([
            'schema' => [
                'a' => 'string',
                'b' => 'string',
                'c' => 'string',
            ],
        ]);

        $input = "foo,bar,baz\nbar,quz,blub\nfoo,bar,test";
        $this->fromString($input)->shouldHaveCount(3);

        $input = "foo,bar,baz\nbar,quz,blub";
        $this->fromString($input)->shouldHaveCount(2);
    }

    public function it_can_parse_from_a_file()
    {
        $this->beConstructedWith([
            'schema' => [
                'a' => 'string',
                'b' => 'string',
            ],
        ]);

        $this->fromFile(__DIR__.'/data/input.csv')->shouldHaveCount(5);
    }

    public function it_parses_string_columns_correctly()
    {
        $this->beConstructedWith([
            'schema' => [
                'foo' => 'string',
                'bar' => 'string',
            ],
        ]);

        $input = ['baz', 'qux'];

        $this->parseRow($input)->shouldEqual([
            'foo' => 'baz',
            'bar' => 'qux',
        ]);
    }

    public function it_parses_int_columns_correctly()
    {
        $this->beConstructedWith([
            'schema' => [
                'foo' => 'int',
                'bar' => 'int',
            ],
        ]);

        $input = ['15', '25'];

        $this->parseRow($input)->shouldEqual([
            'foo' => 15,
            'bar' => 25,
        ]);
    }

    public function it_parses_array_columns_correctly()
    {
        $this->beConstructedWith([
            'schema' => [
                'foo' => 'array:,',
            ],
        ]);

        $input = ['hello,world,how,are,you'];

        $this->parseRow($input)->shouldEqual([
            'foo' => explode(',', $input[0]),
        ]);
    }

    public function it_parses_float_columns_correctly()
    {
        $this->beConstructedWith([
            'schema' => [
                'foo' => 'float',
                'bar' => 'float',
            ],
        ]);

        $input = ['12', '18.5'];

        $this->parseRow($input)->shouldEqual([
            'foo' => 12.0,
            'bar' => 18.5,
        ]);
    }

    public function it_parses_a_complex_schema()
    {
        $this->beConstructedWith([
            'schema' => [
                'author' => 'string',
                'age' => 'int',
                'average_rating' => 'float',
                'children' => 'array:,',
            ],
        ]);

        $input = ['Sir Fooington', '58', '9.8', 'John,Jane,The other one'];

        $this->parseRow($input)->shouldEqual([
            'author' => 'Sir Fooington',
            'age' => 58,
            'average_rating' => 9.8,
            'children' => ['John', 'Jane', 'The other one'],
        ]);
    }

    public function it_throws_an_exception_if_the_provided_type_is_not_supported()
    {
        $this->beConstructedWith([
            'schema' => [
                'a' => 'foo',
            ],
        ]);

        $this->shouldThrow(UnsupportedTypeException::class)->during('parseRow', [['foo']]);
    }
}
