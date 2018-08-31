<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command;

use Sabre\VObject\Component\VCard;

/**
 * vCard trait.
 *
 * @package FinalGene\PhoneBook\Console
 */
trait VCardTrait
{
    /**
     * Create vCard.
     *
     * @return VCard
     */
    protected function createVCard(): VCard
    {
        return new VCard();
    }
}
