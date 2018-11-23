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

    // Available scopes.
    public const SCOPE_APP_REMOTE_CONTROL = 'app-remote-control';
    public const SCOPE_PLAYLIST_MODIFY_PRIVATE = 'playlist-modify-private';
    public const SCOPE_PLAYLIST_MODIFY_PUBLIC = 'playlist-modify-public';
    public const SCOPE_PLAYLIST_READ_COLLABORATIVE = 'playlist-read-collaborative';
    public const SCOPE_PLAYLIST_READ_PRIVATE = 'playlist-read-private';
    public const SCOPE_STREAMING = 'streaming';
    public const SCOPE_USER_READ_PRIVATE = 'user-read-private';
    public const SCOPE_USER_READ_BIRTHDATE = 'user-read-birthdate';
    public const SCOPE_USER_READ_EMAIL = 'user-read-email';
    public const SCOPE_USER_TOP_READ = 'user-top-read';
    public const SCOPE_USER_READ_RECENTLY_PLAYED = 'user-read-recently-played';
    public const SCOPE_USER_LIBRARY_MODIFY = 'user-library-modify';
    public const SCOPE_USER_LIBRARY_READ = 'user-library-read';
    public const SCOPE_USER_READ_CURRENTLY_PLAYING = 'user-read-currently-playing';
    public const SCOPE_USER_READ_PLAYBACK_STATE = 'user-read-playback-state';
    public const SCOPE_USER_MODIFY_PLAYBACK_STATE = 'user-modify-playback-state';
    public const SCOPE_USER_FOLLOW_MODIFY = 'user-follow-modify';
    public const SCOPE_USER_FOLLOW_READ = 'user-follow-read';

    /**
     * Spotify constructor.
     *
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (!isset($options['responseType']) || $options['responseType'] !== self::RESPONSE_TYPE) {
            $options['responseType'] = self::RESPONSE_TYPE;
        }

        parent::__construct($options, $collaborators);
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return self::BASE_SPOTIFY_URL . 'authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return self::BASE_SPOTIFY_URL . 'api/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     *
     * @return string
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
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * Checks a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string      $data     Parsed response data
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
     *
     * @param array       $response
     * @param AccessToken $token
     *
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new SpotifyResourceOwner($response);
    }
}
