<?php

namespace FourLabs\GampBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class AnalyticsFactory
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var int
     */
    private $version;

    /**
     * @var string
     */
    private $trackingId;

    /**
     * @var bool
     */
    private $ssl;

    /**
     * @var bool
     */
    private $anonymize;

    /**
     * @var bool
     */
    private $async;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var bool
     */
    private $sandbox;

    /**
     * @param RequestStack $requestStack Request stack
     * @param int          $version      GA Version
     * @param string       $trackingId   GA tracking ID
     * @param bool         $ssl          Use ssl
     * @param bool         $anonymize    Anonymize IP
     * @param bool         $async        Async calls
     * @param bool         $debug        Enable debug
     * @param bool         $sandbox      Sandbox
     */
    public function __construct(RequestStack $requestStack, $version, $trackingId, $ssl, $anonymize, $async, $debug, $sandbox)
    {
        $this->requestStack = $requestStack;
        $this->version = $version;
        $this->trackingId = $trackingId;
        $this->ssl = $ssl;
        $this->anonymize = $anonymize;
        $this->async = $async;
        $this->debug = $debug;
        $this->sandbox = $sandbox;
    }

    /**
     * @return Analytics
     */
    public function createAnalytics()
    {
        $analytics = new Analytics($this->ssl, $this->sandbox);

        $analytics
            ->setProtocolVersion($this->version)
            ->setTrackingId($this->trackingId)
            ->setAnonymizeIp($this->anonymize)
            ->setAsyncRequest($this->async && !$this->debug)
            ->setDebug($this->debug);

        if (null !== $request = $this->requestStack->getCurrentRequest()) {
            $analytics
                ->setIpOverride($request->getClientIp())
                ->setUserAgentOverride($request->headers->get('User-Agent', ''));

            // Set clientId from "_ga" cookie if exists,
            // otherwise this must be set at a later point
            if (null !== $ga = $request->cookies->get('_ga')) {
                $cookie = $this->parseCookie($ga);
                $analytics->setClientId(array_pop($cookie));
            }
        }

        return $analytics;
    }

    /**
     * Parse the GA Cookie and return data as an array.
     * Example of GA cookie: _ga:GA1.2.492973748.1449824416.
     *
     * @param $cookie
     *
     * @return array(version, domainDepth, cid)
     */
    public function parseCookie($cookie)
    {
        return explode('.', substr($cookie, 2), 3);
    }
}
