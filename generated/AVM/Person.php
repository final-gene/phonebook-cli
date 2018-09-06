<?php

namespace FinalGene\PhoneBook\AVM;

/**
 * Class representing Person
 */
class Person
{

    /**
     * @property string $realName
     */
    private $realName = null;

    /**
     * Ich weiß noch nicht wozu das gut ist. Ich
     *  werde wohl mal in das Windowsprogramm zur FritzBox schauen müssen. Es
     *  tauch auch sehr selten auf. Trage ich eine Url ein, passiert nicht
     *  wirklich etwas. Das FritzFon MT-F hat einen Farbdisplay und kann wohl
     *  Bilder anzeigen, so ist es zumindest auf den Prospekten
     *  dargestellt
     *
     * @property string $imageUrl
     */
    private $imageUrl = null;

    /**
     * Gets as realName
     *
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * Sets a new realName
     *
     * @param string $realName
     *
     * @return self
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;

        return $this;
    }

    /**
     * Gets as imageUrl
     *
     * Ich weiß noch nicht wozu das gut ist. Ich
     *  werde wohl mal in das Windowsprogramm zur FritzBox schauen müssen. Es
     *  tauch auch sehr selten auf. Trage ich eine Url ein, passiert nicht
     *  wirklich etwas. Das FritzFon MT-F hat einen Farbdisplay und kann wohl
     *  Bilder anzeigen, so ist es zumindest auf den Prospekten
     *  dargestellt
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Sets a new imageUrl
     *
     * Ich weiß noch nicht wozu das gut ist. Ich
     *  werde wohl mal in das Windowsprogramm zur FritzBox schauen müssen. Es
     *  tauch auch sehr selten auf. Trage ich eine Url ein, passiert nicht
     *  wirklich etwas. Das FritzFon MT-F hat einen Farbdisplay und kann wohl
     *  Bilder anzeigen, so ist es zumindest auf den Prospekten
     *  dargestellt
     *
     * @param string $imageUrl
     *
     * @return self
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }


}

