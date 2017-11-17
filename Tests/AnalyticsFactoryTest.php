<?php

namespace FourLabs\GampBundle\Tests;

use FourLabs\GampBundle\Service\AnalyticsFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

final class AnalyticsFactoryTest extends TestCase
{
    /**
     * @var AnalyticsFactory
     */
    protected $factory;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    protected function setUp()
    {
        $this->requestStack = new RequestStack();
        $this->factory = new AnalyticsFactory($this->requestStack, 1, 'UA-XXXXXXXX-X', true, false, true, true, false);
    }

    /**
     * @return Analytics
     */
    private function callFactory()
    {
        return $this->factory->createAnalytics();
    }

    public function testNoRequest()
    {
        $analytics = $this->callFactory();

        self::assertInstanceOf(Analytics::class, $analytics);
    }

    public function testEmptyRequest()
    {
        $this->requestStack->push(Request::create(''));

        $analytics = $this->callFactory();

        self::assertInstanceOf(Analytics::class, $analytics);
    }

    public function testParseCookie()
    {
        list($version, $domainDepth, $cid) = $this->factory->parseCookie('GA1.2.1792370315.1501194811');

        self::assertEquals('1', $version);
        self::assertEquals('2', $domainDepth);
        self::assertEquals('1792370315.1501194811', $cid);
    }

    public function testGaCookie()
    {
        $this->requestStack->push(Request::create('', 'GET', [], ['_ga' => 'GA1.2.1792370315.1501194811']));

        $analytics = $this->callFactory();

        self::assertInstanceOf(Analytics::class, $analytics);
        self::assertEquals('1792370315.1501194811', $analytics->getClientId());
    }

    public function testUserAgentOverride()
    {
        $userAgentString = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

        $request = Request::create('');
        $request->headers->set('User-Agent', $userAgentString);
        $this->requestStack->push($request);

        $analytics = $this->callFactory();

        self::assertInstanceOf(Analytics::class, $analytics);
        self::assertEquals($userAgentString, $analytics->getUserAgentOverride());
    }
}
