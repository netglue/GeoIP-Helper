<?php
declare(strict_types=1);

namespace NetglueGeoIP\ZendMvc;

use Zend\Http\Header\HeaderInterface;
use Zend\Http\PhpEnvironment\Request as PhpRequest;
use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\RequestInterface;
use function current;
use function explode;
use function strpos;
use function trim;

/**
 * Retrieve Client IP from an HTTP Request in Zend Framework
 *
 * Usage: $clientIp = (new IpFromRequest)($request);
 */
class IpFromRequest
{
    public function __invoke(RequestInterface $request) :? string
    {
        if ($request instanceof HttpRequest) {
            $fwdFor = $this->forwardedFor($request);
            if (! empty($fwdFor)) {
                return $this->clientIpFromForwardForHeader($fwdFor);
            }
        }
        if ($request instanceof PhpRequest) {
            $remoteAddress = $request->getServer('REMOTE_ADDR', null);
            return $remoteAddress ? (string) $remoteAddress : null;
        }
        return null;
    }

    private function forwardedFor(HttpRequest $request) :? string
    {
        $header = $request->getHeader('X-Forwarded-For');
        return $header instanceof HeaderInterface
            ? $header->getFieldValue()
            : null;
    }

    private function clientIpFromForwardForHeader(string $header) : string
    {
        $ip = $header;
        if (strpos($header, ',') !== false) {
            $ip = trim(current(explode(',', $header)));
        }
        return $ip;
    }
}
