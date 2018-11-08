<?php

namespace Kerox\OAuth2\Client\Test\TestCase\Provider;

use Kerox\OAuth2\Client\Provider\SpotifyResourceOwner;
use PHPUnit\Framework\TestCase;

class SpotifyResourceOwnerTest extends TestCase
{
    /**
     * @var \Kerox\OAuth2\Client\Provider\SpotifyResourceOwner
     */
    protected $resourceOwner;

    protected function setUp()
    {
        $user = json_decode(file_get_contents(__DIR__ . '/../../Mocks/user.json'), true);

        $this->resourceOwner = new SpotifyResourceOwner($user);
    }

    public function testGetter()
    {
        $this->assertEquals('1990-01-01', $this->resourceOwner->getBirthDate());
        $this->assertEquals('FR', $this->resourceOwner->getCountry());
        $this->assertEquals('John Doe', $this->resourceOwner->getDisplayName());
        $this->assertEquals('john.doe@example.com', $this->resourceOwner->getEmail());
        $this->assertEquals(['spotify' => 'https://open.spotify.com/user/1122334455'], $this->resourceOwner->getExternalUrls());
        $this->assertEquals(['href' => null, 'total' => 10], $this->resourceOwner->getFollowers());
        $this->assertEquals('https://api.spotify.com/v1/users/1122334455', $this->resourceOwner->getHref());
        $this->assertEquals('1122334455', $this->resourceOwner->getId());
        $this->assertEquals([
            [
                'height' => null,
                'url' => 'https://example.com/31964231_10156960367129386_5965686321191059456_n.jpg',
                'width' => null,
            ],
        ], $this->resourceOwner->getImages());
        $this->assertEquals('premium', $this->resourceOwner->getProduct());
        $this->assertEquals('user', $this->resourceOwner->getType());
        $this->assertEquals('spotify:user:1122334455', $this->resourceOwner->getUri());
    }

    public function testToArray()
    {
        $array = json_decode(file_get_contents(__DIR__ . '/../../Mocks/user.json'), true);

        $this->assertSame($array, $this->resourceOwner->toArray());
    }
}
