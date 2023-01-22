<?php

namespace Vatsim\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class VatsimResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw user data from VATSIM Connect.
     *
     * @var array
     */
    protected array $response;

    /**
     * Constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Get the user's VATSIM CID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->response['cid'];
    }

    /**
     * Get first name.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->response['personal']['name_first'] ?? null;
    }

    /**
     * Get last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->response['personal']['name_last'] ?? null;
    }

    /**
     * Get full name.
     *
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return $this->response['personal']['name_full'] ?? null;
    }

    /**
     * Get email address.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['personal']['email'] ?? null;
    }

    /**
     * Get country code.
     *
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->response['personal']['country']['id'] ?? null;
    }

    /**
     * Get country name.
     *
     * @return string|null
     */
    public function getCountryName(): ?string
    {
        return $this->response['personal']['country']['name'] ?? null;
    }

    /**
     * Get VATSIM profile data.
     *
     * @return array|null
     */
    public function getVatsimProfile(): ?array
    {
        return $this->response['vatsim'] ?? null;
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
