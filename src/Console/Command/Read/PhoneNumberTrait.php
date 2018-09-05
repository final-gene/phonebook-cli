<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\Read;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Phone number trait.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 */
trait PhoneNumberTrait
{
    /**
     * Normalize phone number
     *
     * @param string $phoneNumber Phone number
     *
     * @return string
     * @throws \libphonenumber\NumberParseException
     */
    protected function normalizePhoneNumber(string $phoneNumber): string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        return str_replace(
            ' ',
            '',
            $phoneUtil->format(
                $phoneUtil->parse($phoneNumber, 'DE'),
                PhoneNumberFormat::INTERNATIONAL
            )
        );
    }

    /**
     * Normalize phone number type.
     *
     * @param string $type Phone number type
     *
     * @return string
     */
    protected function normalizePhoneNumberType(string $type): string
    {
        $mapping = [
            '/^.*text.*$/' => 'text',
            '/^.*fax.*$/' => 'fax',
            '/^.*(cell|mobile).*$/' => 'cell',
            '/^.*voice.*$/' => 'voice',
            '/^.*video.*$/' => 'video',
            '/^.*pager.*$/' => 'pager',
            '/^.*home.*$/' => 'home',
            '/^.*work.*$/' => 'work',
        ];

        $typeList = preg_split('/[^a-zA-Z0-9]/', strtolower($type));
        foreach ($typeList as &$singleType) {
            $singleType = preg_replace(
                array_keys($mapping),
                array_values($mapping),
                $singleType
            );
        }

        return implode(
            ',',
            array_intersect($typeList, array_values($mapping))
        );
    }
}
