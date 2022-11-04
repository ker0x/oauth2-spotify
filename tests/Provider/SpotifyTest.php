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
        return json_decode(file_get_contents(__DIR__.'/../Mocks/user.json'), true, 512, \JSON_THROW_ON_ERROR);
    }
}

class SpotifyTest extends TestCase
{
    protected Spotify $provider;

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

        self::assertArrayHasKey('client_id', $query);
        self::assertArrayHasKey('redirect_uri', $query);
        self::assertArrayHasKey('state', $query);
        self::assertArrayHasKey('scope', $query);
        self::assertArrayHasKey('response_type', $query);
        self::assertNotNull($this->provider->getState());
    }

    public function testGetBaseAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        self::assertSame('/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        self::assertSame('/api/token', $uri['path']);
    }

    public function testGetResourceOwnerDetailsUrl(): void
    {
        $accessToken = $this->createMock(AccessToken::class);

        $url = $this->provider->getResourceOwnerDetailsUrl($accessToken);
        $uri = parse_url($url);

        self::assertSame('/v1/me', $uri['path']);
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
        self::assertSame('mock_access_token', $token->getToken());
        self::assertLessThanOrEqual(time() + 3600, $token->getExpires());
        self::assertGreaterThanOrEqual(time(), $token->getExpires());
        self::assertNull($token->getRefreshToken());
        self::assertNull($token->getResourceOwnerId());
    }

    public function testGetResourceOwner(): void
    {
        $provider = new FooSpotifyProvider();

        $token = $this->createMock(AccessToken::class);
        $user = $provider->getResourceOwner($token);

        self::assertSame('1990-01-01', $user->getBirthDate());
        self::assertSame('FR', $user->getCountry());
        self::assertSame('John Doe', $user->getDisplayName());
        self::assertSame('john.doe@example.com', $user->getEmail());
        self::assertSame(['spotify' => 'https://open.spotify.com/user/1122334455'], $user->getExternalUrls());
        self::assertSame(['href' => null, 'total' => 10], $user->getFollowers());
        self::assertSame('https://api.spotify.com/v1/users/1122334455', $user->getHref());
        self::assertSame('1122334455', $user->getId());
        self::assertSame([
            [
                'height' => null,
                'url' => 'https://example.com/31964231_10156960367129386_5965686321191059456_n.jpg',
                'width' => null,
            ],
        ], $user->getImages());
        self::assertSame('premium', $user->getProduct());
        self::assertSame('user', $user->getType());
        self::assertSame('spotify:user:1122334455', $user->getUri());
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

    protected function callMethod(string $name, array $args = []): mixed
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
