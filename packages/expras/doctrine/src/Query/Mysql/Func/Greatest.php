<?php

namespace ExprAs\Doctrine\Query\Mysql\Func;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Greatest extends FunctionNode
{
    /**
     * @var array
     */
    protected $greatestArgs;

    /**
     * @param SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            'GREATEST(%s)',
            implode(
                ', ', array_map(
                    fn ($expression) => $expression->dispatch($sqlWalker),
                    $this->greatestArgs
                )
            )
        );
    }

    /**
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        // 2 arguments minimum
        $this->greatestArgs[] = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->greatestArgs[] = $parser->ArithmeticPrimary();

        while ($parser->getLexer()->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->greatestArgs[] = $parser->ArithmeticPrimary();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
