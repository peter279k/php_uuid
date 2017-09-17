<?php

namespace UUID\tests;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use UUID\UUID;

class UUIDGetClockTest extends TestCase
{
    use PHPMock;

    protected $getClockReflection = null;
    protected $uuidObject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->uuidObject = new UUID();
        $this->getClockReflection = new \ReflectionMethod($this->uuidObject, 'getClock');
        $this->getClockReflection->setAccessible(true);
    }

    protected function setActualTime(...$time)
    {
        $time = array_map(__NAMESPACE__ . '\convertNSecToMicrotimeTimestamp', $time);
        $microtimeMock = $this->getFunctionMock('UUID', 'microtime');
        $microtimeMock->expects($this->exactly(count($time)))
            ->willReturn(...$time);
    }

    public function testGetClock()
    {
        $testData = [
            0 => 0,
            1 => 0,
            10 => 0,
            100 => 0,
            1000 => 1000,
            10000 => 10000,
            1000000000 => 1000000000,
            1000000123 => 1000000000,
            1999999999 => 1999999000,
            9999999999 => 9999999000,
        ];

        $this->setActualTime(...(array_keys($testData)));

        foreach ($testData as $result) {
            $this->assertEquals($result, $this->getClockReflection->invoke($this->uuidObject));
        }
    }
}
