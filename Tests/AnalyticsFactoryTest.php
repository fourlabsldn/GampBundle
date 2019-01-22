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

        self::assertSame('1', $version);
        self::assertSame('2', $domainDepth);
        self::assertSame('1792370315.1501194811', $cid);
    }

    public function testParseCookieWithEmptyString()
    {
        list($version, $domainDepth, $cid) = $this->factory->parseCookie('');

        self::assertSame('', $version);
        self::assertSame('', $domainDepth);
        self::assertSame('', $cid);
    }

    public function testGaCookie()
    {
        $this->requestStack->push(Request::create('', 'GET', [], ['_ga' => 'GA1.2.1792370315.1501194811']));

        $analytics = $this->callFactory();

        self::assertInstanceOf(Analytics::class, $analytics);
        self::assertSame('1792370315.1501194811', $analytics->getClientId());
    }

    public function testGaCookieWithEmptyString()
    {
        $this->requestStack->push(Request::create('', 'GET', [], ['_ga' => '']));

        $analytics = $this->callFactory();

        self::assertInstanceOf(Analytics::class, $analytics);
        self::assertSame(null, $analytics->getClientId());
    }

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param null|string       $expectedUserAgentString
     * @param null|string|array $userAgent
     */
    public function testUserAgentOverride($expectedUserAgentString, $userAgent)
    {
        $request = Request::create('');
        $request->headers->set('User-Agent', $userAgent);
        $this->requestStack->push($request);

        $analytics = $this->callFactory();

        self::assertInstanceOf(Analytics::class, $analytics);
        self::assertSame($expectedUserAgentString, $analytics->getUserAgentOverride());
    }

    /**
     * @return array[]
     */
    public function userAgentDataProvider()
    {
        return [
            'with string' => [
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            ],
            'with empty string' => [
                null,
                '', // ignored
            ],
            'with trimmed empty string' => [
                null,
                '  ', // ignored
            ],
            'with null' => [
                null,
                null, // ignored
            ],
            'with empty array' => [
                null,
                [], // ignored
            ],
            'with array not empty but with empty values' => [
                null,
                [
                    '', // ignored
                    '   ', // ignored
                    null, // ignored
                ],
            ],
            'with string 0' => [
                '0',
                '0',
            ],
            'with array contains 2 good elements' => [
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html); second element of array',
                [
                    'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                    'second element of array',
                ],
            ],
            'with array contains 2 good elements and same empty values' => [
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html); second element of array; 0',
                [
                    'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                    '', // ignored
                    'second element of array',
                    null, // ignored
                    '0',
                    ' ', // ignored
                ],
            ],
        ];
    }
}
