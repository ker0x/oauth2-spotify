<?php

declare(strict_types=1);

namespace Kerox\OAuth2\Client\Tests\Provider;

use GuzzleHttp\ClientInterface;
use Kerox\OAuth2\Client\Provider\Exception\SpotifyIdentityProviderException;
use Kerox\OAuth2\Client\Provider\Spotify;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class FooSpotifyProvider extends Spotify
{
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return json_decode(file_get_contents(__DIR__ . '/../Mocks/user.json'), true);
    }
}

class SpotifyTest extends TestCase
{
    /**
     * @var \Kerox\OAuth2\Client\Provider\Spotify
     */
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new Spotify([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri' => 'none',
            'responseType' => Spotify::RESPONSE_TYPE,
        ]);
    }

    public function testAuthorizationUrl(): void
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

    public function testGetBaseAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertSame('/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertSame('/api/token', $uri['path']);
    }

    public function testGetResourceOwnerDetailsUrl(): void
    {
        $accessToken = $this->createMock(AccessToken::class);

        $url = $this->provider->getResourceOwnerDetailsUrl($accessToken);
        $uri = parse_url($url);

        $this->assertSame('/v1/me', $uri['path']);
    }

    public function testGetAccessToken(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $response->method('getBody')->willReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
        $response->method('getHeader')->willReturn(['content-type' => 'json']);
        $response->method('getStatusCode')->willReturn(200);

        $client = $this->createMock(ClientInterface::class);
        $client->method('send')->willReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertSame('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testGetResourceOwner(): void
    {
        $provider = new FooSpotifyProvider();

        $token = $this->createMock(AccessToken::class);
        $user = $provider->getResourceOwner($token);

        $this->assertSame('1990-01-01', $user->getBirthDate($token));
        $this->assertSame('FR', $user->getCountry($token));
        $this->assertSame('John Doe', $user->getDisplayName($token));
        $this->assertSame('john.doe@example.com', $user->getEmail($token));
        $this->assertSame(['spotify' => 'https://open.spotify.com/user/1122334455'], $user->getExternalUrls($token));
        $this->assertSame(['href' => null, 'total' => 10], $user->getFollowers($token));
        $this->assertSame('https://api.spotify.com/v1/users/1122334455', $user->getHref($token));
        $this->assertSame('1122334455', $user->getId($token));
        $this->assertSame([
            [
                'height' => null,
                'url' => 'https://example.com/31964231_10156960367129386_5965686321191059456_n.jpg',
                'width' => null,
            ],
        ], $user->getImages($token));
        $this->assertSame('premium', $user->getProduct($token));
        $this->assertSame('user', $user->getType($token));
        $this->assertSame('spotify:user:1122334455', $user->getUri($token));
    }

    public function testCheckResponseFailureWithAuthenticationError(): void
    {
        $this->expectException(SpotifyIdentityProviderException::class);
        $this->expectExceptionMessage('Invalid client secret');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401);

        $data = [
            'error' => 'invalid_client',
            'error_description' => 'Invalid client secret',
        ];

        $this->callMethod('checkResponse', [$response, $data]);
    }

    public function testCheckResponseFailureWithRegularError(): void
    {
        $this->expectException(SpotifyIdentityProviderException::class);
        $this->expectExceptionMessage('invalid id');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $data = [
            'error' => [
                'status' => 400,
                'message' => 'invalid id',
            ],
        ];

        $this->callMethod('checkResponse', [$response, $data]);
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    protected function callMethod($name, array $args = [])
    {
        try {
            $reflection = new \ReflectionMethod(\get_class($this->provider), $name);
            $reflection->setAccessible(true);

            return $reflection->invokeArgs($this->provider, $args);
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}