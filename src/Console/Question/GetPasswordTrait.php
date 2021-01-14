<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Question;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Get password
 *
 * @package FinalGene\PhoneBook\Console\Question
 */
trait GetPasswordTrait
{
    /**
     * Get password form user input
     *
     * @param SymfonyStyle $io Console style
     *
     * @return string
     */
    protected function getPasswordFromUser(SymfonyStyle $io): string
    {
        return (string)$io->askHidden('Enter the password');
    }
}
