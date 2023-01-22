<?php

namespace Vatsim\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Vatsim extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * VATSIM Connect API base URL.
     *
     * @var string
     */
    public string $domain = 'https://auth.vatsim.net';

    /**
     * Returns the base URL for authorizing a client.
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->domain . '/oauth/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->domain . '/oauth/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->domain . '/api/user';
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
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['message'] ?? $response->getReasonPhrase(),
                $response->getStatusCode(),
                $data
            );
        }

        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error'],
                $response->getStatusCode(),
                $data
            );
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param  array $response
     * @param  AccessToken $token
     * @return VatsimResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): VatsimResourceOwner
    {
        return new VatsimResourceOwner($response['data']);
    }
}
