<?php

namespace FourLabs\GampBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class AnalyticsFactory
{
    public function createAnalytics(RequestStack $requestStack, $version, $trackingId, $ssl, $anonymize, $async)
    {
        $analytics = new Analytics($ssl);
        
        $analytics
            ->setProtocolVersion($version)
            ->setTrackingId($trackingId)
        ;

        if(!is_null($request = $requestStack->getCurrentRequest())) {
            $analytics
                ->setIpOverride($request->getClientIp())
                ->setUserAgentOverride($request->headers->get('User-Agent'))
                ->setClientId($request->cookies->get('_ga', null))
            ;
        }

        return $analytics;
    }


}
