<?php

namespace Kerox\OAuth2\Client\Test\TestCase\Provider;

use Kerox\OAuth2\Client\Provider\Spotify;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use League\OAuth2\Client\Token\AccessToken;

class FooSpotifyProvider extends Spotify
{
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return json_decode(file_get_contents(__DIR__ . '/../../Mocks/user.json'), true);
    }
}

class SpotifyTest extends TestCase
{
    /**
     * @var \Kerox\OAuth2\Client\Provider\Spotify
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Spotify([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri' => 'none',
            'responseType' => Spotify::RESPONSE_TYPE,
        ]);
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();

        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testGetBaseAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/api/token', $uri['path']);
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        $accessToken = Mockery::mock(AccessToken::class);

        $url = $this->provider->getResourceOwnerDetailsUrl($accessToken);
        $uri = parse_url($url);

        $this->assertEquals('/v1/me', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $response->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('send')->times(1)->andReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testGetResourceOwner()
    {
        $provider = new FooSpotifyProvider();

        $token = Mockery::mock(AccessToken::class);
        $user = $provider->getResourceOwner($token);

        $this->assertEquals('1990-01-01', $user->getBirthDate($token));
        $this->assertEquals('FR', $user->getCountry($token));
        $this->assertEquals('John Doe', $user->getDisplayName($token));
        $this->assertEquals('john.doe@example.com', $user->getEmail($token));
        $this->assertEquals(['spotify' => 'https://open.spotify.com/user/1122334455'], $user->getExternalUrls($token));
        $this->assertEquals(['href' => null, 'total' => 10], $user->getFollowers($token));
        $this->assertEquals('https://api.spotify.com/v1/users/1122334455', $user->getHref($token));
        $this->assertEquals('1122334455', $user->getId($token));
        $this->assertEquals([
            [
                'height' => null,
                'url' => 'https://example.com/31964231_10156960367129386_5965686321191059456_n.jpg',
                'width' => null,
            ],
        ], $user->getImages($token));
        $this->assertEquals('premium', $user->getProduct($token));
        $this->assertEquals('user', $user->getType($token));
        $this->assertEquals('spotify:user:1122334455', $user->getUri($token));
    }
}
