<?php

namespace UUID\tests;

function convertNSecToMicrotimeTimestamp($nsec)
{
    $sec = (int)($nsec / 1000000000);
    $usec = str_pad((int)($nsec % 1000000000), 9, '0', STR_PAD_LEFT);
    return sprintf('0.%s %d', substr($usec, 0, 6), $sec);
}
