<?php
/*
 * This file is part of the Steam package.
 *
 * (c) Michael B Muller <muller.michaelb@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Steam;

class Properties
{

    /**
     * Temperature K
     * @var double
     */
    public $temperature = NULL;

    /**
     * Pressure MPa
     * @var double
     */
    public $pressure = NULL;

    /**
     * Quality of Steam
     * If Saturated, 0 to 1; Otherwise null
     * @var double
     */
    public $quality = NULL;

    /**
     * Saturated Liquid Properties
     * If qualify between 0 and 1
     * @var Properties
     */
    public $saturatedLiquid = null;

    /**
     * Saturated Gas Properties
     * If qualify between 0 and 1
     * @var Properties
     */
    public $saturatedGas = null;

    /**
     * Massflow kg/hr
     * @var double
     */
    public $massFlow = 0;

    /**
     * Volume flow m3/hr
     * @var double
     */
    public $volumeFlow = 0;

    /**
     * Internal Energy kJ/kg
     * @var double
     */
    public $internalEnergy = null;

    /**
     * Specific Enthalpy kJ/kg
     * @var double
     */
    public $specificEnthalpy = NULL;

    /**
     * Specific Entropy kJ/kg/R
     * @var double
     */
    public $specificEntropy = NULL;

    /**
     * Specific Entropy m3/kg
     * @var double
     */
    public $specificVolume = NULL;

    /**
     * Energy Flow kJ/hr
     * @var double
     */
    public $energyFlow = NULL;

    /**
     * Phase Liquid/Saturated/Gas
     * @var double
     */
    public $phase = NULL;

    /**
     * Density kg/m3
     * @var double
     */
    public $density = NULL;

    /**
     * IAPWS IF97 Steam Region
     * @var double
     */
    public $region = NULL;

    /**
     * Returns Properties based on pressure and temperature
     * @param $pressure
     * @param $temperature
     * @param $massFlow
     * @return Properties
     */
    static function pressureTemperature($pressure, $temperature, $massFlow = null)
    {
        return self::setProperties(IF97::waterPropertiesPT($pressure, $temperature),$massFlow);
    }

    /**
     * @param $pressure
     * @param $quality
     * @param $massFlow
     * @return Properties
     */
    static function pressureQuality($pressure, $quality, $massFlow = null)
    {
        $properties = self::saturatedPressure($pressure);
        $properties->setQuality($quality);
        return self::setProperties($properties,$massFlow);
    }
    /**
     * @param $pressure
     * @param $specificEnthalpy
     * @param $massFlow
     * @return Properties
     */
    static function pressureSpecificEnthalpy($pressure, $specificEnthalpy, $massFlow = null)
    {
        return self::setProperties(IF97::waterPropertiesPH($pressure, $specificEnthalpy), $massFlow);
    }

    static function pressureSpecificEntropy($pressure, $specificEntropy, $massFlow = null)
    {
        return self::setProperties(IF97::waterPropertiesPH($pressure, $specificEntropy), $massFlow);
    }

    static function saturatedPressure($pressure)
    {
        $saturatedProperties = IF97::saturatedPropertiesByPressure($pressure);
        $properties = new Properties();
        $properties->saturatedGas = $saturatedProperties['gas'];
        $properties->saturatedLiquid = $saturatedProperties['liquid'];
        return $properties;
    }

    static function saturatedTemperature($temperature)
    {
        $saturatedProperties = IF97::saturatedPropertiesByTemperature($temperature);
        $properties = new Properties();
        $properties->saturatedGas = $saturatedProperties['gas'];
        $properties->saturatedLiquid = $saturatedProperties['liquid'];
        return $properties;
    }

    /**
     * Determine Steam Properties based on Pressure and Quality
     * @param double $quality (0-1)
     * @return void
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
        $this->temperature = $this->saturatedGas->temperature * 1;
        $this->pressure = $this->saturatedGas->pressure * 1;
        $this->specificEnthalpy = $this->saturatedGas->specificEnthalpy * $quality + $this->saturatedLiquid->specificEnthalpy * (1 - $quality);
        $this->specificEntropy = $this->saturatedGas->specificEntropy * $quality + $this->saturatedLiquid->specificEntropy * (1 - $quality);
        $this->specificVolume = $this->saturatedGas->specificVolume * $quality + $this->saturatedLiquid->specificVolume * (1 - $quality);
        $this->density = 1 / $this->specificVolume;
        $this->quality = $quality;
        $this->phase = 'Saturated';
        //$this->region = $tmp['region'];
    }

    /**
     * Sets the Mass Flow and Energy flow of the Steam
     * @param double $massFlow kg/hr
     * @return void
     */
    public function setMassFlow($massFlow)
    {
        $this->massFlow = $massFlow * 1;
        $this->energyFlow = $this->specificEnthalpy * $this->massFlow / 1000;
        $this->volumeFlow = $this->specificVolume * $this->massFlow * 1000;
    }

    /**
     * @param Properties $properties
     * @param $massFlow
     * @return Properties
     */
    static function setProperties($properties, $massFlow)
    {
        if ($massFlow) $properties->setMassFlow($massFlow);
        return $properties;
    }
}
