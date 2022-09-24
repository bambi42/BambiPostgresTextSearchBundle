<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\Doctrine\Query;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class TsMatches
 * "ts_matches" "(" StringPrimary "," StringPrimary ")" => "(" StringPrimary "@@" StringPrimary ")"
 *
 * @package App\Bambi\Query
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class TsMatches extends FunctionNode
{
    public Node $tsExpression1;
    public Node $tsExpression2;
    public bool $expression2Parenthesis;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        if ($this->expression2Parenthesis) {
            return '(' .
                $this->tsExpression1->dispatch($sqlWalker) . ' @@ ' . '(' .
                $this->tsExpression2->dispatch($sqlWalker) . '))';
        } else {
            return '(' .
                $this->tsExpression1->dispatch($sqlWalker) . ' @@ ' .
                $this->tsExpression2->dispatch($sqlWalker) . ')';
        }
    }

    /**
     * @param Parser $parser
     * @return void
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->tsExpression1 = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        if ($parser->getLexer()->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            $this->expression2Parenthesis = true;
            $parser->match(Lexer::T_OPEN_PARENTHESIS);
            $this->tsExpression2 = $parser->StringPrimary();
            $parser->match(Lexer::T_CLOSE_PARENTHESIS);
        } else {
            $this->expression2Parenthesis = false;
            $this->tsExpression2 = $parser->StringPrimary();
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}