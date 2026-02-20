<?php

namespace ExprAs\Doctrine\Query\Mysql\Func;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser as QueryParser;

/**
 * Custom DQL String function to allow REGEXP functionality in MySQL Queries
 *
 * @author     Matt Cockayne <matt@zucchi.co.uk>
 * @package    ExprAs\Doctrine
 * @subpackage Query
 */
class Regexp extends FunctionNode
{
    public $field;

    public $regex;

    public function getSql(SqlWalker $sqlWalker)
    {
        return $this->field->dispatch($sqlWalker) . ' REGEXP ' . $this->regex->dispatch($sqlWalker);
    }

    public function parse(QueryParser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); // (2)
        $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->field = $parser->StringPrimary(); // (4)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->regex = $parser->StringPrimary(); // (6)
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)
    }
}
