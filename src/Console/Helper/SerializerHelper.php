<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Helper;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\Console\Helper\Helper;

/**
 * Serializer helper class.
 *
 * @package FinalGene\PhoneBook\Console
 */
class SerializerHelper extends Helper
{
    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return __CLASS__;
    }

    /**
     * Get avm serializer.
     *
     * @return Serializer
     * @throws \JMS\Serializer\Exception\InvalidArgumentException
     */
    public function getAvmSerializer(): Serializer
    {
        return SerializerBuilder::create()
            ->addMetadataDir(
                __DIR__ . '/../../../config/jms',
                'FinalGene\PhoneBook\AVM'
            )
            ->build();
    }
}
