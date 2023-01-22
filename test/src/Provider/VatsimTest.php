<?php

namespace Vatsim\OAuth2\Client\Test\Provider;

use GuzzleHttp\ClientInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Vatsim\OAuth2\Client\Provider\Vatsim;

class VatsimTest extends TestCase
{
    use QueryBuilderTrait;

    protected Vatsim $provider;

    protected function setUp(): void
    {
        $this->provider = new Vatsim([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes()
    {
        $scopeSeparator = ' ';
        $options = ['scope' => [uniqid(), uniqid()]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);
        $this->assertStringContainsString($encodedScope, $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')
            ->andReturn(json_encode([
                'scopes' => [],
                'token_type' => 'Bearer',
                // 'expires_in' => 604800,
                'access_token' => 'mock_access_token',
                // 'refresh_token' => 'mock_refresh_token',
            ]));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'application/json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testOwnerData()
    {
        $id = uniqid();
        $firstName = uniqid();
        $lastName = uniqid();
        $fullName = uniqid();
        $email = uniqid();
        $countryCode = uniqid();
        $countryName = uniqid();
        $vatsimProfile = [uniqid()];

        $authResponse = Mockery::mock(ResponseInterface::class);
        $authResponse->shouldReceive('getBody')
            ->andReturn(json_encode([
                'scopes' => ['full_name', 'email', 'country', 'vatsim_profile'],
                'token_type' => 'Bearer',
                // 'expires_in' => 604800,
                'access_token' => 'mock_access_token',
                // 'refresh_token' => 'mock_refresh_token',
            ]));
        $authResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/json']);
        $authResponse->shouldReceive('getStatusCode')->andReturn(200);

        $resourceResponse = Mockery::mock(ResponseInterface::class);
        $resourceResponse->shouldReceive('getBody')
            ->andReturn(json_encode([
                'data' => [
                    'cid' => $id,
                    'personal' => [
                        'name_first' => $firstName,
                        'name_last' => $lastName,
                        'name_full' => $fullName,
                        'email' => $email,
                        'country' => [
                            'id' => $countryCode,
                            'name' => $countryName,
                        ],
                    ],
                    'vatsim' => $vatsimProfile,
                    'oauth' => ['token_valid' => true],
                ],
            ]));
        $resourceResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/json']);
        $resourceResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($authResponse, $resourceResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($id, $user->toArray()['cid']);
        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($firstName, $user->toArray()['personal']['name_first']);
        $this->assertEquals($lastName, $user->getLastName());
        $this->assertEquals($lastName, $user->toArray()['personal']['name_last']);
        $this->assertEquals($fullName, $user->getFullName());
        $this->assertEquals($fullName, $user->toArray()['personal']['name_full']);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->toArray()['personal']['email']);
        $this->assertEquals($countryCode, $user->getCountryCode());
        $this->assertEquals($countryCode, $user->toArray()['personal']['country']['id']);
        $this->assertEquals($countryName, $user->getCountryName());
        $this->assertEquals($countryName, $user->toArray()['personal']['country']['name']);
        $this->assertEquals($vatsimProfile, $user->getVatsimProfile());
        $this->assertEquals($vatsimProfile, $user->toArray()['vatsim']);
    }

    public function testExceptionThrownWhenErrorObjectReceived()
    {
        $status = rand(400, 600);
        $message = uniqid();

        $postResponse = Mockery::mock(ResponseInterface::class);
        $postResponse->shouldReceive('getBody')
            ->andReturn(json_encode([
                'message' => $message,
            ]));
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $this->expectException(IdentityProviderException::class);
        $this->expectExceptionMessage($message);
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testExceptionThrownWhenErrorResponseReceived()
    {
        $status = rand(400, 600);
        $reason = uniqid();

        $postResponse = Mockery::mock(ResponseInterface::class);
        $postResponse->shouldReceive('getBody')
            ->andReturn('<html><body>some unexpected response.</body></html>');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'text/html']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);
        $postResponse->shouldReceive('getReasonPhrase')->andReturn($reason);

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $this->expectException(IdentityProviderException::class);
        $this->expectExceptionMessage($reason);
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testExceptionThrownWhenOAuthErrorReceived()
    {
        $error = uniqid();

        $postResponse = Mockery::mock(ResponseInterface::class);
        $postResponse->shouldReceive('getBody')
            ->andReturn(json_encode([
                'error' => $error,
            ]));
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $this->expectException(IdentityProviderException::class);
        $this->expectExceptionMessage($error);
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}
