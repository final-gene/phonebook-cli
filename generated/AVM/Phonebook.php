<?php

namespace FinalGene\PhoneBook\AVM;

/**
 * Class representing Phonebook
 */
class Phonebook
{

    /**
     * Gibt den Namen des Telefonbuchs an.
     *
     * @property string $name
     */
    private $name = null;

    /**
     * @property integer $owner
     */
    private $owner = null;

    /**
     * @property \FinalGene\PhoneBook\AVM\Contact[] $contact
     */
    private $contact = [

    ];

    /**
     * Gets as name
     *
     * Gibt den Namen des Telefonbuchs an.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets a new name
     *
     * Gibt den Namen des Telefonbuchs an.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets as owner
     *
     * @return integer
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Sets a new owner
     *
     * @param integer $owner
     *
     * @return self
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Adds as contact
     *
     * @return self
     *
     * @param \FinalGene\PhoneBook\AVM\Contact $contact
     */
    public function addToContact(\FinalGene\PhoneBook\AVM\Contact $contact)
    {
        $this->contact[] = $contact;

        return $this;
    }

    /**
     * isset contact
     *
     * @param scalar $index
     *
     * @return boolean
     */
    public function issetContact($index)
    {
        return isset($this->contact[$index]);
    }

    /**
     * unset contact
     *
     * @param scalar $index
     *
     * @return void
     */
    public function unsetContact($index)
    {
        unset($this->contact[$index]);
    }

    /**
     * Gets as contact
     *
     * @return \FinalGene\PhoneBook\AVM\Contact[]
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Sets a new contact
     *
     * @param \FinalGene\PhoneBook\AVM\Contact[] $contact
     *
     * @return self
     */
    public function setContact(array $contact)
    {
        $this->contact = $contact;

        return $this;
    }


}

