<?php

namespace FourLabs\GampBundle\Service;

use Symfony\Component\HttpFoundation\Request;
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

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return $analytics;
        }

        $this->handleClientIp($analytics, $request);
        $this->handleUserAgent($analytics, $request);
        $this->handleClientId($analytics, $request);

        return $analytics;
    }

    /**
     * Parse the GA Cookie and return data as an array.
     * Example of GA cookie: _ga:GA1.2.492973748.1449824416.
     *
     * @param string $cookie
     *
     * @return string[] (version, domainDepth, cid)
     */
    public function parseCookie($cookie)
    {
        $parsedCookie = explode('.', substr($cookie, 2), 3);
        $missingElements = 3 - count($parsedCookie);

        if ($missingElements <= 0) {
            return $parsedCookie;
        }

        return array_merge(
            $parsedCookie,
            array_fill(0, $missingElements, '')
        );
    }

    /**
     * @param Analytics $analytics
     * @param Request   $request
     */
    private function handleClientIp(Analytics $analytics, Request $request)
    {
        $clientIp = $request->getClientIp();

        if (null === $clientIp) {
            return;
        }

        $analytics->setIpOverride($clientIp);
    }

    /**
     * @param Analytics $analytics
     * @param Request   $request
     */
    private function handleUserAgent(Analytics $analytics, Request $request)
    {
        $userAgents = array_filter(
            (array) $request->headers->get('User-Agent', null, false),
            static function ($value) {
                /** @var null|string $value */
                if (null === $value) {
                    return false;
                }

                if ('' === trim($value)) {
                    return false;
                }

                return true;
            }
        );

        if (empty($userAgents)) {
            return;
        }

        $analytics->setUserAgentOverride(implode('; ', $userAgents));
    }

    /**
     * @param Analytics $analytics
     * @param Request   $request
     */
    private function handleClientId(Analytics $analytics, Request $request)
    {
        // Set clientId from "_ga" cookie if exists,
        // otherwise this must be set at a later point
        $ga = $request->cookies->get('_ga');

        if (null === $ga) {
            return;
        }

        list($version, $domainDepth, $clientId) = $this->parseCookie($ga);

        if ('' === $clientId) {
            return;
        }

        $analytics->setClientId($clientId);
    }
}
