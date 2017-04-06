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

class Properties{

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
     * IAPWS Steam Region
     * @var double 
     */
    public $region = NULL;

    /**
     * Determines Full Steam Properties based on $properties
     * 
     * If $properties is NULL, creates structured blank Steam_Object 
     * 
     * $properties must include "pressure" and 1 of the following:
     * temperature, quality, specificEnthalpy, or specificEntropy
     * 
     * $properties may also include "massFlow"
     * 
     * @param array() $properties 
     */
    public function __construct($properties = NULL) {
        
        //Set Massflow if provided
        if (isset($properties['massFlow'])) $this->massFlow = $properties['massFlow'];
       
        //Determine Steam Properties
        if (isset($properties['temperature'])) $pressureAnd='temperature';
        if (isset($properties['quality'])) $pressureAnd='quality';
        if (isset($properties['specificEnthalpy'])) $pressureAnd='specificEnthalpy';
        if (isset($properties['specificEntropy'])) $pressureAnd='specificEntropy';
        
        if (isset($properties['pressure']) and isset($pressureAnd)) {
            switch ($pressureAnd) {
                case 'temperature':
                    $properties = IF97::waterPropertiesPT($properties['pressure'],$properties['temperature']);
                    break;
                case 'quality':
                    $properties = $this->propertyQuality($properties['pressure'], $properties['quality']);
                    break;
                case 'specificEnthalpy':
                    $properties = $this->iapws->waterPropertiesPH($properties['pressure'], $properties['specificEnthalpy']);
                    break;
                case 'specificEntropy':
                    $properties = $this->iapws->waterPropertiesPS($properties['pressure'], $properties['specificEntropy']);
                    break;
            }
            $this->setProperties($properties);
        }        
    }

    /**
     * Determine Steam Properties based on Pressure and Quality
     * @param double $quality (0-1)
     * @return array() Steam Properties
     */
    public function propertyQuality($quality){
        $this->quality = $quality;
        $this->temperature = $this->saturatedGas->temperature * 1;
        $this->pressure = $this->saturatedGas->pressure * 1;
        $this->specificEnthalpy = $this->saturatedGas->specificEnthalpy * $quality + $this->saturatedLiquid->specificEnthalpy * (1-$quality);
        $this->specificEntropy = $this->saturatedGas->specificEntropy * $quality + $this->saturatedLiquid->specificEntropy * (1-$quality);
        $this->specificVolume = $this->saturatedGas->specificVolume * $quality + $this->saturatedLiquid->specificVolume * (1-$quality);
        $this->density = 1/$this->specificVolume;
        $this->quality = $quality;
        //$this->region = $tmp['region'];
    }

    /**
     * Sets the Mass Flow and Energy flow of the Steam
     * @param double $massFlow kg/hr
     */
    public function setMassFlow($massFlow){       
        $this->massFlow = $massFlow*1;
        $this->energyFlow = $this->specificEnthalpy * $this->massFlow / 1000;        
        $this->volumeFlow = $this->specificVolume * $this->massFlow * 1000;
    }

    /**
     * Set Steam Properties as Object Variables
     * @param array() $properties  Steam Properties
     */
    private function setProperties($properties) {
        $this->temperature = $properties['temperature'];
        $this->pressure = $properties['pressure'];
        $this->specificEnthalpy = $properties['specificEnthalpy'];
        $this->specificEntropy = $properties['specificEntropy'];
        $this->specificVolume = $properties['specificVolume'];
        $this->quality = $properties['quality'];
        $this->density = null;
        if ($properties['specificVolume']<>0) $this->density = 1/$properties['specificVolume'];
        $this->region = 0;
        if (isset($properties['region']) ) $this->region = $properties['region'];
        $this->phase = 'Saturated';
        if (is_null($this->quality)){
            $this->phase = $properties['phase'];
        }
        $this->setMassFlow($this->massFlow);
    }  
}
