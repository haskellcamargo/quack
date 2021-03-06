<?php
namespace QuackCompiler\Tests;

define('BASE_PATH', __DIR__ . '/../src');
require_once './src/toolkit/QuackToolkit.php';

use \QuackCompiler\Lexer\Tokenizer;

define('SHOW_SYMBOL_TABLE', true);

class LexerTest extends \PHPUnit_Framework_TestCase
{
    private function tokenize($source, $show_symbol_table = false)
    {
        return implode(array_map(function ($token) use ($show_symbol_table) {
            return (string) $token;
        }, (new Tokenizer($source))->eagerlyEvaluate($show_symbol_table)));
    }

    public function testIdent()
    {
        $this->assertEquals("[T_IDENT, 0]", $this->tokenize('quack'));
        $this->assertEquals("[T_IDENT, quack]", $this->tokenize('quack', SHOW_SYMBOL_TABLE));
        $this->assertEquals("[T_IDENT, 0][T_IDENT, 1]", $this->tokenize('hello world'));
        $this->assertEquals("[T_IDENT, hello][T_IDENT, world]", $this->tokenize('hello world', SHOW_SYMBOL_TABLE));
    }

    public function testNumber()
    {
        $decimal_integer = "1083";
        $octal_integer = "0314";
        $octal_partial_integer = "0314891";
        $hexa_integer = "0xFFAB01";
        $decimal_double = "124.1323";
        $decimal_non_octal_double = "0314.0";
        $binary_integer = "0b11111111";
        $binary_invalid = "0b1019";

        $this->assertEquals("[T_INTEGER, 1083]", $this->tokenize($decimal_integer, SHOW_SYMBOL_TABLE));
        $this->assertEquals("[T_INTEGER, 0314]", $this->tokenize($octal_integer, SHOW_SYMBOL_TABLE));
        $this->assertEquals("[T_INTEGER, 0314]", $this->tokenize($octal_partial_integer, SHOW_SYMBOL_TABLE));
        $this->assertEquals("[T_INTEGER, 0xFFAB01]", $this->tokenize($hexa_integer, SHOW_SYMBOL_TABLE));
        $this->assertEquals("[T_DOUBLE, 124.1323]", $this->tokenize($decimal_double, SHOW_SYMBOL_TABLE));
        $this->assertEquals("[T_DOUBLE, 0314.0]", $this->tokenize($decimal_non_octal_double, SHOW_SYMBOL_TABLE));
        $this->assertEquals("[T_INTEGER, 0b11111111]", $this->tokenize($binary_integer, SHOW_SYMBOL_TABLE));
        $this->assertEquals("[T_INTEGER, 0b101][T_INTEGER, 9]", $this->tokenize($binary_invalid, SHOW_SYMBOL_TABLE));
    }

    public function testString()
    {
        $string = "'lorem ipsum dolor'";
        $string_with_quote = "'lorem ipsum \' dolor'";
        $string_with_double_quote = "'lorem ipsum \" dolor";
        $complex_string = '"complex \" string"';
        $simple_string = '"simple \' string"';

        $this->assertEquals(
            '[T_STRING, lorem ipsum dolor]',
            $this->tokenize($string, SHOW_SYMBOL_TABLE)
        );
        $this->assertEquals(
            '[T_STRING, lorem ipsum \\\' dolor]',
            $this->tokenize($string_with_quote, SHOW_SYMBOL_TABLE)
        );
        $this->assertEquals(
            '[T_STRING, lorem ipsum " dolor]',
            $this->tokenize($string_with_double_quote, SHOW_SYMBOL_TABLE)
        );
        $this->assertEquals(
            '[T_STRING, complex \\" string]',
            $this->tokenize($complex_string, SHOW_SYMBOL_TABLE)
        );
        $this->assertEquals(
            '[T_STRING, simple \' string]',
            $this->tokenize($simple_string, SHOW_SYMBOL_TABLE)
        );
    }

    public function testKeywords()
    {
        $keywords = [
            "true", "false", "let", "if", "for", "while", "do", "impl", "class", "shape",
            "module", "foreach", "in", "where",
            "const", "nil", "open", "global", "as", "enum", "continue", "switch",
            "break", "and", "or", "xor", "try", "rescue", "finally", "raise", "elif",
            "else", "case", "not"
        ];

        $this->assertEquals(
            "[true][false][let][if][for][while][do][impl][class][shape]" .
            "[module][foreach][in][where][const][nil]" .
            "[open][global][as][enum][continue][switch][break][and][or][xor][try]" .
            "[rescue][finally][raise][elif][else][case][not]",
            $this->tokenize(implode(' ', $keywords))
        );
    }

    public function testOperators()
    {
        $this->assertEquals(
            "[-][T_INTEGER, 0][*][T_INTEGER, 1][and]" .
            "[T_INTEGER, 2][or][T_INTEGER, 3][+][T_INTEGER, 4][;][@][T_IDENT, 5]",
            $this->tokenize("-1 * 3 and 2 or 4 + 8; @name")
        );
    }
}
