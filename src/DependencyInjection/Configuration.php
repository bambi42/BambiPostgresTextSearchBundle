<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package App\Bambi\PostgresTextSearchBundle\DependencyInjection
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('bambi_postgres_full_text_search');
        $treeBuilder->getRootNode()
            ->children()
        ;

        return $treeBuilder;
    }
}