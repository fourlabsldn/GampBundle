<?php

namespace FourLabs\GampBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class AnalyticsFactory
{
    /**
     * @param RequestStack $requestStack
     * @param int $version
     * @param string $trackingId
     * @param bool $ssl
     * @param bool $anonymize
     * @param bool $async
     * @param bool $debug
     * @return Analytics
     */
    public function createAnalytics(RequestStack $requestStack, $version, $trackingId, $ssl, $anonymize, $async, $debug)
    {
        $analytics = new Analytics($ssl);

        $analytics
            ->setProtocolVersion($version)
            ->setTrackingId($trackingId)
            ->setAnonymizeIp($anonymize)
            ->setAsyncRequest($async && !$debug)
            ->setDebug($debug)
        ;

        if (!is_null($request = $requestStack->getCurrentRequest())) {
            $userAgent = null === $request->headers->get('User-Agent') ? '' : $request->headers->get('User-Agent');
            $analytics
                ->setIpOverride($request->getClientIp())
                ->setUserAgentOverride($userAgent)
            ;

            // set clientId from ga cookie if exists, otherwise this must be set at a later point
            if ($request->cookies->has('_ga')) {
                $clientId = $this->parseCookie($request->cookies->get('_ga'))['cid'];
                $analytics->setClientId($clientId);
            }
        }

        return $analytics;
    }

    /**
     * Parse the GA Cookie and return data as an array.
     *
     * @param $cookie
     *
     * @return array(version, domainDepth, cid)
     *                        Example of GA cookie: _ga:GA1.2.492973748.1449824416
     */
    public function parseCookie($cookie)
    {
        list($version, $domainDepth, $cid1, $cid2) = explode('.', $cookie, 4);

        return array(
            'version' => $version,
            'domainDepth' => $domainDepth,
            'cid' => $cid1.'.'.$cid2,
        );
    }
}
