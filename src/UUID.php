<?php

namespace UUID;

/**
 * Class UUID
 * @package UUID
 */
class UUID
{
    const GREGORIAN_OFFSET = 0x01b21dd213814000;

    protected $lastTime = null;
    protected $mac = 0xffffffffffff;

    protected function getClock()
    {
        $microtime = microtime();
        list($usec, $sec) = explode(' ', $microtime);
        list($zero, $usec) = explode('.', $usec);
        $usec = str_pad($usec, 9, 0, STR_PAD_RIGHT);
        $usec = substr($usec, 0, 9);
        return $sec*1000000000 + $usec;
    }

    protected function getTime($offset = 0)
    {
        $time = $this->getClock() / 100;

        /*
         * prevent from returning time from past
         */
        if ($time > $this->lastTime or is_null($this->lastTime)) {
            $this->lastTime = $time;
        } else {
            $time = ++$this->lastTime;
        }

        $this->lastTime = $time;

        return $time + $offset;
    }

    public function v1()
    {
        $time = $this->getTime(self::GREGORIAN_OFFSET);
        $clock_seq = $time & 0x3fff;
        $mac = $this->mac;
        $time_low = $time & 0xffffffff;
        $time_mid = ($time >> 32) & 0xffff;
        $time_hi_version = ($time >> 48) & 0xfff;
        $clock_seq_low = $clock_seq & 0xff;
        $clock_seq_hi_variant = ($clock_seq >> 8) & 0x3f;

        $upper = ($time_low << 32) | ($time_mid << 16) | $time_hi_version;
        $upper &= ~0x7000;
        $upper |= 1 << 12;

        $lower = (($clock_seq_hi_variant << 8) | $clock_seq_low) << 48;
        $lower |= $mac;
        $lower &= ~(0xc000 << 48);
        $lower |= (~0x8000 << 48);

        $uuid = strrev(substr(strrev(dechex($upper)), 0, 16)) . strrev(substr(strrev(dechex($lower)), 0, 16));
        $join = '-';
        return join($join, [
            substr($uuid, 0, 8),
            substr($uuid, 8, 4),
            substr($uuid, 12, 4),
            substr($uuid, 16, 4),
            substr($uuid, 20)
        ]);
    }
}
