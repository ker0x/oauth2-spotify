<?php

declare(strict_types=1);

namespace Kerox\OAuth2\Client\Provider;

enum SpotifyScope: string
{
    // Images
    case UGC_IMAGE_UPLOAD = 'ugc-image-upload';

    // Spotify Connect
    case USER_READ_PLAYBACK_STATE = 'user-read-playback-state';
    case USER_MODIFY_PLAYBACK_STATE = 'user-modify-playback-state';
    case USER_READ_CURRENTLY_PLAYING = 'user-read-currently-playing';

    // Playback
    case APP_REMOTE_CONTROL = 'app-remote-control';
    case STREAMING = 'streaming';

    // Playlist
    case PLAYLIST_READ_PRIVATE = 'playlist-read-private';
    case PLAYLIST_READ_COLLABORATIVE = 'playlist-read-collaborative';
    case PLAYLIST_MODIFY_PRIVATE = 'playlist-modify-private';
    case PLAYLIST_MODIFY_PUBLIC = 'playlist-modify-public';

    // Follow
    case USER_FOLLOW_MODIFY = 'user-follow-modify';
    case USER_FOLLOW_READ = 'user-follow-read';

    // Listening History
    case USER_READ_PLAYBACK_POSITION = 'user-read-playback-position';
    case USER_TOP_READ = 'user-top-read';
    case USER_READ_RECENTLY_PLAYED = 'user-read-recently-played';

    // Library
    case USER_LIBRARY_MODIFY = 'user-library-modify';
    case USER_LIBRARY_READ = 'user-library-read';

    // User
    case USER_READ_PRIVATE = 'user-read-private';
    case USER_READ_EMAIL = 'user-read-email';
}
