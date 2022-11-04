<?php

declare(strict_types=1);

namespace Kerox\OAuth2\Client\Provider;

use Kerox\OAuth2\Client\Provider\Exception\SpotifyIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Spotify extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const BASE_SPOTIFY_URL = 'https://accounts.spotify.com/';
    public const RESPONSE_TYPE = 'code';

    public function __construct(array $options = [], array $collaborators = [])
    {
        if (!isset($options['responseType']) || self::RESPONSE_TYPE !== $options['responseType']) {
            $options['responseType'] = self::RESPONSE_TYPE;
        }

        parent::__construct($options, $collaborators);
    }

    /**
     * Returns the base URL for authorizing a client.
     */
    public function getBaseAuthorizationUrl(): string
    {
        return self::BASE_SPOTIFY_URL.'authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return self::BASE_SPOTIFY_URL.'api/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.spotify.com/v1/me';
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * Checks a provider response for errors.
     *
     * @param array|string $data Parsed response data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            $error = $data['error_description'] ?? $data['error'] ?? $response->getReasonPhrase();
            $statusCode = $response->getStatusCode();

            if (\is_array($data['error'])) {
                $error = $data['error']['message'];
                $statusCode = $data['error']['status'];
            }

            throw new SpotifyIdentityProviderException($error, $statusCode, $response);
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new SpotifyResourceOwner($response);
    }
}
