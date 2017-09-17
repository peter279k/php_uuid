<?php
/**
 * Created by PhpStorm.
 * User: penguin
 * Date: 17.09.17
 * Time: 17:08
 */

namespace UUID\tests;

use UUID\UUID;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

class UUIDGetTimeTest extends TestCase
{
    use PHPMock;

    const OFFSET = 0x01b21dd213814000;

    /**
     * @var \ReflectionMethod $getTimeReflection
     */
    protected $getTimeReflection = null;

    /**
     * @var UUID $uuidObject
     */
    protected $uuidObject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->uuidObject = new UUID();
        $this->getTimeReflection = new \ReflectionMethod($this->uuidObject, 'getTime');
        $this->getTimeReflection->setAccessible(true);
    }

    protected function setActualTime(...$time)
    {
        $time = array_map(__NAMESPACE__ . '\convertNSecToMicrotimeTimestamp', $time);
        $microtimeMock = $this->getFunctionMock('UUID', 'microtime');
        $microtimeMock->expects($this->exactly(count($time)))
            ->willReturn(...$time);
    }

    public function testGetTime()
    {
        $times = [
            0 => 0,
            1000 => 10,
            10000 => 100,
        ];
        $timeSequence = array_keys($times);
        $this->setActualTime(...$timeSequence);
        foreach ($times as $time => $expected) {
            $this->assertEquals($expected, $this->getTimeReflection->invoke($this->uuidObject));
        }
    }

    public function testGetTimeCollisions()
    {
        $times = [
            0 => 0,
            10 => 1,
            20 => 2,
            30 => 3,
            40 => 4,
        ];
        $this->setActualTime(...(array_keys($times)));
        foreach ($times as $time => $expected) {
            $this->assertEquals($expected, $this->getTimeReflection->invoke($this->uuidObject));
        }
    }

    public function testGetTimeOffset()
    {
        $this->setActualTime(0);
        $this->assertEquals(self::OFFSET, $this->getTimeReflection->invoke($this->uuidObject, self::OFFSET));
    }

    public function testGetTimeNotRepeat()
    {
        $this->setActualTime(0, 0);
        $timeOne = $this->getTimeReflection->invoke($this->uuidObject);
        $timeTwo = $this->getTimeReflection->invoke($this->uuidObject);
        $this->assertNotEquals($timeOne, $timeTwo);
    }

    public function testGetTimeDoNotReturningToPast()
    {
        $times = [
            10 * pow(10, 9),
            7 * pow(10, 9),
            5 * pow(10, 9),
            1 * pow(10, 9),
        ];
        $this->setActualTime(...$times);
        $result = $this->getTimeReflection->invoke($this->uuidObject);
        for ($i = 1; $i < count($times); $i++) {
            $nextResult = $this->getTimeReflection->invoke($this->uuidObject);
            $this->assertTrue($nextResult > $result);
            $result = $nextResult;
        }
    }
}
