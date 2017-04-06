<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Steam\Core;

class IF97Test extends TestCase
{

    public function test_BoundaryByTemperatureRegion3to2()
    {
        $pressure = Core::boundaryByTemperatureRegion3to2(0.623150e3);
        $this->assertEquals(0.165291643e2, $pressure, 'Pressure', 1e-7);
    }

    public function test_BoundaryByPressureRegion3to2()
    {
        $temperature = Core::boundaryByPressureRegion3to2(0.165291643e2);
        $this->assertEquals(0.623150e3, $temperature, 'Temperature', 1e-6);
    }

    public function test_Region1()
    {
        $properties = Core::region1(3, 300);
        $this->assertEquals(0.100215168 * pow(10, -2), $properties->specificVolume, 'region 1', pow(10, -7));
        $this->assertEquals(0.115331273 * pow(10, 3), $properties->specificEnthalpy, 'region 1', pow(10, -6));
        $this->assertEquals(0.392294792, $properties->specificEntropy, 'region 1', pow(10, -9));

        $properties = Core::region1(80, 300);
        $this->assertEquals(0.971180894 * pow(10, -3), $properties->specificVolume, 'region 1', pow(10, -6));
        $this->assertEquals(0.184142828 * pow(10, 3), $properties->specificEnthalpy, 'region 1', pow(10, -6));
        $this->assertEquals(0.368563852, $properties->specificEntropy, 'region 1', pow(10, -9));

        $properties = Core::region1(3, 500);
        $this->assertEquals(0.120241800 * pow(10, -2), $properties->specificVolume, 'region 1', pow(10, -7));
        $this->assertEquals(0.975542239 * pow(10, 3), $properties->specificEnthalpy, 'region 1', pow(10, -6));
        $this->assertEquals(0.258041912 * pow(10, 1), $properties->specificEntropy, 'region 1', pow(10, -9));
    }

    public function test_backwardPHregion1()
    {
        $temperature = Core::backwardPHregion1(3, 500);
        $this->assertEquals(0.391798509 * pow(10, 3), $temperature, 'backwardPHregion1', pow(10, -6));
        $temperature = Core::backwardPHregion1(80, 500);
        $this->assertEquals(0.378108626 * pow(10, 3), $temperature, 'backwardPHregion1', pow(10, -6));
        $temperature = Core::backwardPHregion1(80, 1500);
        $this->assertEquals(0.611041229 * pow(10, 3), $temperature, 'backwardPHregion1', pow(10, -6));
    }

    public function test_backwardPSregion1()
    {
        $temperature = Core::backwardPSregion1(3, 0.5);
        $this->assertEquals(0.307842258 * pow(10, 3), $temperature, 'backwardPSregion1', pow(10, -6));
        $temperature = Core::backwardPSregion1(80, 0.5);
        $this->assertEquals(0.309979785 * pow(10, 3), $temperature, 'backwardPSregion1', pow(10, -6));
        $temperature = Core::backwardPSregion1(80, 3);
        $this->assertEquals(0.565899909 * pow(10, 3), $temperature, 'backwardPSregion1', pow(10, -6));
    }

    public function test_Region2()
    {
        $properties = Core::region2(0.0035, 300);
        $this->assertEquals(0.394913866 * pow(10, 2), $properties->specificVolume, 'region 2', pow(10, -7));
        $this->assertEquals(0.254991145 * pow(10, 4), $properties->specificEnthalpy, 'region 2', pow(10, -5));
        $this->assertEquals(0.852238967 * pow(10, 1), $properties->specificEntropy, 'region 2', pow(10, -8));

        $properties = Core::region2(0.0035, 700);
        $this->assertEquals(0.923015898 * pow(10, 2), $properties->specificVolume, 'region 2', pow(10, -7));
        $this->assertEquals(0.333568375 * pow(10, 4), $properties->specificEnthalpy, 'region 2', pow(10, -5));
        $this->assertEquals(0.101749996 * pow(10, 2), $properties->specificEntropy, 'region 2', pow(10, -7));

        $properties = Core::region2(30, 700);
        $this->assertEquals(0.542946619 * pow(10, -2), $properties->specificVolume, 'region 2', pow(10, -11));
        $this->assertEquals(0.263149474 * pow(10, 4), $properties->specificEnthalpy, 'region 2', pow(10, -5));
        $this->assertEquals(0.517540298 * pow(10, 1), $properties->specificEntropy, 'region 2', pow(10, -8));
    }

    public function test_backwardPHregion2a()
    {
        $temperature = Core::backwardPHregion2a(0.001, 3000);
        $this->assertEquals(0.534433241 * pow(10, 3), $temperature, 'backwardPHregion2a', pow(10, -6));
        $temperature = Core::backwardPHregion2a(3, 3000);
        $this->assertEquals(0.575373370 * pow(10, 3), $temperature, 'backwardPHregion2a', pow(10, -6));
        $temperature = Core::backwardPHregion2a(3, 4000);
        $this->assertEquals(0.101077577 * pow(10, 4), $temperature, 'backwardPHregion2a', pow(10, -5));
    }

    public function test_backwardPHregion2b()
    {
        $temperature = Core::backwardPHregion2b(5, 3500);
        $this->assertEquals(0.801299102 * pow(10, 3), $temperature, 'backwardPHregion2b', pow(10, -6));
        $temperature = Core::backwardPHregion2b(5, 4000);
        $this->assertEquals(0.101531583 * pow(10, 4), $temperature, 'backwardPHregion2b', pow(10, -5));
        $temperature = Core::backwardPHregion2b(25, 3500);
        $this->assertEquals(0.875279054 * pow(10, 3), $temperature, 'backwardPHregion2b', pow(10, -6));
    }

    public function test_backwardPHregion2c()
    {
        $temperature = Core::backwardPHregion2c(40, 2700);
        $this->assertEquals(0.743056411 * pow(10, 3), $temperature, 'backwardPHregion2c', pow(10, -6));
        $temperature = Core::backwardPHregion2c(60, 2700);
        $this->assertEquals(0.791137067 * pow(10, 3), $temperature, 'backwardPHregion2c', pow(10, -6));
        $temperature = Core::backwardPHregion2c(60, 3200);
        $this->assertEquals(0.882756860 * pow(10, 3), $temperature, 'backwardPHregion2c', pow(10, -6));
    }


    public function test_backwardPSregion2a()
    {
        $temperature = Core::backwardPSregion2a(0.1, 7.5);
        $this->assertEquals(0.399517097e3, $temperature, 'backwardPSregion2a', 1e-6);
        $temperature = Core::backwardPSregion2a(0.1, 8);
        $this->assertEquals(0.514127081e3, $temperature, 'backwardPSregion2a', 1e-6);
        $temperature = Core::backwardPSregion2a(2.5, 8);
        $this->assertEquals(0.103984917e4, $temperature, 'backwardPSregion2a', 1e-5);
    }

    public function test_backwardPSregion2b()
    {
        $temperature = Core::backwardPSregion2b(8, 6);
        $this->assertEquals(0.600484040e3, $temperature, 'backwardPSregion2b', 1e-6);
        $temperature = Core::backwardPSregion2b(8, 7.5);
        $this->assertEquals(0.106495556e4, $temperature, 'backwardPSregion2b', 1e-5);
        $temperature = Core::backwardPSregion2b(90, 6);
        $this->assertEquals(0.103801126e4, $temperature, 'backwardPSregion2b', 1e-5);
    }

    public function test_BackwardPSregion2c()
    {
        $temperature = Core::backwardPSregion2c(20, 5.75);
        $this->assertEquals(0.697992849e3, $temperature, 'backwardPSregion2c', 1e-6);
        $temperature = Core::backwardPSregion2c(80, 5.25);
        $this->assertEquals(0.854011484e3, $temperature, 'backwardPSregion2c', 1e-6);
        $temperature = Core::backwardPSregion2c(80, 5.75);
        $this->assertEquals(0.949017998e3, $temperature, 'backwardPSregion2c', 1e-6);
    }

    public function testRegion3Density()
    {
        $properties = Core::region3Density(500, 650);
        $this->assertEquals(.255837018e2, $properties->pressure, 'pressure', 1e-7);
        $this->assertEquals(.186343019e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-3);
        $this->assertEquals(.405427273e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        $properties = Core::region3Density(200, 650);
        $this->assertEquals(.222930643e2, $properties->pressure, 'pressure', 1e-7);
        $this->assertEquals(.237512401e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-3);
        $this->assertEquals(.485438792e1, $properties->specificEntropy, 'specificEntropy', 1e-8);

        $properties = Core::region3Density(500, 750);
        $this->assertEquals(.783095639e2, $properties->pressure, 'pressure', 1e-7);
        $this->assertEquals(.225868845e4, $properties->specificEnthalpy, 'specificEnthalpy', 1e-3);
        $this->assertEquals(.446971906e1, $properties->specificEntropy, 'specificEntropy', 1e-8);
    }

    public function testRegion4()
    {
        $pressure = Core::region4(300);
        $this->assertEquals(0.353658941e-2, $pressure, 'region4', 1e-11);
        $pressure = Core::region4(500);
        $this->assertEquals(0.263889776e1, $pressure, 'region4', 1e-8);
        $pressure = Core::region4(600);
        $this->assertEquals(0.123443146e2, $pressure, 'region4', 1e-7);
    }

    public function testBackwardRegion4()
    {
        $temperature = Core::backwardRegion4(.1);
        $this->assertEquals(0.372755919e3, $temperature, '', 1e-6);
        $temperature = Core::backwardRegion4(1);
        $this->assertEquals(0.453035632e3, $temperature, '', 1e-6);
        $temperature = Core::backwardRegion4(10);
        $this->assertEquals(0.584149488e3, $temperature, '', 1e-6);
    }

}