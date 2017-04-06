<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Steam\Support;

class SupportTest extends TestCase
{
    /**
     * Tests waterPropertiesPH and  waterPropertiesPS across the full range of temperatures and pressures
     */
    public function testSteamRange()
    {
        $pressureMin = Support::PRESSURE_MIN;
        $pressureMax = Support::PRESSURE_MAX;
        $pressureInc = ($pressureMax - $pressureMin) / 4;
        $temperatureMin = Support::TEMPERATURE_MIN;
        $temperatureMax = Support::TEMPERATURE_MAX;
        $temperatureInc = ($temperatureMax - $temperatureMin) / 10;

        for ($pressure = $pressureMin; $pressure <= $pressureMax; $pressure += $pressureInc) {
            for ($temperature = $temperatureMin; $temperature <= $temperatureMax; $temperature += $temperatureInc) {
                $propertiesPT = Support::waterPropertiesPT($pressure, $temperature);
                $propertiesPH = Support::waterPropertiesPH($pressure, $propertiesPT->specificEnthalpy);
                $this->assertEquals($temperature, $propertiesPH->temperature, "specificEnthalpy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPH->region}]", 1e-6);
                $propertiesPS = Support::waterPropertiesPS($pressure, $propertiesPT->specificEntropy);
                $this->assertEquals($temperature, $propertiesPS->temperature, "specificEntropy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPS->region}]", 1e-6);
            }
        }
    }

    /**
     * Tests waterPropertiesPH and  waterPropertiesPS focused on the Tp to Crit section of region 3
     */
    public function testSteamRangeRegion3Focus()
    {
        $pressureMin = Support::PRESSURE_Tp - 1;
        $pressureMax = Support::PRESSURE_CRIT + 1;
        $pressureInc = ($pressureMax - $pressureMin) / 4;
        $temperatureMin = Support::TEMPERATURE_Tp - 1;
        $temperatureMax = Support::TEMPERATURE_CRIT + 1;
        $temperatureInc = ($temperatureMax - $temperatureMin) / 10;

        for ($pressure = $pressureMin; $pressure <= $pressureMax; $pressure += $pressureInc) {
            for ($temperature = $temperatureMin; $temperature <= $temperatureMax; $temperature += $temperatureInc) {
                $propertiesPT = Support::waterPropertiesPT($pressure, $temperature);
                $propertiesPH = Support::waterPropertiesPH($pressure, $propertiesPT->specificEnthalpy);
                $this->assertEquals($temperature, $propertiesPH->temperature, "specificEnthalpy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPH->region}]", 1e-6);
                $propertiesPS = Support::waterPropertiesPS($pressure, $propertiesPT->specificEntropy);
                $this->assertEquals($temperature, $propertiesPS->temperature, "specificEntropy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPS->region}]", 1e-6);
            }
        }
    }

    /**
     * Tests $propertiesSatP, $propertiesSatT, waterPropertiesPH and waterPropertiesPS from Min to Tp Pressure
     */
    public function testSaturated()
    {
        $pressureMin = Support::PRESSURE_MIN;
        $pressureMax = Support::PRESSURE_Tp;
        $pressureInc = ($pressureMax - $pressureMin) / 50;

        for ($pressure = $pressureMin; $pressure <= $pressureMax; $pressure += $pressureInc) {

            $propertiesSatP = Support::saturatedPropertiesByPressure($pressure);
            $propertiesSatT = Support::saturatedPropertiesByTemperature($propertiesSatP['temperature']);
            $this->assertEquals($pressure, $propertiesSatT['pressure'], "[Pressure: {$pressure} | Region: {$propertiesSatP['region']}]", 1e-6);

            $testSpecificEnthalpy = ($propertiesSatP['liquid']->specificEnthalpy + $propertiesSatP['gas']->specificEnthalpy) / 2;
            $testSpecificEntropy = ($propertiesSatP['liquid']->specificEntropy + $propertiesSatP['gas']->specificEntropy) / 2;

            $testPropertiesPH = Support::waterPropertiesPH($pressure, $testSpecificEnthalpy);
            $this->assertEquals($pressure, $testPropertiesPH->pressure, "[Pressure: {$pressure} | Region: {$testPropertiesPH->region}]", 1e-6);
            $this->assertEquals($testSpecificEnthalpy, $testPropertiesPH->specificEnthalpy, "[Pressure: {$pressure} | Region: {$testPropertiesPH->region}]", 1e-6);
            $this->assertEquals($testSpecificEntropy, $testPropertiesPH->specificEntropy, "[Pressure: {$pressure} | Region: {$testPropertiesPH->region}]", 1e-6);
            $this->assertEquals(.5, $testPropertiesPH->quality, "[Pressure: {$pressure} | Region: {$testPropertiesPH->region}]", 1e-6);

            $testPropertiesPS = Support::waterPropertiesPS($pressure, $testSpecificEntropy);
            $this->assertEquals($pressure, $testPropertiesPS->pressure, "[Pressure: {$pressure} | Region: {$testPropertiesPS->region}]", 1e-6);
            $this->assertEquals($testSpecificEnthalpy, $testPropertiesPS->specificEnthalpy, "[Pressure: {$pressure} | Region: {$testPropertiesPS->region}]", 1e-6);
            $this->assertEquals($testSpecificEntropy, $testPropertiesPS->specificEntropy, "[Pressure: {$pressure} | Region: {$testPropertiesPS->region}]", 1e-6);
            $this->assertEquals(.5, $testPropertiesPS->quality, "[Pressure: {$pressure} | Region: {$testPropertiesPS->region}]", 1e-6);
        }
    }

    /**
     * @covers Support::waterPropertiesPT()
     */
    public function testWaterPropertiesPT()
    {
        //Region 1
        $properties = Support::waterPropertiesPT(3, 300);
        $this->assertEquals(.100215168e-2, $properties->specificVolume, 'specificVolume', 1e-12);
        $this->assertEquals(.115331273e3, $properties->specificEnthalpy, 'specificEnthalpy', 1e-7);
        $this->assertEquals(.392294792, $properties->specificEntropy, 'specificEntropy', 1e-9);

        $properties = Support::waterPropertiesPT(80, 300);
        $this->assertEquals(.971180894e-3, $properties->specificVolume, 'specificVolume', 1e-13);
        $this->assertEquals(.184142828e3, $properties->specificEnthalpy, 'specificEnthalpy', 1e-6);
        $this->assertEquals(.368563852, $properties->specificEntropy, 'specificEntropy', 1e-9);

        $properties = Support::waterPropertiesPT(3, 500);
        $this->assertEquals(.120241800e-2, $properties->specificVolume, 'specificVolume', 1e-11);
        $this->assertEquals(.975542239e3, $properties->specificEnthalpy, 'specificEnthalpy', 1e-7);
        $this->assertEquals(.258041912e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        //Region 2
        $properties = Support::waterPropertiesPT(.0035, 300);
        $this->assertEquals(.394913866e2, $properties->specificVolume, 'specificVolume', 1e-7);
        $this->assertEquals(.254991145e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-6);
        $this->assertEquals(.852238967e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        $properties = Support::waterPropertiesPT(.0035, 700);
        $this->assertEquals(.923015898e2, $properties->specificVolume, 'specificVolume', 1e-7);
        $this->assertEquals(.333568375e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-5);
        $this->assertEquals(.101749996e2, $properties->specificEntropy, 'specificEntropy', 1e-7);

        $properties = Support::waterPropertiesPT(30, 700);
        $this->assertEquals(.542946619e-2, $properties->specificVolume, 'specificVolume', 1e-11);
        $this->assertEquals(.263149474e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-5);
        $this->assertEquals(.517540298e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        //Region 3
        $properties = Support::waterPropertiesPT(.255837018e2, 650);
        $this->assertEquals(1 / 500, $properties->specificVolume, 'specificVolume', 1e-9);
        $this->assertEquals(.186343019e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-6);
        $this->assertEquals(.405427273e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        $properties = Support::waterPropertiesPT(.222930643e2, 650);
        $this->assertEquals(1 / 200, $properties->specificVolume, 'specificVolume', 1e-9);
        $this->assertEquals(.237512401e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-4);
        $this->assertEquals(.485438792e1, $properties->specificEntropy, 'specificEntropy', 1e-6);

        $properties = Support::waterPropertiesPT(.783095639e2, 750);
        $this->assertEquals(1 / 500, $properties->specificVolume, 'specificVolume', 1e-9);
        $this->assertEquals(.225868845e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-4);
        $this->assertEquals(.446971906e1, $properties->specificEntropy, 'specificEntropy', 1e-6);

    }

    /**
     * @covers Support::regionSelect()
     */
    public function testRegionSelect()
    {
        //Region 1
        $region = Support::waterPropertiesPT(3, 300)->region;
        $this->assertEquals(1, $region, 'Region');
        $region = Support::waterPropertiesPT(80, 300)->region;
        $this->assertEquals(1, $region, 'Region');
        $region = Support::waterPropertiesPT(3, 500)->region;
        $this->assertEquals(1, $region, 'Region');

        //Region 2
        $region = Support::waterPropertiesPT(.0035, 300)->region;
        $this->assertEquals(2, $region, 'Region');
        $region = Support::waterPropertiesPT(.0035, 700)->region;
        $this->assertEquals(2, $region, 'Region');
        $region = Support::waterPropertiesPT(30, 700)->region;
        $this->assertEquals(2, $region, 'Region');

        //Region 3
        $region = Support::waterPropertiesPT(.255837018e2, 650)->region;
        $this->assertEquals(3, $region, 'Region');
        $region = Support::waterPropertiesPT(.222930643e2, 650)->region;
        $this->assertEquals(3, $region, 'Region');
        $region = Support::waterPropertiesPT(.783095639e2, 750)->region;
        $this->assertEquals(3, $region, 'Region');
    }

    public function testRegion3()
    {
        $propertiesFromDensity = Support::region3Density(500, 650);
        $properties = Support::region3($propertiesFromDensity->pressure, $propertiesFromDensity->temperature);
        $this->assertEquals(500, $properties->density, 'Density', 1e-6);

        $propertiesFromDensity = Support::region3Density(200, 650);
        $properties = Support::region3($propertiesFromDensity->pressure, $propertiesFromDensity->temperature);
        $this->assertEquals(200, $properties->density, 'Density', 1e-5);

        $propertiesFromDensity = Support::region3Density(500, 750);
        $properties = Support::region3($propertiesFromDensity->pressure, $propertiesFromDensity->temperature);
        $this->assertEquals(500, $properties->density, 'Density', 1e-6);
    }

    /**
     * @covers Support::saturatedPressure()
     */
    public function testSaturatedPressure()
    {
        $this->assertEquals(0.353658941e-2, Support::region4(300), '', 1e-11);
        $this->assertEquals(0.263889776e1, Support::region4(500), '', 1e-8);
        $this->assertEquals(0.123443146e2, Support::region4(600), '', 1e-7);
    }

    /**
     * @covers Support::saturatedTemperature()
     */
    public function testSaturatedTemperature()
    {
        $this->assertEquals(0.372755919e3, Support::backwardRegion4(.1), '', 1e-6);
        $this->assertEquals(0.453035632e3, Support::backwardRegion4(1), '', 1e-6);
        $this->assertEquals(0.584149488e3, Support::backwardRegion4(10), '', 1e-6);
    }

    /**
     * @covers Support::waterPropertiesPH()
     */
    public function testWaterPropertiesPH()
    {
        //Region 1
        $properties = Support::waterPropertiesPH(3, .115331273e3);
        $this->assertEquals(1, $properties->region, 'Region');
        $this->assertEquals(300, $properties->temperature, 'temperature', 1e-7);
        $this->assertEquals(.100215168e-2, $properties->specificVolume, 'specificVolume', 1e-12);
        $this->assertEquals(.392294792, $properties->specificEntropy, 'specificEntropy', 1e-9);

        $properties = Support::waterPropertiesPH(80, .184142828e3);
        $this->assertEquals(1, $properties->region, 'Region');
        $this->assertEquals(300, $properties->temperature, 'temperature', 1e-7);
        $this->assertEquals(.971180894e-3, $properties->specificVolume, 'specificVolume', 1e-13);
        $this->assertEquals(.368563852, $properties->specificEntropy, 'specificEntropy', 1e-8);

        $properties = Support::waterPropertiesPH(3, .975542239e3);
        $this->assertEquals(1, $properties->region, 'Region');
        $this->assertEquals(500, $properties->temperature, 'temperature', 1e-7);
        $this->assertEquals(.120241800e-2, $properties->specificVolume, 'specificVolume', 1e-11);
        $this->assertEquals(.258041912e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        //Region 2
        $properties = Support::waterPropertiesPH(.0035, .254991145e4);
        $this->assertEquals('2a', $properties->region, 'Region');
        $this->assertEquals(300, $properties->temperature, 'temperature', 1e-6);
        $this->assertEquals(.394913866e2, $properties->specificVolume, 'specificVolume', 1e-7);
        $this->assertEquals(.852238967e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        $properties = Support::waterPropertiesPH(.0035, .333568375e4);
        $this->assertEquals('2a', $properties->region, 'Region');
        $this->assertEquals(700, $properties->temperature, 'temperature', 1e-5);
        $this->assertEquals(.923015898e2, $properties->specificVolume, 'specificVolume', 1e-6);
        $this->assertEquals(.101749996e2, $properties->specificEntropy, 'specificEntropy', 1e-7);

        $properties = Support::waterPropertiesPH(30, .263149474e4);
        $this->assertEquals('2c', $properties->region, 'Region');
        $this->assertEquals(700, $properties->temperature, 'temperature', 1e-6);
        $this->assertEquals(.542946619e-2, $properties->specificVolume, 'specificVolume', 1e-10);
        $this->assertEquals(.517540298e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        //Region 3
        $properties = Support::waterPropertiesPH(.255837018e2, .186343019e4);
        $this->assertEquals(3, $properties->region, 'Region');
        $this->assertEquals(650, $properties->temperature, 'temperature', 1e-7);
        $this->assertEquals(1 / 500, $properties->specificVolume, 'specificVolume', 1e-10);
        $this->assertEquals(.405427273e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        $properties = Support::waterPropertiesPH(.222930643e2, .237512401e4);
        $this->assertEquals(3, $properties->region, 'Region');
        $this->assertEquals(650, $properties->temperature, 'temperature', 1e-6);
        $this->assertEquals(1 / 200, $properties->specificVolume, 'specificVolume', 1e-10);
        $this->assertEquals(.485438792e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        $properties = Support::waterPropertiesPH(.783095639e2, .225868845e4);
        $this->assertEquals(3, $properties->region, 'Region');
        $this->assertEquals(750, $properties->temperature, 'temperature', 1e-6);
        $this->assertEquals(1 / 500, $properties->specificVolume, 'specificVolume', 1e-10);
        $this->assertEquals(.446971906e1, $properties->specificEntropy, 'specificEntropy', 1e-8);
    }

    /**
     * @covers Support::backwardPHregion1Exact()
     */
    public function testBackwardPHregion1Exact()
    {
        $properties = Support::region1(3, 391.798509);
        $this->assertEquals(391.798509, Support::backwardPHregion1Exact($properties->pressure, $properties->specificEnthalpy), 'Temperature', 1e-12);
        $properties = Support::region1(3, 378.108626);
        $this->assertEquals(378.108626, Support::backwardPHregion1Exact($properties->pressure, $properties->specificEnthalpy), 'Temperature', 1e-12);
        $properties = Support::region1(80, 611.041229);
        $this->assertEquals(611.041229, Support::backwardPHregion1Exact($properties->pressure, $properties->specificEnthalpy), 'Temperature', 1e-11);
    }

    /**
     * @covers Support::backwardPHregion3()
     */
    public function testBackwardPHregion3()
    {
        $properties = Support::region3(.255837018e2, 650);
        $this->assertEquals(650, Support::backwardPHregion3($properties->pressure, $properties->specificEnthalpy), 'Temperature', 1e-7);
        $properties = Support::region3(.222930643e2, 650);
        $this->assertEquals(650, Support::backwardPHregion3($properties->pressure, $properties->specificEnthalpy), 'Temperature', 1e-6);
        $properties = Support::region3(.783095639e2, 750);
        $this->assertEquals(750, Support::backwardPHregion3($properties->pressure, $properties->specificEnthalpy), 'Temperature', 1e-7);
    }

    /**
     * @covers Support::waterPropertiesPS()
     */
    public function testWaterPropertiesPS()
    {
        //Region 1
        $properties = Support::waterPropertiesPS(3, .392294792);
        $this->assertEquals(1, $properties->region, 'Region');
        $this->assertEquals(300, $properties->temperature, 'temperature', 1e-7);
        $this->assertEquals(.100215168e-2, $properties->specificVolume, 'specificVolume', 1e-12);
        $this->assertEquals(.115331273e3, $properties->specificEnthalpy, 'specificEnthalpy', 1e-7);

        $properties = Support::waterPropertiesPS(80, .368563852);
        $this->assertEquals(1, $properties->region, 'Region');
        $this->assertEquals(300, $properties->temperature, 'temperature', 1e-7);
        $this->assertEquals(.971180894e-3, $properties->specificVolume, 'specificVolume', 1e-13);
        $this->assertEquals(.184142828e3, $properties->specificEnthalpy, 'specificEnthalpy', 1e-6);

        $properties = Support::waterPropertiesPS(3, .258041912e1);
        $this->assertEquals(1, $properties->region, 'Region');
        $this->assertEquals(500, $properties->temperature, 'temperature', 1e-7);
        $this->assertEquals(.120241800e-2, $properties->specificVolume, 'specificVolume', 1e-11);
        $this->assertEquals(.975542239e3, $properties->specificEnthalpy, 'specificEnthalpy', 1e-6);

        //Region 2
        $properties = Support::waterPropertiesPS(.0035, .852238967e1);
        $this->assertEquals('2a', $properties->region, 'Region');
        $this->assertEquals(300, $properties->temperature, 'temperature', 1e-6);
        $this->assertEquals(.394913866e2, $properties->specificVolume, 'specificVolume', 1e-7);
        $this->assertEquals(.254991145e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-5);

        $properties = Support::waterPropertiesPS(.0035, .101749996e2);
        $this->assertEquals('2a', $properties->region, 'Region');
        $this->assertEquals(700, $properties->temperature, 'temperature', 1e-5);
        $this->assertEquals(.923015898e2, $properties->specificVolume, 'specificVolume', 1e-6);
        $this->assertEquals(.333568375e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-4);

        $properties = Support::waterPropertiesPS(30, .517540298e1);
        $this->assertEquals('2c', $properties->region, 'Region');
        $this->assertEquals(700, $properties->temperature, 'temperature', 1e-6);
        $this->assertEquals(.542946619e-2, $properties->specificVolume, 'specificVolume', 1e-11);
        $this->assertEquals(.263149474e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-5);

        //Region 3
        $properties = Support::waterPropertiesPS(.255837018e2, .405427273e1);
        $this->assertEquals(3, $properties->region, 'Region');
        $this->assertEquals(650, $properties->temperature, 'temperature', 1e-6);
        $this->assertEquals(1 / 500, $properties->specificVolume, 'specificVolume', 1e-10);
        $this->assertEquals(.186343019e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-5);

        $properties = Support::waterPropertiesPS(.222930643e2, .485438792e1);
        $this->assertEquals(3, $properties->region, 'Region');
        $this->assertEquals(650, $properties->temperature, 'temperature', 1e-6);
        $this->assertEquals(1 / 200, $properties->specificVolume, 'specificVolume', 1e-10);
        $this->assertEquals(.237512401e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-5);

        $properties = Support::waterPropertiesPS(.783095639e2, .446971906e1);
        $this->assertEquals(3, $properties->region, 'Region');
        $this->assertEquals(750, $properties->temperature, 'temperature', 1e-6);
        $this->assertEquals(1 / 500, $properties->specificVolume, 'specificVolume', 1e-10);
        $this->assertEquals(.225868845e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-5);
    }

    public function testBackwardPSregion1Exact()
    {
        $properties = Support::region1(3, 391.798509);
        $this->assertEquals(391.798509, Support::backwardPSregion1Exact($properties->pressure, $properties->specificEntropy), 'Temperature', 1e-12);
        $properties = Support::region1(3, 378.108626);
        $this->assertEquals(378.108626, Support::backwardPSregion1Exact($properties->pressure, $properties->specificEntropy), 'Temperature', 1e-12);
        $properties = Support::region1(80, 611.041229);
        $this->assertEquals(611.041229, Support::backwardPSregion1Exact($properties->pressure, $properties->specificEntropy), 'Temperature', 1e-11);
    }

    /**
     * @covers Support::backwardPSregion3()
     */
    public function testBackwardPSregion3()
    {
        $properties = Support::region3(.255837018e2, 650);
        $this->assertEquals(650, Support::backwardPSregion3($properties->pressure, $properties->specificEntropy), "Temperature [Pressure {$properties->pressure} | SpEntropy {$properties->specificEntropy}]", 1e-7);
        $properties = Support::region3(.222930643e2, 650);
        $this->assertEquals(650, Support::backwardPSregion3($properties->pressure, $properties->specificEntropy), "Temperature [Pressure {$properties->pressure} | SpEntropy {$properties->specificEntropy}]", 1e-6);
        $properties = Support::region3(.783095639e2, 750);
        $this->assertEquals(750, Support::backwardPSregion3($properties->pressure, $properties->specificEntropy), "Temperature [Pressure {$properties->pressure} | SpEntropy {$properties->specificEntropy}]", 1e-7);
    }

    /**
     * @covers Support::rangeTemperatureByPressure()
     */
    public function testRangeTemperatureByPressure()
    {
        $range = Support::rangeTemperatureByPressure(.1);
        $this->assertEquals(Support::TEMPERATURE_MIN, $range['min'], 'Temperature Min', 1e-12);
        $this->assertEquals(Support::TEMPERATURE_MAX, $range['max'], 'Temperature Max', 1e-12);

        $range = Support::rangeTemperatureByPressure(3);
        $this->assertEquals(Support::TEMPERATURE_MIN, $range['min'], 'Temperature Min', 1e-12);
        $this->assertEquals(Support::TEMPERATURE_MAX, $range['max'], 'Temperature Max', 1e-12);

        $range = Support::rangeTemperatureByPressure(15);
        $this->assertEquals(Support::TEMPERATURE_MIN, $range['min'], 'Temperature Min', 1e-12);
        $this->assertEquals(Support::TEMPERATURE_MAX, $range['max'], 'Temperature Max', 1e-12);
    }

    /**
     * @covers Support::rangeSpecificEntropyByPressure()
     */
    public function testRangeSpecificEntropyByPressure()
    {
        $range = Support::rangeSpecificEntropyByPressure(.1);
        $this->assertEquals(0, $range['min'], 'Specific Entropy Min', 1e-2);
        $this->assertEquals(9.57, $range['max'], 'Specific Entropy Max', 1e-2);

        $range = Support::rangeSpecificEntropyByPressure(3);
        $this->assertEquals(0, $range['min'], 'Specific Entropy Min', 1e-2);
        $this->assertEquals(7.99, $range['max'], 'Specific Entropy Max', 1e-2);

        $range = Support::rangeSpecificEntropyByPressure(15);
        $this->assertEquals(0, $range['min'], 'Specific Entropy Min', 1e-2);
        $this->assertEquals(7.20, $range['max'], 'Specific Entropy Max', 1e-2);

        $range = Support::rangeSpecificEntropyByPressure(50);
        $this->assertEquals(0, $range['min'], 'Specific Entropy Min', 1e-2);
        $this->assertEquals(6.52, $range['max'], 'Specific Entropy Max', 1e-2);

        $range = Support::rangeSpecificEntropyByPressure(100);
        $this->assertEquals(0, $range['min'], 'Specific Entropy Min', 1e-2);
        $this->assertEquals(6.04, $range['max'], 'Specific Entropy Max', 1e-2);
    }

    /**
     * @covers Support::rangeSpecificEnthalpyByPressure()
     */
    public function testRangeSpecificEnthalpyByPressure()
    {
        $range = Support::rangeSpecificEnthalpyByPressure(.1);
        $this->assertEquals(0, $range['min'], 'Specific Enthalpy Min', 1e-1);
        $this->assertEquals(4160.21, $range['max'], 'Specific Enthalpy Max', 1e-2);

        $range = Support::rangeSpecificEnthalpyByPressure(3);
        $this->assertEquals(3.00, $range['min'], 'Specific Enthalpy Min', 1e-1);
        $this->assertEquals(4147.03, $range['max'], 'Specific Enthalpy Max', 1e-2);

        $range = Support::rangeSpecificEnthalpyByPressure(15);
        $this->assertEquals(15.07, $range['min'], 'Specific Enthalpy Min', 1e-1);
        $this->assertEquals(4091.33, $range['max'], 'Specific Enthalpy Max', 1e-2);

        $range = Support::rangeSpecificEnthalpyByPressure(50);
        $this->assertEquals(49.13, $range['min'], 'Specific Enthalpy Min', 1e-1);
        $this->assertEquals(3925.96, $range['max'], 'Specific Enthalpy Max', 1e-2);

        $range = Support::rangeSpecificEnthalpyByPressure(100);
        $this->assertEquals(95.39, $range['min'], 'Specific Enthalpy Min', 1e-1);
        $this->assertEquals(3715.19, $range['max'], 'Specific Enthalpy Max', 1e-2);
    }

    /**
     * @covers Support::rangeByPressure()
     */
    public function testRangeByPressure()
    {
        $range = Support::rangeByPressure(3, 'specificEnthalpy');
        $this->assertEquals(3.00, $range['min'], 'Specific Enthalpy Min', 1e-1);
        $this->assertEquals(4147.03, $range['max'], 'Specific Enthalpy Max', 1e-2);

        $range = Support::rangeByPressure(3, 'specificEntropy');
        $this->assertEquals(0, $range['min'], 'Specific Entropy Min', 1e-2);
        $this->assertEquals(7.99, $range['max'], 'Specific Entropy Max', 1e-2);
    }
}