<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Steam\IF97;

class IF97Test extends TestCase
{

    public function testRegion1()
    {
        $properties = IF97::region1(3, 300);
        $this->assertEquals(0.115331273 * pow(10, 3), $properties->specificEnthalpy, 'region 1', pow(10, -6));
        $this->assertEquals(0.392294792, $properties->specificEntropy, 'region 1', pow(10, -9));

        $properties = IF97::region1(80, 300);
        $this->assertEquals(0.184142828 * pow(10, 3), $properties->specificEnthalpy, 'region 1', pow(10, -6));
        $this->assertEquals(0.368563852, $properties->specificEntropy, 'region 1', pow(10, -9));

        $properties = IF97::region1(3, 500);
        $this->assertEquals(0.975542239 * pow(10, 3), $properties->specificEnthalpy, 'region 1', pow(10, -6));
        $this->assertEquals(0.258041912 * pow(10, 1), $properties->specificEntropy, 'region 1', pow(10, -9));
    }

    public function test_backwardPHregion1()
    {
        $temperature = IF97::backwardPHregion1(3, 500);
        $this->assertEquals(0.391798509 * pow(10, 3), $temperature, 'backwardPHregion1', pow(10, -6));
        $temperature = IF97::backwardPHregion1(80, 500);
        $this->assertEquals(0.378108626 * pow(10, 3), $temperature, 'backwardPHregion1', pow(10, -6));
        $temperature = IF97::backwardPHregion1(80, 1500);
        $this->assertEquals(0.611041229 * pow(10, 3), $temperature, 'backwardPHregion1', pow(10, -6));
    }

    public function test_backwardPSregion1()
    {
        $temperature = IF97::backwardPSregion1(3, 0.5);
        $this->assertEquals(0.307842258 * pow(10, 3), $temperature, 'backwardPSregion1', pow(10, -6));
        $temperature = IF97::backwardPSregion1(80, 0.5);
        $this->assertEquals(0.309979785 * pow(10, 3), $temperature, 'backwardPSregion1', pow(10, -6));
        $temperature = IF97::backwardPSregion1(80, 3);
        $this->assertEquals(0.565899909 * pow(10, 3), $temperature, 'backwardPSregion1', pow(10, -6));
    }


}