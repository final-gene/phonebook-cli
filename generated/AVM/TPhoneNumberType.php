<?php

namespace FinalGene\PhoneBook\AVM;

/**
 * Class representing TPhoneNumberType
 *
 *
 * XSD Type: T_PhoneNumber
 */
class TPhoneNumberType
{

    /**
     * @var string $__value
     */
    private $__value = null;

    /**
     * @var int $prio
     */
    private $prio = null;

    /**
     * @var string $type
     */
    private $type = null;

    /**
     * @var int $quickdial
     */
    private $quickdial = null;

    /**
     * @var string $vanity
     */
    private $vanity = null;

    /**
     * Construct
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value($value);
    }

    /**
     * Gets or sets the inner value
     *
     * @param string $value
     * @return string
     */
    public function value()
    {
        if ($args = func_get_args()) {
            $this->__value = $args[0];
        }
        return $this->__value;
    }

    /**
     * Gets a string value
     *
     * @return string
     */
    public function __toString()
    {
        return strval($this->__value);
    }

    /**
     * Gets as prio
     *
     * @return int
     */
    public function getPrio()
    {
        return $this->prio;
    }

    /**
     * Sets a new prio
     *
     * @param int $prio
     * @return self
     */
    public function setPrio($prio)
    {
        $this->prio = $prio;
        return $this;
    }

    /**
     * Gets as type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets a new type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Gets as quickdial
     *
     * @return int
     */
    public function getQuickdial()
    {
        return $this->quickdial;
    }

    /**
     * Sets a new quickdial
     *
     * @param int $quickdial
     * @return self
     */
    public function setQuickdial($quickdial)
    {
        $this->quickdial = $quickdial;
        return $this;
    }

    /**
     * Gets as vanity
     *
     * @return string
     */
    public function getVanity()
    {
        return $this->vanity;
    }

    /**
     * Sets a new vanity
     *
     * @param string $vanity
     * @return self
     */
    public function setVanity($vanity)
    {
        $this->vanity = $vanity;
        return $this;
    }


}

