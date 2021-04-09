<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\ApiPlatform\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Class AbstractFullTextSearchFilter
 * @package App\Bambi\PostgresTextSearchBundle\ApiPlatform\Filter
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
abstract class AbstractTextSearchFilter extends AbstractContextAwareFilter
{
    protected string $textSearchParameterName;
    protected bool $preVectorized;
    protected string $postgresTsConfigString;

    public function __construct(ManagerRegistry $managerRegistry, ?RequestStack $requestStack = null, LoggerInterface $logger = null, array $properties = null, NameConverterInterface $nameConverter = null, string $textSearchParameterName = 'full_text_search', bool $preVectorized = false, $postgresTsConfigString = "'english'")
    {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties, $nameConverter);

        $this->textSearchParameterName = $textSearchParameterName;
        $this->preVectorized = $preVectorized;
        $this->postgresTsConfigString = $postgresTsConfigString;
    }
}