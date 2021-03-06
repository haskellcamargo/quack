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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;

class RangeExpr extends Expr
{
    public $from;
    public $to;
    public $by;

    public function __construct($from, $to, $by)
    {
        $this->from = $from;
        $this->to = $to;
        $this->by = $by;
    }

    public function format(Parser $parser)
    {
        $source = $this->from->format($parser);
        $source .= ' .. ';
        $source .= $this->to->format($parser);

        if (null !== $this->by) {
            $source .= ' by ';
            $source .= $this->by->format($parser);
        }

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        $this->from->injectScope($parent_scope);
        $this->to->injectScope($parent_scope);

        if (null !== $this->by) {
            $this->by->injectScope($parent_scope);
        }
    }

    public function getType()
    {
        $newtype = new Type(NativeQuackType::T_LIST);

        $type = (object)[
            'from' => $this->from->getType(),
            'to'   => $this->to->getType(),
            'by'   => null !== $this->by ? $this->by->getType() : null
        ];

        $throw_error_on = function ($operand, $got) {
            throw new ScopeError([
                'message' => "Expected number on operand `{$operand}' of range expression. Got {$got}"
            ]);
        };

        if (!$type->from->isNumber()) {
            $throw_error_on('from', $type->from);
        }

        if (!$type->to->isNumber()) {
            $throw_error_on('to', $type->to);
        }

        if (null !== $type->by && !$type->by->isNumber()) {
            $throw_error_on('by', $type->by);
        }

        $newtype->subtype = Type::getBaseType(array_filter(array_values((array) $type), function ($t) {
            return null !== $t;
        }));

        return $newtype;
    }
}
