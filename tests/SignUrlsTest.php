<?php

namespace Rokka\Client\Tests;

use Rokka\Client\UriHelper;

class SignUrlsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function provideAddOptionsToUri()
    {
        return
            [
                'infinite' => ['', '?sig=c768c2eb3b76aaa9', null],
                'time limited' => [
                    '',
                    '?sigopts=IntcInVudGlsXCI6XCIyMDUwLTAyLTA4VDA4OjA1OjAwKzAwOjAwXCJ9Ig%3D%3D&sig=3c47b0a26da4b044',
                    new \DateTime('2050-02-08T08:03:00'),
                ],
                'same sig a minute later' => [
                    '',
                    '?sigopts=IntcInVudGlsXCI6XCIyMDUwLTAyLTA4VDA4OjA1OjAwKzAwOjAwXCJ9Ig%3D%3D&sig=3c47b0a26da4b044',
                    new \DateTime('2050-02-08T08:04:00'),
                ],
                'different sig 2 minutes later' => [
                    '',
                    '?sigopts=IntcInVudGlsXCI6XCIyMDUwLTAyLTA4VDA4OjEwOjAwKzAwOjAwXCJ9Ig%3D%3D&sig=5d278ae553f11990',
                    new \DateTime('2050-02-08T08:07:00'),
                ],
                'keep v query' => [
                    '?v={"a":"b"}',
                    '?v=%7B%22a%22:%22b%22%7D&sig=520f41fceb3f2281',
                ],
                'keep any query query' => [
                    '?foo=bar&lala=hello&soso',
                    '?foo=bar&lala=hello&soso&sig=4ea45331892304ad',
                ],
                'remove existing sig' => [
                    '?foo=bar&lala=hello&soso&sig=lala',
                    '?foo=bar&lala=hello&soso&sig=4ea45331892304ad',
                ],
                'remove existing sigopts & sig' => [
                    '?foo=bar&lala=hello&soso&sig=lala&sigopts=84989',
                    '?foo=bar&lala=hello&soso&sig=4ea45331892304ad',
                ],
                'remove existing sigopts & sig with date' => [
                    '?foo=bar&lala=hello&soso&sig=lala&sigopts=84989',
                    '?foo=bar&lala=hello&soso&sigopts=IntcInVudGlsXCI6XCIyMDUwLTAyLTA4VDA4OjA1OjAwKzAwOjAwXCJ9Ig%3D%3D&sig=11b8398ceeb98278',
                    new \DateTime('2050-02-08T08:03:00'),
                ],
            ];
    }

    /**
     * @dataProvider provideAddOptionsToUri
     *
     * @param string $inputQuery
     * @param string $expectedQuery
     *
     * @throws \PHPUnit_Framework_Exception
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function testCheckSignature($inputQuery = '', $expectedQuery = '', \DateTime $until = null)
    {
        $key = 'OCOuisGe30QyocYkQN1SPErGGKunyuhZ';
        $path = 'http://test.rokka.test/dynamic/c1b110.jpg';
        $this->assertSame(
            $path.$expectedQuery,
            (string) UriHelper::signUrl($path.$inputQuery, $key, $until));
    }

    public function testSlashSignature()
    {
        //with leading slash
        $this->assertEquals('/dynamic/abcdef.jpg?sig=c819798233635f29', (string) UriHelper::signUrl('/dynamic/abcdef.jpg', 'abcdef'));
        //without leading slash, should return the same sig
        $this->assertEquals('dynamic/abcdef.jpg?sig=c819798233635f29', (string) UriHelper::signUrl('dynamic/abcdef.jpg', 'abcdef'));
        // different path leads to different sig
        $this->assertEquals('/dynamic/resize-width-100/abcdef.jpg?sig=a854c19f4953cbac', (string) UriHelper::signUrl('/dynamic/resize-width-100/abcdef.jpg', 'abcdef'));
    }
}
