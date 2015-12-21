<?php
namespace Uppu4\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class MediaInfoType extends Type
{
    const MEDIAINFOTYPE = 'mediainfotype';

    public function convertToPHPValue($info) {

        $info = json_decode($info);
        $mediaInfo = \Uppu4\Entity\MediaInfo::setMediaInfo($info);
        return $mediaInfo;
    }

    public function convertToDatabaseValue(\Uppu4\Entity\MediaInfo $mediaInfo) {
        $mediaInfo = json_encode($mediaInfo);
        return $mediaInfo;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    public function getName() {
        return self::MEDIAINFOTYPE;
    }
}
