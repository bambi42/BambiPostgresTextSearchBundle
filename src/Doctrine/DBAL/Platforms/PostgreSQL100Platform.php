<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\Doctrine\DBAL\Platforms;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform as BasePostgreSQL100Platform;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use UnexpectedValueException;

/**
 * Class PostgreSQL100Platform
 * This class is prone to break in the future!
 * @package App\Bambi\PostgresTextSearchBundle\Doctrine\DBAL\Platforms
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class PostgreSQL100Platform extends BasePostgreSQL100Platform
{
    /**
     * @inheritDoc
     * Creates the 'columns' part of a CREATE INDEX statement.
     *
     * @param Index|mixed[] $columnsOrIndex
     * @return string
     */
    public function getIndexFieldDeclarationListSQL($columnsOrIndex): string
    {
        if (!$columnsOrIndex instanceof Index || !$columnsOrIndex->hasFlag('fulltext')) {
            return parent::getIndexFieldDeclarationListSQL($columnsOrIndex);
        }

        $index = $columnsOrIndex;
        if ($index->hasOption('to_tsvector')
            && strtolower($index->getOption('to_tsvector')) !== "false") {
            $config = "'english'";
            if ($index->hasOption('config')) {
                $config = $index->getOption('config');
            }

            return sprintf("to_tsvector(%s, %s)",
                $config,
                implode(" || ' ' || ",
                    array_map(function ($colName) {
                        return sprintf("coalesce(%s, '')", $colName);
                    }, $index->getColumns())));
        } elseif (count($index->getColumns()) == 1) {
            return $index->getColumns()[0];
        } else {
            throw new UnexpectedValueException('If flag "fulltext" is given and flag "to_tsvector" is not given
            then only one column can be indexed"');
        }
    }

    /**
     * @inheritDoc
     * Adds USING (GIN|GIST) to CREATE INDEX statement.
     *
     * @param Index $index
     * @param Table|string $table
     * @return string
     */
    public function getCreateIndexSQL(Index $index, $table)
    {
        if (!$index->hasFlag('fulltext')) {
            return parent::getCreateIndexSQL($index, $table);
        }

        $indexType = 'GIN';
        if ($index->hasOption('index_type')) {
            $indexType = strtoupper($index->getOption('index_type'));
        }

        if ($indexType !== 'GIN' && $indexType !== 'GIST') {
            throw new UnexpectedValueException('Option "index_type" must be either "gin" or "gist".');
        }

        if ($table instanceof Table) {
            $table = $table->getQuotedName($this);
        }

        $table = sprintf("%s USING %s", $table, $indexType);

        return parent::getCreateIndexSQL($index, $table);
    }

    /**
     * @inheritDoc
     * Returns the SQL needed for retrieving the indexes present in the DB.
     * CAUTION the SQL generated by this method is very fragile and prone to bugs and unexpected behavior
     * in newer versions of either this bundle, Doctrine DBAL or Postgresql. It is required though
     * to make automatic index management via Doctrine work.
     *
     * @param string $table
     * @param null $database
     * @return string|string[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getListTableIndexesSQL($table, $database = null)
    {
        $originalSql = parent::getListTableIndexesSQL($table, $database);
        $additionalSQL =
            "CASE pg_index.indkey[0]
		        WHEN 0 THEN
				    array_to_string(ARRAY(
				        SELECT attnum
					        FROM pg_attribute JOIN (
					            SELECT left(substring((regexp_matches(pg_get_expr(pg_index.indexprs, pg_index.indrelid),
						            'COALESCE\([a-zA-Z0-9_-]+,', 'g'))[1] from 10), -1) AS name) AS col_names
							ON pg_attribute.attname = col_names.name
							WHERE attrelid = pg_index.indrelid), ' ')
				ELSE array_to_string(pg_index.indkey, ' ')
			END AS indkey";

        return str_replace('pg_index.indkey', $additionalSQL, $originalSql);
    }
}