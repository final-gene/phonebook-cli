<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command;

use FinalGene\PhoneBook\Exception\ReadFileException;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Splitter\VCard as VCardList;
use SplFileInfo;

/**
 * vCard trait.
 *
 * @package FinalGene\PhoneBook\Console
 */
trait VCardTrait
{
    use FileTrait;

    /**
     * Create vCard.
     *
     * @return VCard
     */
    protected function createVCard(): VCard
    {
        return new VCard();
    }

    /**
     * Get vCard by file.
     *
     * @param SplFileInfo $file Source file
     *
     * @return VCardList
     * @throws ReadFileException
     */
    protected function getVCardListByFile(SplFileInfo $file): VCardList
    {
        return new VCardList($this->getResourceForFile($file));
    }
}
