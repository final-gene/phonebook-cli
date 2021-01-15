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
use function is_resource;

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
     * @param SplFileInfo $file Source file
     *
     * @return resource
     * @throws \FinalGene\PhoneBook\Exception\ReadFileException
     */
    protected function getResourceForFile(SplFileInfo $file)
    {
        $pathname = $file->getPathname();
        if ('php://stdin' !== $pathname
            &&  !$file->isReadable()
        ) {
            throw new ReadFileException(
                'Could not read from ' . $file->getPathname()
            );
        }

        if (!is_resource($resource = fopen($file->getPathname(), 'rb'))) {
            throw new ReadFileException(
                'Could not read from ' . $file->getPathname()
            );
        }

        return $resource;
    }

    /**
     * Read file
     *
     * @param SplFileInfo $file Source file
     *
     * @return string
     * @throws \FinalGene\PhoneBook\Exception\ReadFileException
     * @throws \FinalGene\PhoneBook\Exception\EmptyFileException
     */
    protected function readFile(SplFileInfo $file): string
    {
        $resource = $this->getResourceForFile($file);
        $data = stream_get_contents($resource);
        fclose($resource);

        if (empty($data)) {
            throw new EmptyFileException(
                'Could not read from ' . $file->getPathname()
            );
        }

        return $data;
    }
}
