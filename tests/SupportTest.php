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
    public function testSteamRangeRegion3Focus(){
        $pressureMin = Support::PRESSURE_Tp-1;
        $pressureMax = Support::PRESSURE_CRIT+1;
        $pressureInc = ($pressureMax-$pressureMin)/4;
        $temperatureMin = Support::TEMPERATURE_Tp-1;
        $temperatureMax = Support::TEMPERATURE_CRIT+1;
        $temperatureInc = ($temperatureMax-$temperatureMin)/10;

        for( $pressure = $pressureMin; $pressure <= $pressureMax; $pressure += $pressureInc ){
            for( $temperature = $temperatureMin; $temperature <= $temperatureMax; $temperature += $temperatureInc ){
                $propertiesPT = Support::waterPropertiesPT($pressure, $temperature);
                $propertiesPH = Support::waterPropertiesPH($pressure, $propertiesPT->specificEnthalpy);
                $this->assertEquals( $temperature, $propertiesPH->temperature, "specificEnthalpy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPH->region}]", 1e-6 );
                $propertiesPS = Support::waterPropertiesPS($pressure, $propertiesPT->specificEntropy);
                $this->assertEquals( $temperature, $propertiesPS->temperature, "specificEntropy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPS->region}]", 1e-6 );
            }
        }
    }

    /**
     * Tests $propertiesSatP, $propertiesSatT, waterPropertiesPH and waterPropertiesPS from Min to Tp Pressure
     */
    public function testSaturated(){
        $pressureMin = Support::PRESSURE_MIN;
        $pressureMax = Support::PRESSURE_Tp;
        $pressureInc = ($pressureMax-$pressureMin)/50;

        for( $pressure = $pressureMin; $pressure <= $pressureMax; $pressure += $pressureInc ){

            $propertiesSatP = Support::saturatedPropertiesByPressure($pressure);
            $propertiesSatT = Support::saturatedPropertiesByTemperature($propertiesSatP['temperature']);
            $this->assertEquals( $pressure, $propertiesSatT['pressure'], "[Pressure: {$pressure} | Region: {$propertiesSatP['region']}]", 1e-6 );

            $testSpecificEnthalpy = ($propertiesSatP['liquid']->specificEnthalpy+$propertiesSatP['gas']->specificEnthalpy)/2;
            $testSpecificEntropy = ($propertiesSatP['liquid']->specificEntropy+$propertiesSatP['gas']->specificEntropy)/2;

            $testPropertiesPH = Support::waterPropertiesPH($pressure, $testSpecificEnthalpy);
            $this->assertEquals( $pressure, $testPropertiesPH->pressure, "[Pressure: {$pressure} | Region: {$testPropertiesPH->region}]", 1e-6 );
            $this->assertEquals( $testSpecificEnthalpy, $testPropertiesPH->specificEnthalpy, "[Pressure: {$pressure} | Region: {$testPropertiesPH->region}]", 1e-6 );
            $this->assertEquals( $testSpecificEntropy, $testPropertiesPH->specificEntropy, "[Pressure: {$pressure} | Region: {$testPropertiesPH->region}]", 1e-6 );
            $this->assertEquals( .5, $testPropertiesPH->quality, "[Pressure: {$pressure} | Region: {$testPropertiesPH->region}]", 1e-6 );

            $testPropertiesPS = Support::waterPropertiesPS($pressure, $testSpecificEntropy);
            $this->assertEquals( $pressure, $testPropertiesPS->pressure, "[Pressure: {$pressure} | Region: {$testPropertiesPS->region}]", 1e-6 );
            $this->assertEquals( $testSpecificEnthalpy, $testPropertiesPS->specificEnthalpy, "[Pressure: {$pressure} | Region: {$testPropertiesPS->region}]", 1e-6 );
            $this->assertEquals( $testSpecificEntropy, $testPropertiesPS->specificEntropy, "[Pressure: {$pressure} | Region: {$testPropertiesPS->region}]", 1e-6 );
            $this->assertEquals( .5, $testPropertiesPS->quality, "[Pressure: {$pressure} | Region: {$testPropertiesPS->region}]", 1e-6 );
        }
    }
}