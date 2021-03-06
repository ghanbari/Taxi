<?php

namespace FunPro\GeoBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use FunPro\GeoBundle\Doctrine\ValueObject\LineString;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;

/**
 * Class LineStringType
 *
 * @package FunPro\GeoBundle\Doctrine\Type
 */
class LineStringType extends Type
{
    const LINESTRING = 'linestring';

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'LINESTRING';
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     *
     * @todo Needed?
     */
    public function getName()
    {
        return self::LINESTRING;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value->count() ? (string) $value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $lineString = new LineString();

        $strPoints = array();
        preg_match_all('/((?:\\d+\\.?(?:\\d+)?)\\s?){2}/', $value, $strPoints);
        foreach ($strPoints[0] as $strPoint) {
            $arrayPoint = explode(' ', $strPoint);
            $point = new Point($arrayPoint[0], $arrayPoint[1]);
            $lineString->attach($point);
        }

        return $lineString;
    }

    public function canRequireSQLConversion()
    {
        return true;
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return sprintf('LineStringFromText(%s)', $sqlExpr);
    }

    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        return sprintf('AsText(%s)', $sqlExpr);
    }
}
