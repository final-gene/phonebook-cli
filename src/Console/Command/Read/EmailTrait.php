<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\Read;

/**
 * EMail trait.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 */
trait EmailTrait
{
    /**
     * Normalize email type.
     *
     * @param string $type
     *
     * @return string
     */
    protected function normalizeEmailType(string $type): string
    {
        $mapping = [
            '/^.*home.*$/' => 'home',
            '/^.*work.*$/' => 'work',
        ];

        $typeList = preg_split('/[^a-zA-Z0-9]/', strtolower($type));
        foreach ($typeList as &$singleType) {
            $singleType = preg_replace(
                array_keys($mapping),
                array_values($mapping),
                $singleType);
        }

        return implode(
            ',',
            array_intersect($typeList, array_values($mapping))
        );
    }
}
