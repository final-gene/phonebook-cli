<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Helper;

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
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
     * @return SerializerInterface
     * @throws \JMS\Serializer\Exception\InvalidArgumentException
     */
    public function getAvmSerializer(): SerializerInterface
    {
        return SerializerBuilder::create()
            ->addMetadataDir(
                __DIR__ . '/../../../config/jms',
                'FinalGene\PhoneBook\AVM'
            )
            ->build();
    }
}
