<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\DependencyInjection;

use Bambi\PostgresTextSearchBundle\Doctrine\Query;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class BambiPostgresTextSearchExtension
 * @package App\Bambi\DependencyInjection
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class BambiPostgresTextSearchExtension extends Extension implements PrependExtensionInterface
{

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
    }

    /**
     * @inheritDoc
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if(!isset($bundles['DoctrineBundle'])) {
            return;
        }

        $doctrineConfig = ['orm' => ['dql' => ['string_functions' => []]]];
        $customDQLFunctions = [
            'to_tsvector' => Query\ToTsvector::class,
            'to_tsquery'  => Query\ToTsquery::class,
            'websearch_to_tsquery' => Query\WebsearchToTsquery::class,
            'plainto_tsquery' => Query\PlaintoTsquery::class,
            'phraseto_tsquery' => Query\PhrasetoTsquery::class,
            'ts_matches'  => Query\TsMatches::class,
            'text' => Query\Text::class,
        ];
        foreach($customDQLFunctions as $functionName => $functionNode) {
            $doctrineConfig['orm']['dql']['string_functions'][$functionName] = $functionNode;
        }
        $container->prependExtensionConfig('doctrine', $doctrineConfig);
    }
}