<?php
/**
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

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;

class BitXor extends FunctionNode
{
    /**
     * @var Node 
     */
    public $firstArithmetic;

    /**
     * @var Node 
     */
    public $secondArithmetic;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->firstArithmetic = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondArithmetic = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'BIT_XOR (' .
            $this->firstArithmetic->dispatch($sqlWalker) . ', ' .
            $this->secondArithmetic->dispatch($sqlWalker) .
        ')';
    }
}
