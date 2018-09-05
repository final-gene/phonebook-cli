<?php

namespace FinalGene\PhoneBook\AVM\Telephony;

/**
 * Class representing TelephonyAnonymousType
 */
class TelephonyAnonymousType
{

    /**
     * @property \FinalGene\PhoneBook\AVM\Number[] $number
     */
    private $number = [

    ];

    /**
     * Adds as number
     *
     * @return self
     *
     * @param \FinalGene\PhoneBook\AVM\Number $number
     */
    public function addToNumber(\FinalGene\PhoneBook\AVM\Number $number)
    {
        $this->number[] = $number;

        return $this;
    }

    /**
     * isset number
     *
     * @param scalar $index
     *
     * @return boolean
     */
    public function issetNumber($index)
    {
        return isset($this->number[$index]);
    }

    /**
     * unset number
     *
     * @param scalar $index
     *
     * @return void
     */
    public function unsetNumber($index)
    {
        unset($this->number[$index]);
    }

    /**
     * Gets as number
     *
     * @return \FinalGene\PhoneBook\AVM\Number[]
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Sets a new number
     *
     * @param \FinalGene\PhoneBook\AVM\Number[] $number
     *
     * @return self
     */
    public function setNumber(array $number)
    {
        $this->number = $number;

        return $this;
    }


}

