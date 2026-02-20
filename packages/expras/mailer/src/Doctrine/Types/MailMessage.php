<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 22.08.13
 * Time: 13:50
 */
namespace ExprAs\Mailer\Doctrine\Types;

use Doctrine\DBAL\Types\BlobType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class MailMessage extends BlobType
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        /** @var Email $value */
        return serialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;

        return unserialize($value);
    }
}

