<?php

namespace App\Tests\Importer;

use App\Model\Importer\ArrayToObjectMapper;
use PHPUnit\Framework\TestCase;

class ArrayToObjectMapperTest extends TestCase
{
    public function testFillObjectFromArray()
    {
        $mapper = new ArrayToObjectMapper();
        $inputArray = [
            'firstDummyProperty' => 23,
            'SecondDummyPROPERTY' => 'FortyTwo',
        ];
        $objectToFill = new FillMeDummy();
        $mapper->fillObjectFromArray($inputArray, $objectToFill);

        $this->assertEquals(23, $objectToFill->getFirstDummyProperty());
        $this->assertEquals('FortyTwo', $objectToFill->getSecondDummyProperty());
    }
}

class FillMeDummy
{
    private $firstDummyProperty;

    private $secondDummyProperty;

    public function getFirstDummyProperty()
    {
        return $this->firstDummyProperty;
    }

    public function getSecondDummyProperty()
    {
        return $this->secondDummyProperty;
    }

    public function setFirstDummyProperty($firstDummyProperty)
    {
        $this->firstDummyProperty = $firstDummyProperty;
    }

    public function setSecondDummyProperty($secondDummyProperty)
    {
        $this->secondDummyProperty = $secondDummyProperty;
    }
}
