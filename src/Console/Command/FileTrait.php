<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command;

use FinalGene\PhoneBook\Exception\EmptyFileException;
use FinalGene\PhoneBook\Exception\ReadFileException;
use SplFileInfo;

/**
 * File trait.
 *
 * @package FinalGene\PhoneBook\Console
 */
trait FileTrait
{
    /**
     * Read file
     *
     * @param SplFileInfo $file
     *
     * @return string
     * @throws \FinalGene\PhoneBook\Exception\ReadFileException
     * @throws \FinalGene\PhoneBook\Exception\EmptyFileException
     */
    protected function readFile(SplFileInfo $file): string
    {
        $stdIn = @fopen($file->getPathname(), 'rb');
        if (false === $stdIn) {
            throw new ReadFileException(
                'Could not read from ' . $file->getPathname()
            );
        }

        stream_set_timeout($stdIn, 10);
        stream_set_blocking($stdIn, false);

        $data = stream_get_contents($stdIn);

        fclose($stdIn);

        if (empty($data)) {
            throw new EmptyFileException(
                'Could not read from ' . $file->getPathname()
            );
        }

        return $data;
    }
}
