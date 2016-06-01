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
            ->setAnonymizeIp($anonymize)
            ->setAsyncRequest($async)
        ;

        if(!is_null($request = $requestStack->getCurrentRequest())) {
            $analytics
                ->setIpOverride($request->getClientIp())
                ->setUserAgentOverride($request->headers->get('User-Agent'))
            ;

            // set clientId from ga cookie if exists
            if($request->cookies->has('_ga')){
                $clientId = $this->parseCookie($request->cookies->get('_ga'))['cid'];
                $analytics->setClientId($clientId);
            }

            // Also, you can set clientID later like this:
            // $this->get('gamp.analytics')->setClientId($user->getId());
        }

        return $analytics;
    }

    /**
     * Parse the GA Cookie and return data as an array
     * @param $cookie
     * @return array(version, domainDepth, cid)
     * Example of GA cookie: _ga:GA1.2.492973748.1449824416
     */
    public function parseCookie($cookie = null)
    {
        // If $cookie is null try to use the default _ga cookie
        if(empty($cookie)){
            $cookie = $_COOKIE["_ga"];
        }

        list($version, $domainDepth, $cid1, $cid2) = split('[\.]', $cookie,4);

        return array(
            'version' => $version,
            'domainDepth' => $domainDepth,
            'cid' => $cid1.'.'.$cid2
        );
    }


}
