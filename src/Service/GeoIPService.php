<?php
declare(strict_types=1);

namespace NetglueGeoIP\Service;

use NetglueGeoIP\Exception;
use MaxMind\Db\Reader;
use Throwable;
use function filter_var;
use function end;
use function count;
use function is_string;

class GeoIPService
{
    /** @var Reader */
    private $reader;

    /** @var array */
    private $locales = [];

    /**
     * A list of cached items indexed by ip
     * @var array
     */
    private $cache = [];

    public function __construct(
        Reader $reader,
        array $locales = ['en']
    ) {
        $this->reader = $reader;
        $this->setLocales($locales);
    }

    public function get(string $ip) : array
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw Exception\InvalidIpAddressException::withIp($ip);
        }
        $ipVersion = $this->reader->metadata()->__get('ipVersion');
        if ($ipVersion === 4 && strrpos($ip, ':')) {
            throw Exception\IpV6UnsupportedException::withIp($ip);
        }

        if (isset($this->cache[$ip])) {
            return $this->cache[$ip];
        }

        try {
            $data = $this->reader->get($ip);
            $this->cache[$ip] = $data ? $data : [];
        } catch (Throwable $exception) {
            throw new Exception\RuntimeException('Cannot retrieve data for the given IP', 500, $exception);
        }

        return $this->cache[$ip];
    }

    public function continent(string $ip) : array
    {
        $data = $this->get($ip);
        return isset($data['continent'])
            ? $data['continent']
            : [];
    }

    public function country(string $ip) : array
    {
        $data = $this->get($ip);
        return isset($data['country'])
            ? $data['country']
            : [];
    }

    public function city(string $ip) : array
    {
        $data = $this->get($ip);
        return isset($data['city'])
            ? $data['city']
            : [];
    }

    public function location(string $ip) : array
    {
        $data = $this->get($ip);
        return isset($data['location'])
            ? $data['location']
            : [];
    }

    public function subdivisions(string $ip) : array
    {
        $data = $this->get($ip);
        return isset($data['subdivisions'])
            ? $data['subdivisions']
            : [];
    }

    public function region(string $ip) : array
    {
        $data = $this->subdivisions($ip);
        if (count($data)) {
            return end($data);
        }
        return [];
    }

    public function continentCode(string $ip) :? string
    {
        $continent = $this->continent($ip);
        return isset($continent['code'])
            ? $continent['code']
            : null;
    }

    public function countryCode(string $ip) :? string
    {
        $country = $this->country($ip);
        return isset($country['iso_code'])
            ? $country['iso_code']
            : null;
    }

    public function regionCode(string $ip) :? string
    {
        $subdivision = $this->region($ip);
        return isset($subdivision['iso_code'])
            ? $subdivision['iso_code']
            : null;
    }

    public function continentName(string $ip, ?string $locale = null) :? string
    {
        $continent = $this->continent($ip);
        return isset($continent['names'])
            ? $this->localeName($continent['names'], $locale)
            : null;
    }

    public function countryName(string $ip, ?string $locale = null) :? string
    {
        $country = $this->country($ip);
        return isset($country['names'])
            ? $this->localeName($country['names'], $locale)
            : null;
    }

    public function cityName(string $ip, ?string $locale = null) :? string
    {
        $city = $this->city($ip);
        return isset($city['names'])
            ? $this->localeName($city['names'], $locale)
            : null;
    }

    public function regionName(string $ip, ?string $locale = null) :? string
    {
        $region = $this->region($ip);
        return isset($region['names'])
            ? $this->localeName($region['names'], $locale)
            : null;
    }

    public function timezone(string $ip) :? string
    {
        $data = $this->location($ip);
        return isset($data['time_zone'])
            ? $data['time_zone']
            : null;
    }

    public function latitude(string $ip) :? float
    {
        $data = $this->location($ip);
        return isset($data['latitude'])
            ? $data['latitude']
            : null;
    }

    public function longitude(string $ip) :? float
    {
        $data = $this->location($ip);
        return isset($data['longitude'])
            ? $data['longitude']
            : null;
    }

    private function localeName(array &$names, ?string $locale) :? string
    {
        if ($locale) {
            return isset($names[$locale])
                ? $names[$locale]
                : null;
        }
        foreach ($this->locales as $locale) {
            if (isset($names[$locale])) {
                return $names[$locale];
            }
        }
        return null;
    }

    private function setLocales(array $locales) : void
    {
        $msg = 'The locales argument must a non-empty array of strings';
        if (! count($locales)) {
            throw new Exception\InvalidArgumentException($msg);
        }
        foreach ($locales as $locale) {
            if (! is_string($locale)) {
                throw new Exception\InvalidArgumentException($msg);
            }
            $this->locales[] = $locale;
        }
    }
}
