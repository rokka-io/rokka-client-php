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
                'infinite' => ['', '?sig=62e7a9ccd3dea053', null],
                'time limited' => [
                    '',
                    '?sigopts=%7B%22until%22%3A%222050-02-08T08%3A05%3A00%2B00%3A00%22%7D&sig=24f7a7b07122c063',
                    new \DateTime('2050-02-08T08:03:00+00:00'),
                ],
                'same sig a minute later' => [
                    '',
                    '?sigopts=%7B%22until%22%3A%222050-02-08T08%3A05%3A00%2B00%3A00%22%7D&sig=24f7a7b07122c063',
                    new \DateTime('2050-02-08T08:04:00+00:00'),
                ],
                'different sig 2 minutes later' => [
                    '',
                    '?sigopts=%7B%22until%22%3A%222050-02-08T08%3A10%3A00%2B00%3A00%22%7D&sig=fa95fb5de8a284df',
                    new \DateTime('2050-02-08T08:07:00+00:00'),
                ],
                'keep v query' => [
                    '?v={"a":"b"}',
                    '?v=%7B%22a%22:%22b%22%7D&sig=18b39da4d52113f8',
                ],
                'keep any query query' => [
                    '?foo=bar&lala=hello&soso',
                    '?foo=bar&lala=hello&soso&sig=b4491de1823ef16f',
                ],
                'remove existing sig' => [
                    '?foo=bar&lala=hello&soso&sig=lala',
                    '?foo=bar&lala=hello&soso&sig=b4491de1823ef16f',
                ],
                'remove existing sigopts & sig' => [
                    '?foo=bar&lala=hello&soso&sig=lala&sigopts=84989',
                    '?foo=bar&lala=hello&soso&sig=b4491de1823ef16f',
                ],
                'remove existing sigopts & sig with date' => [
                    '?foo=bar&lala=hello&soso&sig=lala&sigopts=84989',
                    '?foo=bar&lala=hello&soso&sigopts=%7B%22until%22%3A%222050-02-08T08%3A05%3A00%2B00%3A00%22%7D&sig=c444f72eb623e5c9',
                    new \DateTime('2050-02-08T08:03:00+00:00'),
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
    public function testCheckSignature($inputQuery = '', $expectedQuery = '', ?\DateTime $until = null)
    {
        $key = 'OCOuisGe30QyocYkQN1SPErGGKunyuhZ';
        $path = 'http://test.rokka.test/dynamic/c1b110.jpg';
        $this->assertSame(
            $path.$expectedQuery,
            (string) UriHelper::signUrl($path.$inputQuery, $key, $until));
    }

    public function testSlashSignature()
    {
        // with leading slash
        $this->assertEquals('/dynamic/abcdef.jpg?sig=b7789b71b470e458', (string) UriHelper::signUrl('/dynamic/abcdef.jpg', 'abcdef'));
        // without leading slash, should return the same sig
        $this->assertEquals('dynamic/abcdef.jpg?sig=b7789b71b470e458', (string) UriHelper::signUrl('dynamic/abcdef.jpg', 'abcdef'));
        // different path leads to different sig
        $this->assertEquals('/dynamic/resize-width-100/abcdef.jpg?sig=6a7233a3c5bd2374', (string) UriHelper::signUrl('/dynamic/resize-width-100/abcdef.jpg', 'abcdef'));
    }
}
