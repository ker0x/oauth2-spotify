<?php

declare(strict_types=1);

namespace Kerox\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class SpotifyResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $data;

    public function __construct(array $response)
    {
        $this->data = $response;
    }

    public function getBirthDate(): ?string
    {
        return $this->data['birthdate'] ?? null;
    }

    public function getCountry(): ?string
    {
        return $this->data['country'] ?? null;
    }

    public function getDisplayName(): string
    {
        return $this->data['display_name'];
    }

    public function getEmail(): ?string
    {
        return $this->data['email'] ?? null;
    }

    public function getExternalUrls(): array
    {
        return $this->data['external_urls'];
    }

    public function getFollowers(): array
    {
        return $this->data['followers'];
    }

    public function getHref(): string
    {
        return $this->data['href'];
    }

    public function getId(): string
    {
        return $this->data['id'];
    }

    public function getImages(): array
    {
        return $this->data['images'];
    }

    public function getProduct(): ?string
    {
        return $this->data['product'] ?? null;
    }

    public function getType(): string
    {
        return $this->data['type'];
    }

    public function getUri(): string
    {
        return $this->data['uri'];
    }

    /**
     * Return all of the owner details available as an array.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
