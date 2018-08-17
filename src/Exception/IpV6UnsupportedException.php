<?php
declare(strict_types=1);

namespace NetglueGeoIP\Exception;

class IpV6UnsupportedException extends RuntimeException
{
    public static function withIp(string $ip) : self
    {
        return new static(sprintf(
            'You are trying to get data for an IPv6 address on an IPv4 only database: %s',
            $ip
        ));
    }
}
