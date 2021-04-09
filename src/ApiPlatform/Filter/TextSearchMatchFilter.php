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
        if (!$this->isPropertyEnabled($property, $resourceClass) || !$this->isPropertyMapped($property, $resourceClass, true)) {
            return null;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $field = $property;

        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field] = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator, $resourceClass, Join::LEFT_JOIN);
        }

        return sprintf('%s.%s', $alias, $field);
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = [])
    {
        if (isset($context['filters']) && !isset($context['filters'][$this->textSearchParameterName])) {
            return;
        }

        if (!isset($context['filters'][$this->textSearchParameterName]) || !\is_array($context['filters'][$this->textSearchParameterName])) {
            parent::apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);

            return;
        }

        if (count(array_flip($context['filters'][$this->textSearchParameterName])) === 1) {
            $query = end($context['filters'][$this->textSearchParameterName]);
        } else {
            $this->logger->notice('Invalid filter ignored', [
                'exception' => new InvalidArgumentException(sprintf('Multiple different query strings given for filter %s', $this->textSearchParameterName))
            ]);
            return;
        }

        $properties = array_map(function ($p) {
            return $this->denormalizePropertyName($p);
        }, array_keys($context['filters'][$this->textSearchParameterName]));

        $processedProperties = [];
        foreach ($properties as $property) {
            if (($processedProperty = $this->processProperty($property, $queryBuilder, $queryNameGenerator, $resourceClass)) !== null) {
                $processedProperties[] = $processedProperty;
            }
        }
        $propertyExpr = sprintf("CONCAT(' ', %s)", implode(", ' ', ", $processedProperties));

        if (!$this->preVectorized) {
            $queryBuilder->andWhere(sprintf('ts_matches(websearch_to_tsquery(%s, :query), to_tsvector(%s, %s))=true', $this->postgresTsConfigString, $this->postgresTsConfigString, $propertyExpr));
        } else {
            $queryBuilder->andWhere(sprintf('ts_matches(websearch_to_tsquery(%s, :query), %s)=true', $this->postgresTsConfigString, $this->postgresTsConfigString, $propertyExpr));
        }

        $queryBuilder->setParameter('query', $query);
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->getProperties();
        if (null === $properties) {
            $properties = array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        foreach ($properties as $property => $propertyOptions) {
            if (!$this->isPropertyMapped($property, $resourceClass)) {
                continue;
            }
            $propertyName = $this->normalizePropertyName($property);
            $description[sprintf('%s[%s]', $this->textSearchParameterName, $propertyName)] = [
                'property' => $propertyName,
                'type' => 'string',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
    }
}