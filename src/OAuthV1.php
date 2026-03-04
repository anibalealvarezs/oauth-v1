<?php

namespace Anibalealvarezs\OAuthV1;

use Anibalealvarezs\OAuthV1\Enums\SignatureMethod;
use Anibalealvarezs\OAuthV1\Helpers\Helper;

class OAuthV1
{
    /**
     * @var string
     */
    protected string $consumerId = '';

    /**
     * @var string
     */
    protected string $consumerSecret = '';

    /**
     * @var SignatureMethod
     */
    protected SignatureMethod $signatureMethod;

    /**
     * @var string
     */
    protected string $realm = '';

    /**
     * @var string
     */
    protected string $version = '';

    /**
     * @var string
     */
    protected string $token = '';

    /**
     * @var string
     */
    protected string $tokenSecret = '';

    /**
     * @var string
     */
    protected string $oauthNonce = '';

    /**
     * @var string
     */
    protected string $oauthTimestamp = '';

    /**
     * @param string $consumerId
     * @param string $consumerSecret
     * @param string $token
     * @param string $tokenSecret
     * @param string $realm
     * @param SignatureMethod $signatureMethod
     * @param string $version
     */
    public function __construct(
        string $consumerId,
        string $consumerSecret,
        string $token,
        string $tokenSecret,
        string $realm = "",
        SignatureMethod $signatureMethod = SignatureMethod::HMAC_SHA1,
        string $version = "1.0",
    ) {
        $this->setConsumerId($consumerId)
            ->setConsumerSecret($consumerSecret)
            ->setToken($token)
            ->setTokenSecret($tokenSecret)
            ->setRealm($realm)
            ->setSignatureMethod($signatureMethod)
            ->setVersion($version)
            ->setNonce(Helper::generateNonce());
    }

    /**
     * @return string
     */
    protected function getConsumerId(): string
    {
        return $this->consumerId;
    }

    /**
     * @param string $consumerId
     * @return OAuthV1
     */
    public function setConsumerId(string $consumerId): self
    {
        $this->consumerId = $consumerId;
        return $this;
    }

    /**
     * @return string
     */
    protected function getConsumerSecret(): string
    {
        return $this->consumerSecret;
    }

    /**
     * @param string $consumerSecret
     * @return OAuthV1
     */
    public function setConsumerSecret(string $consumerSecret): self
    {
        $this->consumerSecret = $consumerSecret;
        return $this;
    }

    /**
     * @return SignatureMethod
     */
    public function getSignatureMethod(): SignatureMethod
    {
        return $this->signatureMethod;
    }

    /**
     * @param SignatureMethod $signatureMethod
     * @return OAuthV1
     */
    public function setSignatureMethod(SignatureMethod $signatureMethod): self
    {
        $this->signatureMethod = $signatureMethod;
        return $this;
    }

    /**
     * @return string
     */
    protected function getRealm(): string
    {
        return $this->realm;
    }

    /**
     * @param string $realm
     * @return OAuthV1
     */
    public function setRealm(string $realm): self
    {
        $this->realm = $realm;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return OAuthV1
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    protected function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return OAuthV1
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    protected function getTokenSecret(): string
    {
        return $this->tokenSecret;
    }

    /**
     * @param string $tokenSecret
     * @return OAuthV1
     */
    public function setTokenSecret(string $tokenSecret): self
    {
        $this->tokenSecret = $tokenSecret;
        return $this;
    }

    /**
     * @return string
     */
    public function getNonce(): string
    {
        return $this->oauthNonce;
    }

    /**
     * @param string $nonce
     * @return OAuthV1
     */
    public function setNonce(string $nonce): self
    {
        $this->oauthNonce = $nonce;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->oauthTimestamp;
    }

    /**
     * @param string $timestamp
     * @return OAuthV1
     */
    public function setTimestamp(string $timestamp): self
    {
        $this->oauthTimestamp = $timestamp;
        return $this;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $queryParams
     * @param string $prefix
     * @return array
     */
    public function getAuthorizationHeader(string $method, string $url, array $queryParams = [], string $prefix = ''): array
    {
        $string = $prefix;
        $params = $this->getNormalizedParamsWithSignature(
            httpMethod: $method,
            url: $url,
            queryParams: $queryParams,
        );
        $counter = 0;
        foreach ($params['params'] as $k => $v) {
            if (!isset($queryParams[$k])) {
                $string .= ($counter > 0 ? ',' : '') . $k . '="' . $v . '"';
            }
            $counter++;
        }
        return [
            'string' => $string,
            'debugData' => $params['debugData'],
        ];
    }

    /**
     * @param string $httpMethod
     * @param string $url
     * @param array $queryParams
     * @return array
     */
    public function getNormalizedParamsWithSignature(string $httpMethod, string $url, array $queryParams = []): array
    {
        $params = $this->getNormalizedParams(queryParams: $queryParams);
        $signedSbs = $this->getSignedSbs(
            httpMethod: $httpMethod,
            url: $url,
            params: $params,
        );
        $params[Helper::urlencode_rfc3986('oauth_signature')] = $signedSbs['sbs'];
        return [
            'params' => [Helper::urlencode_rfc3986('realm') => Helper::urlencode_rfc3986($this->getRealm()), ...$params],
            'debugData' => $signedSbs['debugData']
        ];
    }

    /**
     * @param string $httpMethod
     * @param string $url
     * @param array $params
     * @return array
     */
    public function getSignedSbs(string $httpMethod, string $url, array $params): array
    {
        $sbs = $this->getSbs(
            httpMethod: $httpMethod,
            url: $url,
            normalizedParams: $params,
        );
        $signature = match ($this->getSignatureMethod()) {
            SignatureMethod::HMAC_SHA1 => $this->getHmacSha1Signature(paramsString: $sbs),
            SignatureMethod::HMAC_SHA256 => $this->getHmacSha256Signature(paramsString: $sbs),
            SignatureMethod::PLAINTEXT => $this->getPlaintextSignature(),
            default => '',
        };
        return [
            'sbs' => Helper::urlencode_rfc3986($signature),
            'debugData' => [
                'normalizedParams' => $params,
                'sbs' => $sbs,
                'signature' => $signature,
            ],
        ];
    }

    /**
     * @param string $httpMethod
     * @param string $url
     * @param array $normalizedParams
     * @return string
     */
    protected function getSbs(string $httpMethod, string $url, array $normalizedParams): string
    {
        // Normalize URL
        $normalized_url = Helper::getNormalizedUrl($url);
        // Generate signature base string
        $sbs_parts = array(
            Helper::urlencode_rfc3986(strtoupper($httpMethod)),
            Helper::urlencode_rfc3986($normalized_url),
            Helper::urlencode_rfc3986(Helper::build_http_query($normalizedParams)),
        );
        return implode('&', $sbs_parts);
    }

    /**
     * @param array $queryParams
     * @return array
     */
    public function getNormalizedParams(array $queryParams = []): array
    {
        $params = [
            'oauth_consumer_key' => $this->getConsumerId(),
            'oauth_nonce' => $this->getNonce(),
            'oauth_signature_method' => $this->getSignatureMethod()->value,
            'oauth_timestamp' => $this->getTimestamp() ?: time(),
            'oauth_token' => $this->getToken(),
            'oauth_version' => $this->getVersion(),
            ...$queryParams,
        ];
        $normalizedParams = [];
        foreach ($params as $k => $v) {
            $normalizedParams[Helper::urlencode_rfc3986($k)] = Helper::urlencode_rfc3986($v);
        }
        uksort($normalizedParams, 'strcmp');
        return $normalizedParams;
    }

    /**
     * @return string
     */
    protected function getPlaintextSignature(): string
    {
        return rawurlencode($this->getConsumerSecret()) . '&' . rawurlencode($this->getTokenSecret());
    }

    /**
     * @param string $paramsString
     * @return string
     */
    protected function getHmacSha1Signature(string $paramsString): string
    {
        return base64_encode(hash_hmac('sha1', $paramsString, $this->getPlaintextSignature(), true));
    }

    /**
     * @param string $paramsString
     * @return string
     */
    protected function getHmacSha256Signature(string $paramsString): string
    {
        return base64_encode(hash_hmac('sha256', $paramsString, $this->getPlaintextSignature(), true));
    }
}
