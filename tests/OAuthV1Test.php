<?php

namespace Tests;

use Anibalealvarezs\OAuthV1\Enums\SignatureMethod;
use Anibalealvarezs\OAuthV1\OAuthV1;
use Faker\Factory as Faker;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class OAuthV1Test extends TestCase
{
    protected Generator $faker;
    protected string $consumerId;
    protected string $consumerSecret;
    protected string $token;
    protected string $tokenSecret;
    protected string $realm;
    protected SignatureMethod $signatureMethod;
    protected string $version;
    protected string $nonce;
    protected string $timestamp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->consumerId = $this->faker->uuid;
        $this->consumerSecret = $this->faker->uuid;
        $this->token = $this->faker->uuid;
        $this->tokenSecret = $this->faker->uuid;
        $this->realm = $this->faker->word;
        $this->signatureMethod = SignatureMethod::HMAC_SHA1;
        $this->version = '1.0';
        $this->nonce = $this->faker->sha1;
        $this->timestamp = (string) time();
    }

    /**
     * @throws ReflectionException
     */
    public function testConstructorWithValidParameters(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret,
            realm: $this->realm,
            signatureMethod: $this->signatureMethod,
            version: $this->version
        );

        // Set nonce explicitly to avoid relying on Helper::generateNonce
        $oauth->setNonce($this->nonce);

        $this->assertEquals($this->consumerId, $this->getProperty($oauth, 'consumerId'));
        $this->assertEquals($this->consumerSecret, $this->getProperty($oauth, 'consumerSecret'));
        $this->assertEquals($this->token, $this->getProperty($oauth, 'token'));
        $this->assertEquals($this->tokenSecret, $this->getProperty($oauth, 'tokenSecret'));
        $this->assertEquals($this->realm, $this->getProperty($oauth, 'realm'));
        $this->assertEquals($this->signatureMethod, $oauth->getSignatureMethod());
        $this->assertEquals($this->version, $oauth->getVersion());
        $this->assertEquals($this->nonce, $oauth->getNonce());
    }

    /**
     * @throws ReflectionException
     */
    public function testSetConsumerId(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $newConsumerId = $this->faker->uuid;
        $oauth->setConsumerId($newConsumerId);
        $this->assertEquals($newConsumerId, $this->getProperty($oauth, 'consumerId'));
    }

    /**
     * @throws ReflectionException
     */
    public function testSetConsumerSecret(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $newConsumerSecret = $this->faker->uuid;
        $oauth->setConsumerSecret($newConsumerSecret);
        $this->assertEquals($newConsumerSecret, $this->getProperty($oauth, 'consumerSecret'));
    }

    /**
     * @throws ReflectionException
     */
    public function testSetToken(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $newToken = $this->faker->uuid;
        $oauth->setToken($newToken);
        $this->assertEquals($newToken, $this->getProperty($oauth, 'token'));
    }

    /**
     * @throws ReflectionException
     */
    public function testSetTokenSecret(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $newTokenSecret = $this->faker->uuid;
        $oauth->setTokenSecret($newTokenSecret);
        $this->assertEquals($newTokenSecret, $this->getProperty($oauth, 'tokenSecret'));
    }

    /**
     * @throws ReflectionException
     */
    public function testSetRealm(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $newRealm = $this->faker->word;
        $oauth->setRealm($newRealm);
        $this->assertEquals($newRealm, $this->getProperty($oauth, 'realm'));
    }

    public function testSetSignatureMethod(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $newSignatureMethod = SignatureMethod::HMAC_SHA256;
        $oauth->setSignatureMethod($newSignatureMethod);
        $this->assertEquals($newSignatureMethod, $oauth->getSignatureMethod());
    }

    public function testSetVersion(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $newVersion = '1.0a';
        $oauth->setVersion($newVersion);
        $this->assertEquals($newVersion, $oauth->getVersion());
    }

    public function testSetNonce(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $newNonce = $this->faker->sha1;
        $oauth->setNonce($newNonce);
        $this->assertEquals($newNonce, $oauth->getNonce());
    }

    public function testSetTimestamp(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $newTimestamp = '1697059200';
        $oauth->setTimestamp($newTimestamp);
        $this->assertEquals($newTimestamp, $oauth->getTimestamp());
    }

    public function testGetNormalizedParams(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret
        );
        $oauth->setNonce($this->nonce);
        $oauth->setTimestamp($this->timestamp);

        $queryParams = ['param1' => 'value1', 'param2' => 'value2'];
        $normalizedParams = $oauth->getNormalizedParams($queryParams);

        $expectedParams = [
            urlencode('oauth_consumer_key') => urlencode($this->consumerId),
            urlencode('oauth_nonce') => urlencode($this->nonce),
            urlencode('oauth_signature_method') => urlencode(SignatureMethod::HMAC_SHA1->value),
            urlencode('oauth_timestamp') => urlencode($this->timestamp),
            urlencode('oauth_token') => urlencode($this->token),
            urlencode('oauth_version') => urlencode('1.0'),
            urlencode('param1') => urlencode('value1'),
            urlencode('param2') => urlencode('value2'),
        ];
        ksort($expectedParams);

        $this->assertEquals($expectedParams, $normalizedParams);
    }

    public function testGetAuthorizationHeaderWithHmacSha1(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret,
            realm: $this->realm,
            signatureMethod: SignatureMethod::HMAC_SHA1
        );
        $oauth->setNonce($this->nonce);
        $oauth->setTimestamp($this->timestamp);

        $method = 'GET';
        $url = 'https://api.example.com/resource';
        $queryParams = ['param1' => 'value1'];
        $prefix = 'OAuth ';

        $result = $oauth->getAuthorizationHeader($method, $url, $queryParams, $prefix);

        $this->assertStringStartsWith($prefix, $result['string']);
        $this->assertStringContainsString('realm="' . urlencode($this->realm) . '"', $result['string']);
        $this->assertStringContainsString('oauth_consumer_key="' . urlencode($this->consumerId) . '"', $result['string']);
        $this->assertStringContainsString('oauth_nonce="' . urlencode($this->nonce) . '"', $result['string']);
        $this->assertStringContainsString('oauth_signature_method="' . urlencode(SignatureMethod::HMAC_SHA1->value) . '"', $result['string']);
        $this->assertStringContainsString('oauth_timestamp="' . urlencode($this->timestamp) . '"', $result['string']);
        $this->assertStringContainsString('oauth_token="' . urlencode($this->token) . '"', $result['string']);
        $this->assertStringContainsString('oauth_version="' . urlencode('1.0') . '"', $result['string']);
        $this->assertArrayHasKey('debugData', $result);
    }

    public function testGetNormalizedParamsWithSignature(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret,
            realm: $this->realm,
            signatureMethod: SignatureMethod::HMAC_SHA1
        );
        $oauth->setNonce($this->nonce);
        $oauth->setTimestamp($this->timestamp);

        $method = 'GET';
        $url = 'https://api.example.com/resource';
        $queryParams = ['param1' => 'value1'];

        $result = $oauth->getNormalizedParamsWithSignature($method, $url, $queryParams);

        $this->assertArrayHasKey('params', $result);
        $this->assertArrayHasKey('debugData', $result);
        $this->assertArrayHasKey(urlencode('realm'), $result['params']);
        $this->assertArrayHasKey(urlencode('oauth_signature'), $result['params']);
        $this->assertEquals(urlencode($this->realm), $result['params'][urlencode('realm')]);
    }

    public function testGetSignedSbsWithHmacSha1(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret,
            signatureMethod: SignatureMethod::HMAC_SHA1
        );
        $oauth->setNonce($this->nonce);
        $oauth->setTimestamp($this->timestamp);

        $method = 'GET';
        $url = 'https://api.example.com/resource';
        $params = $oauth->getNormalizedParams();

        $result = $oauth->getSignedSbs($method, $url, $params);

        $this->assertArrayHasKey('sbs', $result);
        $this->assertArrayHasKey('debugData', $result);
        $this->assertNotEmpty($result['sbs']);
        $this->assertArrayHasKey('signature', $result['debugData']);
    }

    public function testGetSignedSbsWithPlaintext(): void
    {
        $oauth = new OAuthV1(
            consumerId: $this->consumerId,
            consumerSecret: $this->consumerSecret,
            token: $this->token,
            tokenSecret: $this->tokenSecret,
            signatureMethod: SignatureMethod::PLAINTEXT
        );
        $oauth->setNonce($this->nonce);
        $oauth->setTimestamp($this->timestamp);

        $method = 'GET';
        $url = 'https://api.example.com/resource';
        $params = $oauth->getNormalizedParams();

        $result = $oauth->getSignedSbs($method, $url, $params);

        $expectedSignature = urlencode($this->consumerSecret . '&' . $this->tokenSecret);
        $this->assertEquals($expectedSignature, $result['sbs']);
    }

    /**
     * Helper method to get protected property via reflection.
     *
     * @param OAuthV1 $oauth
     * @param string $property
     * @return mixed
     * @throws ReflectionException
     */
    private function getProperty(OAuthV1 $oauth, string $property)
    {
        $reflection = new ReflectionClass($oauth);
        $prop = $reflection->getProperty($property);
        return $prop->getValue($oauth);
    }
}
