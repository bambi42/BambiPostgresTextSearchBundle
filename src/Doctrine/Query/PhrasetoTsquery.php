<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\Doctrine\Query;

use Doctrine\ORM\Query\SqlWalker;

/**
 * Class PhrasetoTsquery
 * "phraseto_tsquery" "(" StringPrimary "," StringPrimary ")"
 *
 * @package App\Bambi\Query
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class PhrasetoTsquery extends ToTsquery
{
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'phrase' . parent::getSql($sqlWalker);
    }
}