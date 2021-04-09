<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Class Tsvector
 * @package App\Bambi\PostgresTextSearchBundle\Doctrine\Types
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class Tsvector extends Type
{

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'tsvector';
    }

    public function getName()
    {
        return 'tsvector';
    }
}