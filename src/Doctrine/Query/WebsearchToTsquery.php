<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\Doctrine\Query;

use Doctrine\ORM\Query\SqlWalker;

/**
 * Class WebsearchToTsquery
 * "websearch_to_tsquery" "(" StringPrimary "," StringPrimary ")"
 *
 * @package App\Bambi\Query
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class WebsearchToTsquery extends ToTsquery
{
    /**
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'websearch_' . parent::getSql($sqlWalker);
    }
}