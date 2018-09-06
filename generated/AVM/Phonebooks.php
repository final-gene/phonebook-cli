<?php

namespace FinalGene\PhoneBook\AVM;

/**
 * Class representing Phonebooks
 */
class Phonebooks
{

    /**
     * Mehr als 1 phonebook werden ignoriert.
     *
     * @property \FinalGene\PhoneBook\AVM\Phonebook $phonebook
     */
    private $phonebook = null;

    /**
     * Gets as phonebook
     *
     * Mehr als 1 phonebook werden ignoriert.
     *
     * @return \FinalGene\PhoneBook\AVM\Phonebook
     */
    public function getPhonebook()
    {
        return $this->phonebook;
    }

    /**
     * Sets a new phonebook
     *
     * Mehr als 1 phonebook werden ignoriert.
     *
     * @param \FinalGene\PhoneBook\AVM\Phonebook $phonebook
     *
     * @return self
     */
    public function setPhonebook(\FinalGene\PhoneBook\AVM\Phonebook $phonebook)
    {
        $this->phonebook = $phonebook;

        return $this;
    }


}

