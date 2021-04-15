<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\ApiPlatform\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Common\PropertyHelperTrait;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\PropertyHelperTrait as OrmPropertyHelperTrait;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Class AbstractFullTextSearchFilter
 * @package App\Bambi\PostgresTextSearchBundle\ApiPlatform\Filter
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
abstract class AbstractTextSearchFilter implements FilterInterface
{
    use OrmPropertyHelperTrait;
    use PropertyHelperTrait;

    protected ManagerRegistry $managerRegistry;
    protected ?LoggerInterface $logger;
    protected ?array $properties;
    protected ?NameConverterInterface $nameConverter;
    protected string $textSearchParameterName;
    protected bool $preVectorized;
    protected string $postgresTsConfigString;

    public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger = null, array $properties = null, NameConverterInterface $nameConverter = null, string $textSearchParameterName = 'ts_query', $postgresTsConfigString = "'english'", bool $preVectorized = false)
    {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger ?? new NullLogger();
        $this->properties = $properties;
        $this->nameConverter = $nameConverter;
        $this->textSearchParameterName = $textSearchParameterName;
        $this->preVectorized = $preVectorized;
        $this->postgresTsConfigString = $postgresTsConfigString;
    }

    protected function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    protected function getProperties(): ?array
    {
        return $this->properties;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function denormalizePropertyName($property): string
    {
        if (!$this->nameConverter instanceof NameConverterInterface) {
            return $property;
        }

        return implode('.', array_map([$this->nameConverter, 'denormalize'], explode('.', (string) $property)));
    }

    protected function normalizePropertyName($property): string
    {
        if (!$this->nameConverter instanceof NameConverterInterface) {
            return $property;
        }

        return implode('.', array_map([$this->nameConverter, 'normalize'], explode('.', (string) $property)));
    }
}