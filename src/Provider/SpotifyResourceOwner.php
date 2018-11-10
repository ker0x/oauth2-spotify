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

    /**
     * SpotifyUser constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->data = $response;
    }

    /**
     * @return null|string
     */
    public function getBirthDate(): ?string
    {
        return $this->data['birthdate'] ?? null;
    }

    /**
     * @return null|string
     */
    public function getCountry(): ?string
    {
        return $this->data['country'] ?? null;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->data['display_name'];
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->data['email'] ?? null;
    }

    /**
     * @return array
     */
    public function getExternalUrls(): array
    {
        return $this->data['external_urls'];
    }

    /**
     * @return array
     */
    public function getFollowers(): array
    {
        return $this->data['followers'];
    }

    /**
     * @return string
     */
    public function getHref(): string
    {
        return $this->data['href'];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->data['id'];
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->data['images'];
    }

    /**
     * @return null|string
     */
    public function getProduct(): ?string
    {
        return $this->data['product'] ?? null;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->data['type'];
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->data['uri'];
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
