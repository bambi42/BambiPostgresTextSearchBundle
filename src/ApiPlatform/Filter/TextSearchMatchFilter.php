<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\ApiPlatform\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Core\Exception\InvalidArgumentException;

/**
 * Class FullTextSearchMatchFilter
 * @package App\Bambi\PostgresTextSearchBundle\ApiPlatform\Filter
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class TextSearchMatchFilter extends AbstractTextSearchFilter
{
    private function processProperty(string $property, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): ?string
    {
        if (!$this->isPropertyMapped($property, $resourceClass, true)) {
            return null;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $field = $property;

        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field] = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator, $resourceClass, Join::LEFT_JOIN);
        }

        return sprintf('COALESCE(%s.%s, \'\')', $alias, $field);
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = [])
    {
        if (!isset($context['filters'][$this->textSearchParameterName])) {
            return;
        }

        if (!is_string($context['filters'][$this->textSearchParameterName])) {
            $this->logger->notice('Ignoring invalid filter', [
                'exception' => new InvalidArgumentException(sprintf('Filter %s expects a value of type string, %s given', $this->textSearchParameterName, gettype($context['filters'][$this->textSearchParameterName])))
            ]);
            return;
        }

        $query = trim($context['filters'][$this->textSearchParameterName]);
        if (!$query) {
            return;
        }

        $processedProperties = [];
        foreach ($this->getProperties() as $property) {
            if (($processedProperty = $this->processProperty($property, $queryBuilder, $queryNameGenerator, $resourceClass)) !== null) {
                $processedProperties[] = $processedProperty;
            }
        }

        if (!$processedProperties) {
            return;
        }

        $propertyExpr = sprintf("CONCAT(' ', %s)", implode(", ' ', ", $processedProperties));

        if (!$this->preVectorized) {
            $queryBuilder->andWhere(sprintf('ts_matches(websearch_to_tsquery(%s, :query), to_tsvector(%s, %s))=true', $this->postgresTsConfigString, $this->postgresTsConfigString, $propertyExpr));
        } else {
            $queryBuilder->andWhere(sprintf('ts_matches(websearch_to_tsquery(%s, :query), (%s))=true', $this->postgresTsConfigString, $propertyExpr));
        }

        $queryBuilder->setParameter('query', $query);
    }

    public function getDescription(string $resourceClass): array
    {
        $properties = [];
        foreach ($this->getProperties() as $property) {
            if ($this->isPropertyMapped($property, $resourceClass)) {
                $properties[] = $this->normalizePropertyName($property);
            }
        }

        return [$this->textSearchParameterName => [
            'property' => implode(', ', $properties),
            'type' => 'string',
            'required' => false,
            'schema' => [
                'type' => 'string',
            ],
        ]];
    }
}