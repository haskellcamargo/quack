<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
 * CONTRIBUTORS.
 *
 * This file is part of Quack.
 *
 * Quack is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Quack is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Quack.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace QuackCompiler\Parser;

use \Exception;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;

define('BEGIN_RED', "\033[01;31m");
define('END_RED', "\033[0m");

define('BEGIN_GREEN', "\033[01;32m");
define('END_GREEN', "\033[0m");

define('BEGIN_BG_RED', "\033[01;41m");
define('END_BG_RED', "\033[0m");

define('BEGIN_BOLD', "\033[1m");
define('END_BOLD', "\033[0m");

class SyntaxError extends Exception
{
    private $expected;
    private $found;
    private $parser;

    public function __construct($parameters)
    {
        $this->expected = $parameters['expected'];
        $this->found    = $parameters['found'];
        $this->parser   = $parameters['parser'];
    }

    private function extractPieceOfSource()
    {
        // TODO: do-end example not working
        $out_buffer = [];
        $position = $this->getPosition();
        $token_size = $this->getFoundTokenSize();
        $new_column = $position['column'] - $token_size;
        $error_line = str_split(
            explode(PHP_EOL, $this->getOriginalSource()->input)[
                $position['line'] - 1
            ]
        );

        $line_indicator = "{$position['line']}| ";

        $correct_piece = $new_column - 1 <= 0
            ? []
            : array_slice($error_line, 0, $new_column);

        $error_piece = array_slice($error_line, $new_column, $new_column + 1);

        $out_buffer[] = $line_indicator;
        $out_buffer[] = BEGIN_GREEN . implode($correct_piece) . END_GREEN;
        $out_buffer[] = BEGIN_BG_RED . implode($error_piece) . END_BG_RED;
        $out_buffer[] = PHP_EOL . str_repeat(' ', strlen($line_indicator) + sizeof($correct_piece));
        $out_buffer[] = BEGIN_BOLD . str_repeat('^', sizeof($error_piece)) . END_BOLD;

        return implode($out_buffer);
    }

    public function __toString()
    {
        $source = $this->extractPieceOfSource();
        $expected = $this->getExpectedTokenName();
        $found = $this->getFoundTokenName();
        $position = $this->getPosition();

        return $source . PHP_EOL . implode(PHP_EOL, [
            BEGIN_RED,
            "*** You have a syntactic error in your code!",
            "    Expecting [{$expected}]",
            "    Found     [{$found}]",
            "    Line      {$position['line']}",
            "    Column    " . ($position['column'] - $this->getFoundTokenSize() + 1),
            END_RED
        ]);
    }

    private function getExpectedTokenName()
    {
        return is_integer($this->expected)
            ? Tag::getName($this->expected)
            : $this->expected;
    }

    private function getFoundTokenName()
    {
        $found_tag = $this->found->getTag();

        return 0 === $found_tag
            ? "end of the source"
            : Tag::getName($found_tag) ?: $found_tag;
    }

    private function getFoundTokenSize()
    {
        if ($this->found instanceof \QuackCompiler\Lexer\Word) {
            // Keyword found
            return sizeof($this->found->lexeme);
        } else {
            // Operator, literal or EoF found
            $found_tag = $this->found->getTag();
            $token_val = $this->parser->input->getSymbolTable()->get(
                $this->found->getPointer()
            );

            return 0 === $found_tag
                ? -1
                : strlen(null !== $token_val
                    ? $token_val
                    : $found_tag
                );
        }
    }

    private function getOriginalSource()
    {
        return $this->parser->input;
    }

    private function getPosition()
    {
        return $this->parser->position();
    }
}
