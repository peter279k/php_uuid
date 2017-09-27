<?php

namespace UUID\tests;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use UUID\UUID;

class UUIDv1Test extends TestCase
{
    use PHPMock;

    protected function setActualTime(...$time)
    {
        $time = array_map(__NAMESPACE__ . '\convertNSecToMicrotimeTimestamp', $time);
        $microtimeMock = $this->getFunctionMock('UUID', 'microtime');
        $microtimeMock->expects($this->exactly(count($time)))
            ->willReturn(...$time);
    }

    /**
     * time = 0x01b21dd213814000
     * clock_seq = time & 0x3fff = 0x0000
     * mac = 0xffffffffff
     * time_low = time & 0xffffffff = 0x13814000
     * time_mid = (time >> 32) & 0xffff = 0x1dd2
     * time_hi_version = (time >> 48) & 0xfff = 0x1b2
     * clock_seq_low = clock_seq & 0xff = 0x00
     * clock_seq_hi_variant = (clock_seq >> 8) & 0x3f = 0x00
     *
     * upper = (time_low << 32) | (time_mid << 16) | time_hi_version = 0x138140001dd201b2
     * upper &= ~0x7000 = 0x138140001dd201b2
     * upper |= 1 << 12 = 0xx138140001dd211b2
     *
     * lower = ((clock_seq_hi_variant << 8) | clock_seq_low) << 48 = 0x0000000000000000
     * lower |= mac = 0x0000ffffffffffff
     * lower &= ~(0xc000 << 48) = 0x0000ffffffffffff
     * lower |= (0x8000 << 48) = 0x8000ffffffffffff
     *
     * uuid = 13814000-1dd2-11b2-8000-ffffffffffff
     */
    public function testUUIDv1atZeroTime()
    {
        $this->setActualTime(0);
        $uuid = new UUID();
        $this->assertEquals('13814000-1dd2-11b2-8000-ffffffffffff', $uuid->v1());
    }
}
