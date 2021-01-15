<?php

namespace FinalGene\PhoneBook\AVM;

/**
 * Class representing Contact
 */
class Contact
{

    /**
     * Gibt an, ob dieses eine wichtige Person ist. 0...nicht
     *  wichtig (oder gar nicht vorhanden) 1...ist wichtig und es klingelt auch
     *  bei Anrufsperre
     *
     * @var int $category
     */
    private $category = null;

    /**
     * @var \FinalGene\PhoneBook\AVM\Person $person
     */
    private $person = null;

    /**
     * @var \FinalGene\PhoneBook\AVM\Number[] $telephony
     */
    private $telephony = null;

    /**
     * Sinn unbekannt
     *
     * @var \FinalGene\PhoneBook\AVM\Services $services
     */
    private $services = null;

    /**
     * Sinn unbekannt.
     *
     * @var \FinalGene\PhoneBook\AVM\Setup $setup
     */
    private $setup = null;

    /**
     * Gets as category
     *
     * Gibt an, ob dieses eine wichtige Person ist. 0...nicht
     *  wichtig (oder gar nicht vorhanden) 1...ist wichtig und es klingelt auch
     *  bei Anrufsperre
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Sets a new category
     *
     * Gibt an, ob dieses eine wichtige Person ist. 0...nicht
     *  wichtig (oder gar nicht vorhanden) 1...ist wichtig und es klingelt auch
     *  bei Anrufsperre
     *
     * @param int $category
     * @return self
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Gets as person
     *
     * @return \FinalGene\PhoneBook\AVM\Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Sets a new person
     *
     * @param \FinalGene\PhoneBook\AVM\Person $person
     * @return self
     */
    public function setPerson(\FinalGene\PhoneBook\AVM\Person $person)
    {
        $this->person = $person;
        return $this;
    }

    /**
     * Adds as number
     *
     * @return self
     * @param \FinalGene\PhoneBook\AVM\Number $number
     */
    public function addToTelephony(\FinalGene\PhoneBook\AVM\Number $number)
    {
        $this->telephony[] = $number;
        return $this;
    }

    /**
     * isset telephony
     *
     * @param int|string $index
     * @return bool
     */
    public function issetTelephony($index)
    {
        return isset($this->telephony[$index]);
    }

    /**
     * unset telephony
     *
     * @param int|string $index
     * @return void
     */
    public function unsetTelephony($index)
    {
        unset($this->telephony[$index]);
    }

    /**
     * Gets as telephony
     *
     * @return \FinalGene\PhoneBook\AVM\Number[]
     */
    public function getTelephony()
    {
        return $this->telephony;
    }

    /**
     * Sets a new telephony
     *
     * @param \FinalGene\PhoneBook\AVM\Number[] $telephony
     * @return self
     */
    public function setTelephony(array $telephony)
    {
        $this->telephony = $telephony;
        return $this;
    }

    /**
     * Gets as services
     *
     * Sinn unbekannt
     *
     * @return \FinalGene\PhoneBook\AVM\Services
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Sets a new services
     *
     * Sinn unbekannt
     *
     * @param \FinalGene\PhoneBook\AVM\Services $services
     * @return self
     */
    public function setServices(\FinalGene\PhoneBook\AVM\Services $services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * Gets as setup
     *
     * Sinn unbekannt.
     *
     * @return \FinalGene\PhoneBook\AVM\Setup
     */
    public function getSetup()
    {
        return $this->setup;
    }

    /**
     * Sets a new setup
     *
     * Sinn unbekannt.
     *
     * @param \FinalGene\PhoneBook\AVM\Setup $setup
     * @return self
     */
    public function setSetup(\FinalGene\PhoneBook\AVM\Setup $setup)
    {
        $this->setup = $setup;
        return $this;
    }


}

