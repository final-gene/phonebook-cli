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
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function getPasswordFromUser(SymfonyStyle $io): string
    {
        $password = (string)$io->askHidden('Enter the password');
        $passwordConfirmation = (string)$io->askHidden('Confirm the password');

        if ('' === $password || $password !== $passwordConfirmation) {
            throw new InvalidArgumentException(
                'The password (confirmation) must not be empty and must be identical with the password!'
            );
        }

        return $passwordConfirmation;
    }
}
