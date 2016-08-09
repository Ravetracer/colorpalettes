<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 09.02.16
 * Time: 08:28
 */

namespace Colorpalettes;

use MischiefCollective\ColorJizz\Formats\RGB;
use MischiefCollective\ColorJizz\Formats\Hex;

/**
 * Class BaseColor
 * @package Colorpalettes
 */
class BaseColor
{
    /**
     * @var int
     */
    private $red = 0;

    /**
     * @var int
     */
    private $green = 0;

    /**
     * @var int
     */
    private $blue = 0;

    /**
     * @var string
     */
    private $colorName = "";

    /**
     * @return int
     */
    public function getRed(): int
    {
        return (int) $this->red;
    }

    /**
     * @param int $red
     * @return BaseColor
     */
    public function setRed($red = 0): BaseColor
    {
        $this->red = (int) $red;

        return $this;
    }

    /**
     * @return int
     */
    public function getGreen(): int
    {
        return (int) $this->green;
    }

    /**
     * @param int $green
     * @return BaseColor
     */
    public function setGreen($green = 0): BaseColor
    {
        $this->green = (int) $green;

        return $this;
    }

    /**
     * @return int
     */
    public function getBlue(): int
    {
        return (int) $this->blue;
    }

    /**
     * @param int $blue
     * @return BaseColor
     */
    public function setBlue($blue = 0): BaseColor
    {
        $this->blue = (int) $blue;

        return $this;
    }

    /**
     * @param string $name
     * @return BaseColor
     */
    public function setName($name = ""): BaseColor
    {
        $this->colorName = filter_var($name, FILTER_SANITIZE_STRING);

        return $this;
    }

    /**
     * @return Hex
     */
    public function getHexValue(): Hex
    {
        $rgb = new RGB($this->getRed(), $this->getGreen(), $this->getBlue());

        return $rgb->toHex();
    }

    /**
     * @return string
     */
    public function getCssHex(): string
    {
        return '#'.sprintf('%02x', $this->getRed()).sprintf('%02x', $this->getGreen()).sprintf('%02x', $this->getBlue());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->colorName;
    }
}