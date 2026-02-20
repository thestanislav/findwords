<?php

/*
 * DoctrineExtensions Mysql Function Pack
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace ExprAs\Doctrine\Query\Mysql\Func;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 *
 * @category DoctrineExtensions
 * @package  ExprAs\Doctrine\Query\Mysql\Func
 * @author   Rafael Kassner <kassner@gmail.com>
 * @author   Sarjono Mukti Aji <me@simukti.net>
 * @license  MIT License
 */
class DateFormat extends FunctionNode
{
    public $date = null;
    public $format = null;
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); // (2)
        $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->date = $parser->ArithmeticPrimary(); // (4)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->format = $parser->ArithmeticPrimary(); // (6)
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)
    }
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'DATE_FORMAT(' .
        $this->date->dispatch($sqlWalker) . ', ' .
        $this->format->dispatch($sqlWalker) .
        ')'; // (7)
    }
}
