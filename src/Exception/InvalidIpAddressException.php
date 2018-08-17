<?php
declare(strict_types=1);

namespace NetglueGeoIP\Exception;

class InvalidIpAddressException extends InvalidArgumentException
{
    public static function withIp(string $ip) : self
    {
        return new static(sprintf(
            'The string "%s" is not a valid IP address',
            $ip
        ));
    }
}
