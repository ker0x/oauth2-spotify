<div align="center">
    <a href="https://github.com/ker0x/oauth2-spotify/actions/workflows/ci.yml" title="Build">
        <img src="https://img.shields.io/github/actions/workflow/status/ker0x/oauth2-spotify/ci.yml?branch=main&style=for-the-badge" alt="Build">
    </a>
    <a href="https://scrutinizer-ci.com/g/ker0x/oauth2-spotify/" title="Coverage">
        <img src="https://img.shields.io/codecov/c/gh/ker0x/oauth2-spotify?style=for-the-badge" alt="Coverage">
    </a>
    <a href="https://php.net" title="PHP Version">
        <img src="https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg?style=for-the-badge" alt="PHP Version">
    </a>
    <a href="https://packagist.org/packages/kerox/oauth2-spotify" title="Downloads">
        <img src="https://img.shields.io/packagist/dt/kerox/oauth2-spotify.svg?style=for-the-badge" alt="Downloads">
    </a>
    <a href="https://packagist.org/packages/kerox/oauth2-spotify" title="Latest Stable Version">
        <img src="https://img.shields.io/packagist/v/kerox/oauth2-spotify.svg?style=for-the-badge" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/kerox/oauth2-spotify" title="License">
        <img src="https://img.shields.io/packagist/l/kerox/oauth2-spotify.svg?style=for-the-badge" alt="License">
    </a>
</div>

# Spotify Provider for OAuth 2.0 Client

This package provides Spotify OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

You can install this package using Composer:

```
composer require kerox/oauth2-spotify
```

You will then need to:
* run ``composer install`` to get these dependencies added to your vendor directory
* add the autoloader to your application with this line: ``require('vendor/autoload.php');``

## Usage

Usage is the same as The League's OAuth client, using `\Kerox\OAuth2\Client\Provider\Spotify` as the provider.

### Authorization Code Flow

```php
$provider = new Kerox\OAuth2\Client\Provider\Spotify([
    'clientId'     => '{spotify-client-id}',
    'clientSecret' => '{spotify-client-secret}',
    'redirectUri'  => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {
    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => [
            Kerox\OAuth2\Client\Provider\SpotifyScope::USER_READ_EMAIL->value,
        ]
    ]);
    
    $_SESSION['oauth2state'] = $provider->getState();
    
    header('Location: ' . $authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    echo 'Invalid state.';
    exit;

}

// Try to get an access token (using the authorization code grant)
$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

// Optional: Now you have a token you can look up a users profile data
try {

    // We got an access token, let's now get the user's details
    /** @var \Kerox\OAuth2\Client\Provider\SpotifyResourceOwner $user */
    $user = $provider->getResourceOwner($token);

    // Use these details to create a new profile
    printf('Hello %s!', $user->getDisplayName());
    
    echo '<pre>';
    var_dump($user);
    echo '</pre>';

} catch (Exception $e) {

    // Failed to get user details
    exit('Damned...');
}

echo '<pre>';
// Use this to interact with an API on the users behalf
var_dump($token->getRefreshToken());
# string(217) "CAADAppfn3msBAI7tZBLWg...

// The time (in epoch time) when an access token will expire
var_dump($token->getExpires());
# int(1436825866)
echo '</pre>';
```

### Authorization Scopes

All scopes described in the [official documentation](https://developer.spotify.com/documentation/general/guides/authorization/scopes/) are available through the `\Kerox\OAuth2\Client\Provider\SpotifyScope` enumeration:

- Images
  * UGC_IMAGE_UPLOAD
- Spotify Connect 
  * USER_READ_PLAYBACK_STATE
  * USER_MODIFY_PLAYBACK_STATE
  * USER_READ_CURRENTLY_PLAYING
- Playback
  * APP_REMOTE_CONTROL
  * STREAMING
- Playlists
  * PLAYLIST_READ_PRIVATE
  * PLAYLIST_READ_COLLABORATIVE
  * PLAYLIST_MODIFY_PRIVATE
  * PLAYLIST_MODIFY_PUBLIC
- Follow
  * USER_FOLLOW_MODIFY
  * USER_FOLLOW_READ
- Listening History
  * USER_READ_PLAYBACK_POSITION
  * USER_TOP_READ
  * USER_READ_RECENTLY_PLAYED
- Library
  * USER_LIBRARY_MODIFY
  * USER_LIBRARY_READ
- Users
  * USER_READ_PRIVATE
  * USER_READ_EMAIL

## Contributing

Please see [CONTRIBUTING](https://github.com/ker0x/oauth2-spotify/blob/master/CONTRIBUTING.md) for details.

## Credits

- [Romain Monteil](https://github.com/ker0x)

## License

The MIT License (MIT). Please see [License File](https://github.com/ker0x/oauth2-spotify/blob/master/LICENSE) for more information.
