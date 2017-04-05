<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Steam\IF97;

 class IF97Test extends TestCase
{

    public function testRegion1()
    {
        $properties = IF97::region1(3, 300);
        $this->assertEquals(0.115331273*pow(10,3) ,$properties->specificEnthalpy,'region 1',pow(10,-6));

        $properties = IF97::region1(80, 300);
        $this->assertEquals(0.184142828 *pow(10,3) ,$properties->specificEnthalpy,'region 1',pow(10,-6));

        $properties = IF97::region1(3, 500);
        $this->assertEquals(0.975542239*pow(10,3) ,$properties->specificEnthalpy,'region 1',pow(10,-6));
    }

}