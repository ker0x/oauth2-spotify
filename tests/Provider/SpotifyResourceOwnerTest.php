<?php

declare(strict_types=1);

namespace Kerox\OAuth2\Client\Tests\Provider;

use Kerox\OAuth2\Client\Provider\SpotifyResourceOwner;
use PHPUnit\Framework\TestCase;

class SpotifyResourceOwnerTest extends TestCase
{
    /**
     * @var \Kerox\OAuth2\Client\Provider\SpotifyResourceOwner
     */
    protected $resourceOwner;

    protected function setUp(): void
    {
        $user = json_decode(file_get_contents(__DIR__ . '/../Mocks/user.json'), true);

        $this->resourceOwner = new SpotifyResourceOwner($user);
    }

    public function testGetter(): void
    {
        $this->assertSame('1990-01-01', $this->resourceOwner->getBirthDate());
        $this->assertSame('FR', $this->resourceOwner->getCountry());
        $this->assertSame('John Doe', $this->resourceOwner->getDisplayName());
        $this->assertSame('john.doe@example.com', $this->resourceOwner->getEmail());
        $this->assertSame(['spotify' => 'https://open.spotify.com/user/1122334455'], $this->resourceOwner->getExternalUrls());
        $this->assertSame(['href' => null, 'total' => 10], $this->resourceOwner->getFollowers());
        $this->assertSame('https://api.spotify.com/v1/users/1122334455', $this->resourceOwner->getHref());
        $this->assertSame('1122334455', $this->resourceOwner->getId());
        $this->assertSame([
            [
                'height' => null,
                'url' => 'https://example.com/31964231_10156960367129386_5965686321191059456_n.jpg',
                'width' => null,
            ],
        ], $this->resourceOwner->getImages());
        $this->assertSame('premium', $this->resourceOwner->getProduct());
        $this->assertSame('user', $this->resourceOwner->getType());
        $this->assertSame('spotify:user:1122334455', $this->resourceOwner->getUri());
    }

    public function testToArray(): void
    {
        $array = json_decode(file_get_contents(__DIR__ . '/../Mocks/user.json'), true);

        $this->assertSame($array, $this->resourceOwner->toArray());
    }
}
