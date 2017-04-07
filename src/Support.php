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

/**
 * Extends IAPWS Core Equations
 */
class Support extends Core
{

    /**
     * Maximum Pressure of Water MPa
     */
    const PRESSURE_MIN = 0.01;

    /**
     * Maximum Temperature of Water K
     */
    const TEMPERATURE_MIN = 273.15;

    /**
     * Pressure of Water where ALL regions meet MPa
     */
    const PRESSURE_Tp = 16.5291643;

    /**
     * Temperature of Water where ALL regions meet K
     */
    const TEMPERATURE_Tp = 623.15;

    /**
     * Critical Pressure of Water MPa
     */
    const PRESSURE_CRIT = 22.064;

    /**
     * Critical Temperature of Water K
     */
    const TEMPERATURE_CRIT = 647.096;

    /**
     * Maximum Pressure of Water MPa
     */
    const PRESSURE_MAX = 100;

    /**
     * Maximum Temperature of Water MPa
     */
    const TEMPERATURE_MAX = 1073.15;

    /**
     * Maximum Temperature of Water for Region 3 MPa
     */
    const TEMPERATURE_REGION3_MAX = 863.15;

    /**
     * Returns Steam Properties based on $pressure and $temperature
     *
     * @param double $pressure MPa
     * @param double $temperature K
     * @return Properties SteamProperties
     */
    static function waterPropertiesPT($pressure, $temperature)
    {
        $properties = new Properties();
        $region = self::regionSelect($pressure, $temperature);
        switch ($region) {
            case 1:
                $properties = Core::region1($pressure, $temperature);
                $properties->phase = "Liquid";
                break;
            case 2:
                $properties = Core::region2($pressure, $temperature);
                $properties->phase = "Gas";
                break;
            case 3:
                $properties = self::region3($pressure, $temperature);
                $properties->phase = "Liquid";
                break;
        }
        $properties->region = $region;
        $properties->temperature = $temperature;
        $properties->quality = NULL;
        return $properties;
    }

    /**
     * Returns the IAPWS region based on $pressure and $temperature
     *
     * @param float $pressure MPa
     * @param float $temperature K
     * @return int Region
     */
    static function regionSelect($pressure, $temperature)
    {
        $region = 0;
        //Determine Boundary
        if ($temperature >= self::TEMPERATURE_Tp) {
            $boundaryPressure = Core::boundaryByTemperatureRegion3to2($temperature);
        } else {
            $boundaryPressure = self::saturatedPressure($temperature);
        }

        if ($temperature >= self::TEMPERATURE_MIN and $temperature <= self::TEMPERATURE_Tp) {
            if ($pressure <= self::PRESSURE_MAX and $pressure >= $boundaryPressure) {
                $region = 1; //Liquid
            }
            if ($pressure > 0 and $pressure <= $boundaryPressure) {
                $region = 2; //Gas
            }
        }
        if ($temperature >= self::TEMPERATURE_Tp and $temperature <= self::TEMPERATURE_REGION3_MAX) {
            if ($pressure > 0 and $pressure <= $boundaryPressure) {
                $region = 2; //Gas
            }
            if ($pressure <= self::PRESSURE_MAX and $pressure > $boundaryPressure) {
                $region = 3; //Liquid
            }
        }
        if ($temperature > self::TEMPERATURE_REGION3_MAX and $temperature <= self::TEMPERATURE_MAX) $region = 2; //Gas
        return $region;
    }

    /**
     * Determines the Density in Region 3 based on $pressure and $temperature
     *
     * @param float $pressure MPa
     * @param float $temperature K
     * @return Properties kg/m3
     */
    static function region3($pressure, $temperature)
    {

        $boundary13Properties = Core::region1($pressure, self::TEMPERATURE_Tp);
        $region3propA = Core::region3Density($densityA = $boundary13Properties->density, $temperature);
        $testPressureA = $region3propA->pressure;

        $boundary23Properties = Core::region2($pressure, Core::boundaryByPressureRegion3to2($pressure));
        $region3propB = Core::region3Density($densityB = $boundary23Properties->density, $temperature);
        $testPressureB = $region3propB->pressure;

        $pressureNew = null;
        $region3propNew = null;

        //Base Goal Seek
        for ($x = 1; $x < 5; $x++) {
            $densityNew = ($densityA + $densityB) / 2;
            $region3propNew = Core::region3Density($densityNew, $temperature);
            $pressureNew = $region3propNew->pressure;
            if ($pressure > $pressureNew) {
                $densityB = $densityNew;
                $testPressureB = $pressureNew;
            } else {
                $densityA = $densityNew;
                $testPressureA = $pressureNew;
            }
        }

        //Uses Linear Interpolation
        $counter = 0;
        while (abs($pressureNew - $pressure) > 1e-10 and $counter++ < 50 and $testPressureA <> $testPressureB) {
            $densityNew = $pressure * ($densityA - $densityB) / ($testPressureA - $testPressureB) + $densityA - $testPressureA * ($densityA - $densityB) / ($testPressureA - $testPressureB);
            $region3propNew = Core::region3Density($densityNew, $temperature);
            $pressureNew = $region3propNew->pressure;
            $densityB = $densityA;
            $densityA = $densityNew;
            $testPressureB = $testPressureA;
            $testPressureA = $pressureNew;
        }
        return $region3propNew;
    }

    /**
     * Returns the saturated pressure based on temperature
     * @param double $temperature K
     * @return double pressure MPa
     */
    static function saturatedPressure($temperature)
    {
        if ($temperature <= self::TEMPERATURE_CRIT) return Core::region4($temperature);
        return null;
    }

    /**
     * Returns the saturated temperature based on pressure
     * @param double $pressure MPa
     * @return double temperature K
     */
    static function saturatedTemperature($pressure)
    {
        if ($pressure <= self::PRESSURE_CRIT) return Core::backwardRegion4($pressure);
        return null;
    }

    /**
     * Provides saturated liquid and gas properties for a given pressure
     * @param double $pressure MPa
     * @return array()
     */
    static function saturatedPropertiesByPressure($pressure)
    {
        $temperature = self::saturatedTemperature($pressure);
        $properties['gas'] = Core::region2($pressure, $temperature);
        $properties['gas']->region = 2;
        $properties['gas']->quality = 1;
        if ($temperature >= self::TEMPERATURE_MIN and $temperature <= self::TEMPERATURE_Tp) {
            $properties['liquid'] = Core::region1($pressure, $temperature);
            $properties['liquid']->quality = 0;
            $properties['liquid']->region = 1;
        }
        if ($temperature > self::TEMPERATURE_Tp and $temperature <= self::TEMPERATURE_CRIT) {
            $properties['liquid'] = self::region3($pressure, $temperature);
            $properties['liquid']->quality = 0;
            $properties['liquid']->region = 3;
        }
        $properties['temperature'] = $temperature;
        $properties['pressure'] = $pressure;
        $properties['gas']->temperature = $temperature;
        $properties['gas']->pressure = $pressure;
        $properties['liquid']->temperature = $temperature;
        $properties['liquid']->pressure = $pressure;
        $properties['region'] = $properties['liquid']->region . '&' . $properties['gas']->region;
        return $properties;
    }

    /**
     * Provides saturated liquid and gas properties for a given temperature
     * @param double $temperature K
     * @return array()
     */
    static function saturatedPropertiesByTemperature($temperature)
    {
        return self::saturatedPropertiesByPressure(self::saturatedPressure($temperature));
    }

    /**
     * Returns Steam Properties based on $pressure and $specificEnthalpy
     * @param double $pressure MPa
     * @param double $specificEnthalpy kJ/kg
     * @return Properties
     */
    static function waterPropertiesPH($pressure, $specificEnthalpy)
    {
        if ($pressure < self::PRESSURE_CRIT) {
            $pressureSatProps = self::saturatedPropertiesByPressure($pressure);
            $specificEnthalpyLimit = $pressureSatProps['liquid']->specificEnthalpy;
        }
        if ($pressure > self::PRESSURE_Tp) {
            $boundaryTemperature = Core::boundaryByPressureRegion3to2($pressure);
            $boundaryProps =Core::region2($pressure, $boundaryTemperature);
            $specificEnthalpyLimit = $boundaryProps->specificEnthalpy;
        }
        if ($specificEnthalpy < $specificEnthalpyLimit) {
            if ($pressure > self::PRESSURE_Tp) $region13boundary = self::waterPropertiesPT($pressure, self::TEMPERATURE_Tp);
            if ($pressure <= self::PRESSURE_Tp or $specificEnthalpy < $region13boundary->specificEnthalpy) {
                $temperature = self::backwardPHregion1Exact($pressure, $specificEnthalpy);
                $testProps = Core::region1($pressure, $temperature);
                $testProps->region = '1';
            } else {
                $temperature = self::backwardPHregion3($pressure, $specificEnthalpy);
                $testProps = self::region3($pressure, $temperature);
                $testProps->region = 3;
            }
            return $testProps;
        }

        if ($pressure < self::PRESSURE_CRIT and $specificEnthalpy >= $pressureSatProps['liquid']->specificEnthalpy and $specificEnthalpy <= $pressureSatProps['gas']->specificEnthalpy) {
            $quality = ($specificEnthalpy - $pressureSatProps['liquid']->specificEnthalpy)
                / ($pressureSatProps['gas']->specificEnthalpy - $pressureSatProps['liquid']->specificEnthalpy);
            $testProps = new Properties();
            $testProps->saturatedGas =  $pressureSatProps['gas'];
            $testProps->saturatedLiquid =$pressureSatProps['liquid'];
            $testProps->setQuality($quality);
            return $testProps;
        }

        if ($pressure <= 4) {
            $temperature = self::backwardPHregion2aExact($pressure, $specificEnthalpy);
            $region = '2a';
        } else {
            $constants = array(
                1 => 0.90584278514723E+3,
                2 => -0.67955786399241,
                3 => 0.12809002730136E-3,
            );
            $pressureLine = $constants[1]
                + $constants[2] * $specificEnthalpy
                + $constants[3] * pow($specificEnthalpy, 2);
            if ($pressureLine > $pressure) {
                $temperature = self::backwardPHregion2bExact($pressure, $specificEnthalpy);
                $region = '2b';
            } else {
                $temperature = self::backwardPHregion2cExact($pressure, $specificEnthalpy);
                $region = '2c';
            }
        }
        $testProps = self::region2($pressure, $temperature);
        $testProps->region = $region;
        return $testProps;

    }

    /**
     * Returns a more accurate Temperature than backwardPHregion1
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K
     */
    static function backwardPHregion1Exact($pressure, $specificEnthalpy)
    {
        return self::backwardExact('region1', 'specificEnthalpy', 'backwardPHregion1', $pressure, $specificEnthalpy);
    }

    /**
     * Returns a more accurate Temperature than backwardPHregion2a
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K
     */
    static function backwardPHregion2aExact($pressure, $specificEnthalpy)
    {
        return self::backwardExact('region2', 'specificEnthalpy', 'backwardPHregion2a', $pressure, $specificEnthalpy);
    }

    /**
     * Returns a more accurate Temperature than backwardPHregion2b
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K
     */
    static function backwardPHregion2bExact($pressure, $specificEnthalpy)
    {
        return self::backwardExact('region2', 'specificEnthalpy', 'backwardPHregion2b', $pressure, $specificEnthalpy);
    }

    /**
     * Returns a more accurate Temperature than backwardPHregion2c
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K
     */
    static function backwardPHregion2cExact($pressure, $specificEnthalpy)
    {
        return self::backwardExact('region2', 'specificEnthalpy', 'backwardPHregion2c', $pressure, $specificEnthalpy);
    }

    /**
     * Uses linear interpolation to goal seek Region3 using pressure and enthalpy
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K
     */
    static function backwardPHregion3($pressure, $specificEnthalpy)
    {
        return self::backwardRegion3Exact($pressure, $specificEnthalpy, 'specificEnthalpy');
    }

    /**
     * Returns Steam Properties based on $pressure and $specificEntropy
     * @param double $pressure MPa
     * @param double $specificEntropy kJ/kg/K
     * @return Properties
     */
    static function waterPropertiesPS($pressure, $specificEntropy)
    {
        if ($pressure < self::PRESSURE_CRIT) {
            $pressureSatProps = self::saturatedPropertiesByPressure($pressure);
            $specificEntropyLimit = $pressureSatProps['liquid']->specificEntropy;
        }

        if ($pressure > self::PRESSURE_Tp) {
            $boundaryTemperature = Core::boundaryByPressureRegion3to2($pressure);
            $boundaryProps = Core::region2($pressure, $boundaryTemperature);
            $region = '2';
            $specificEntropyLimit = $boundaryProps->specificEntropy;
        }
        if ($specificEntropy < $specificEntropyLimit) {
            if ($pressure > self::PRESSURE_Tp) $region13boundary = self::waterPropertiesPT($pressure, self::TEMPERATURE_Tp);
            if ($pressure <= self::PRESSURE_Tp or $specificEntropy < $region13boundary->specificEntropy) {
                $temperature = self::backwardPSregion1Exact($pressure, $specificEntropy);
                $testProps = Core::region1($pressure, $temperature);
                $testProps->region = '1';
            } else {
                $temperature = self::backwardPSregion3($pressure, $specificEntropy);
                $testProps = self::region3($pressure, $temperature);
                $testProps->region = 3;
            }
            return $testProps;
        }

        if ($pressure < self::PRESSURE_CRIT and $specificEntropy >= $pressureSatProps['liquid']->specificEntropy and $specificEntropy <= $pressureSatProps['gas']->specificEntropy) {
            $quality = ($specificEntropy - $pressureSatProps['liquid']->specificEntropy)
                / ($pressureSatProps['gas']->specificEntropy - $pressureSatProps['liquid']->specificEntropy);

            $testProps = new Properties();
            $testProps->saturatedGas =  $pressureSatProps['gas'];
            $testProps->saturatedLiquid =$pressureSatProps['liquid'];
            $testProps->setQuality($quality);
            $testProps->region = 4;
            return $testProps;
        }

        if ($pressure <= 4) {
            $temperature = self::backwardPSregion2aExact($pressure, $specificEntropy);
            $region = '2a';
        } else {
            if ($specificEntropy >= 5.85) {
                $temperature = self::backwardPSregion2bExact($pressure, $specificEntropy);
                $region = '2b';
            } else {
                $temperature = self::backwardPSregion2cExact($pressure, $specificEntropy);
                $region = '2c';
            }
        }
        $testProps = Core::region2($pressure, $temperature);
        $testProps->region = $region;
        return $testProps;
    }

    /**
     * Returns a more accurate Temperature than backwardPSregion1
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K
     */
    static function backwardPSregion1Exact($pressure, $specificEntropy)
    {
        return self::backwardExact('region1', 'specificEntropy', 'backwardPSregion1', $pressure, $specificEntropy);
    }

    /**
     * Returns a more accurate Temperature than backwardPSregion2a
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K
     */
    static function backwardPSregion2aExact($pressure, $specificEntropy)
    {
        return self::backwardExact('region2', 'specificEntropy', 'backwardPSregion2a', $pressure, $specificEntropy);
    }

    /**
     * Returns a more accurate Temperature than backwardPSregion2b
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K
     */
    static function backwardPSregion2bExact($pressure, $specificEntropy)
    {
        return self::backwardExact('region2', 'specificEntropy', 'backwardPSregion2b', $pressure, $specificEntropy);
    }

    /**
     * Returns a more accurate Temperature than backwardPSregion2c
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K
     */
    static function backwardPSregion2cExact($pressure, $specificEntropy)
    {
        return self::backwardExact('region2', 'specificEntropy', 'backwardPSregion2c', $pressure, $specificEntropy);
    }

    /**
     * Uses linear interpolation to goal seek Region3 using pressure and entropy
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg
     * @return float $temperature K
     */
    static function backwardPSregion3($pressure, $specificEntropy)
    {
        return self::backwardRegion3Exact($pressure, $specificEntropy, 'specificEntropy');
    }

    /**
     * Uses linear extrapolation for estimate equation to determine much more accurate $temperature
     * @param string $region ['region1','region2']
     * @param string $backwardUnitType ['specificEthalpy' or 'specificEntropy']
     * @param string $backwardRegionFunction ['region1','region2a', etc]
     * @param float $pressure MPa
     * @param float $var2 [specificEthalpy or specificEntropy]
     * @return float Temperature K
     */
    static function backwardExact($region, $backwardUnitType, $backwardRegionFunction, $pressure, $var2)
    {
        $pointA = self::generatePoint($region, $backwardUnitType, $pressure, self::$backwardRegionFunction($pressure, $var2));
        $pointB = self::generatePoint($region, $backwardUnitType, $pressure, self::$backwardRegionFunction($pressure, $pointA[0]));
        $temperature = self::linearTestPoint($var2, $pointA, $pointB);

        $pointA = self::generatePoint($region, $backwardUnitType, $pressure, $temperature);
        $temperature = self::linearTestPoint($var2, $pointA, $pointB);
        return $temperature;
    }

    /**
     * Specifically for Region3. Uses linear interpolation for estimate equation to determine much more accurate $temperature
     * @param float $pressure MPa
     * @param double $unit [specificEthalpy or specificEntropy]
     * @param string $unitType ['specificEthalpy' or 'specificEntropy']
     * @return double
     */
    static function backwardRegion3Exact($pressure, $unit, $unitType)
    {
        $temperature = self::TEMPERATURE_Tp;
        $pointA = self::generatePoint('region1', $unitType, $pressure, $temperature);
        $pointB = self::generatePoint('region2', $unitType, $pressure, Core::boundaryByPressureRegion3to2($pressure));
        $temperatureB = self::linearTestPoint($unit, $pointA, $pointB);
        $counter = 0;
        while (abs($temperature - $temperatureB) > 1e-6 and $counter++ < 15) {
            $pointA = $pointB;
            $pointB = self::generatePoint('region3', $unitType, $pressure, $temperatureB);
            $temperature = $temperatureB;
            $temperatureB = self::linearTestPoint($unit, $pointA, $pointB);
        }
        return $temperatureB;
    }

    /**
     * Generates a Data Point for a given function
     * @param string $function
     * @param string $key
     * @param float $var1
     * @param float $var2
     * @return array()
     */
    static function generatePoint($function, $key, $var1, $var2)
    {
        $result = self::$function($var1, $var2);
        $point = array($result->$key, $var2);
        return $point;
    }

    /**
     * Uses linear extrapolation to determine location of $X relative to both points
     * @param float $X
     * @param array() $point1
     * @param array() $point2
     * @return float Y
     */
    static function linearTestPoint($X, $point1, $point2)
    {
        $slope = 0;
        if ($point1[0] - $point2[0] <> 0) $slope = ($point1[1] - $point2[1]) / ($point1[0] - $point2[0]);
        $yIntercept = $point1[1] - $slope * $point1[0];
        return $X * $slope + $yIntercept;
    }

    /**
     * Returns the minimum and maximum acceptable values for Temperature based on a given pressure
     * @param double $pressure
     * @return array ('min', 'max') K
     */
    static function rangeTemperatureByPressure($pressure)
    {
        return array('min' => self::TEMPERATURE_MIN, 'max' => self::TEMPERATURE_MAX);
    }

    /**
     * Returns the minimum and maximum acceptable values for Entropy based on a given pressure
     * @param double $pressure MPa
     * @return array ('min', 'max') kJ/kg/K
     */
    static function rangeSpecificEntropyByPressure($pressure)
    {
        return self::rangeByPressure($pressure, 'specificEntropy');
    }

    /**
     * Returns the minimum and maximum acceptable values for Enthalpy based on a given pressure
     * @param double $pressure MPa
     * @return array ('min', 'max') kJ/kg
     */
    static function rangeSpecificEnthalpyByPressure($pressure)
    {
        return self::rangeByPressure($pressure, 'specificEnthalpy');
    }

    /**
     * Returns the minimum and maximum acceptable values for "$type" based on a given pressure
     * @param double $pressure MPa
     * @return array ('min', 'max')
     */
    static function rangeByPressure($pressure, $type)
    {
        $min = self::waterPropertiesPT($pressure, self::TEMPERATURE_MIN);
        $max = self::waterPropertiesPT($pressure, self::TEMPERATURE_MAX);
        $result = array(
            'min' => $min->$type,
            'max' => $max->$type,
        );
        return $result;
    }
}

?>
