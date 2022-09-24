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
 * Class ToTsquery
 * "to_tsquery" "(" StringPrimary "," StringPrimary ")"
 *
 * @package App\Bambi\Query
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class ToTsquery extends FunctionNode
{
    public Node $languageExpression;
    public Node $queryExpression;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'to_tsquery(' .
            $this->languageExpression->dispatch($sqlWalker) . ', ' .
            $this->queryExpression->dispatch($sqlWalker) . ')';
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
        $this->languageExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->queryExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}