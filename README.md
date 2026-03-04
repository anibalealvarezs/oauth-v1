# OAuthV1 Class Documentation

## Overview

The `OAuthV1` class, part of the `anibalealvarezs/oauth-v1` package, implements OAuth 1.0 authentication for secure API interactions. It provides methods to configure consumer credentials, tokens, signature methods, and generate authorization headers for HTTP requests. The class supports multiple signature methods (HMAC-SHA1, HMAC-SHA256, PLAINTEXT) and includes utilities for normalizing parameters and generating signatures.

### Namespace

`Anibalealvarezs\OAuthV1`

## Version

`1.0.0`

### Dependencies

- PHP 8.1 or higher
- `Anibalealvarezs\OAuthV1\Enums\SignatureMethod`
- `Anibalealvarezs\OAuthV1\Helpers\Helper`

## Installation

Add the package to your project using Composer:

```bash
composer require anibalealvarezs/oauth-v1:@dev
```

Ensure the repository is configured in `composer.json`:

```json
{
  "repositories": [
    {
      "type": "composer",
      "url": "https://satis.anibalalvarez.com/"
    }
  ]
}
```

## Usage

### Instantiating the Class

Create an instance of `OAuthV1` with required OAuth 1.0 parameters:

```php
use Anibalealvarezs\OAuthV1\OAuthV1;
use Anibalealvarezs\OAuthV1\Enums\SignatureMethod;

$oauth = new OAuthV1(
    consumerId: 'your-consumer-id',
    consumerSecret: 'your-consumer-secret',
    token: 'your-access-token',
    tokenSecret: 'your-token-secret',
    realm: 'your-realm',
    signatureMethod: SignatureMethod::HMAC_SHA1,
    version: '1.0'
);
```

### Generating an Authorization Header

Use `getAuthorizationHeader` to generate an OAuth 1.0 authorization header for an HTTP request:

```php
$method = 'GET';
$url = 'https://api.example.com/resource';
$queryParams = ['param1' => 'value1'];
$header = $oauth->getAuthorizationHeader($method, $url, $queryParams, 'OAuth ');

echo $header['string']; // Outputs: OAuth realm="your-realm",oauth_consumer_key="your-consumer-id",...
```

### Setting Parameters

Update OAuth parameters using setter methods:

```php
$oauth->setConsumerId('new-consumer-id')
      ->setNonce('custom-nonce')
      ->setTimestamp('1697059200');
```

## Methods

### Constructor

```php
public function __construct(
    string $consumerId,
    string $consumerSecret,
    string $token,
    string $tokenSecret,
    string $realm = "",
    SignatureMethod $signatureMethod = SignatureMethod::HMAC_SHA1,
    string $version = "1.0"
)
```

Initializes the OAuth 1.0 client with consumer credentials, tokens, realm, signature method, and version. Automatically generates a nonce using `Helper::generateNonce`.

### Setters

- `setConsumerId(string $consumerId): self`
- `setConsumerSecret(string $consumerSecret): self`
- `setToken(string $token): self`
- `setTokenSecret(string $tokenSecret): self`
- `setRealm(string $realm): self`
- `setSignatureMethod(SignatureMethod $signatureMethod): self`
- `setVersion(string $version): self`
- `setNonce(string $nonce): self`
- `setTimestamp(string $timestamp): self`

Set OAuth 1.0 parameters and return the instance for method chaining.

### Getters

- `getSignatureMethod(): SignatureMethod`
- `getVersion(): string`
- `getNonce(): string`
- `getTimestamp(): string`

Retrieve configured OAuth parameters.

### OAuth 1.0 Methods

- `getAuthorizationHeader(string $method, string $url, array $queryParams = [], string $prefix = ''): array`
  - Generates an OAuth 1.0 authorization header string and debug data.
  - Returns: `['string' => 'OAuth ...', 'debugData' => [...]]`
- `getNormalizedParams(array $queryParams = []): array`
  - Normalizes OAuth parameters and query parameters, sorting them for signature generation.
- `getNormalizedParamsWithSignature(string $httpMethod, string $url, array $queryParams = []): array`
  - Extends `getNormalizedParams` by adding a signature based on the HTTP method and URL.
- `getSignedSbs(string $httpMethod, string $url, array $params): array`
  - Generates a signed signature base string (SBS) using the configured signature method.

## Signature Methods

Supported signature methods (via `SignatureMethod` enum):

- `HMAC_SHA1`: HMAC-SHA1 signature.
- `HMAC_SHA256`: HMAC-SHA256 signature.
- `PLAINTEXT`: Plaintext signature (consumer secret and token secret).

## Example

```php
use Anibalealvarezs\OAuthV1\OAuthV1;
use Anibalealvarezs\OAuthV1\Enums\SignatureMethod;

$oauth = new OAuthV1(
    consumerId: 'consumer-id',
    consumerSecret: 'consumer-secret',
    token: 'access-token',
    tokenSecret: 'token-secret',
    signatureMethod: SignatureMethod::HMAC_SHA256
);

$header = $oauth->getAuthorizationHeader(
    method: 'POST',
    url: 'https://api.example.com/resource',
    queryParams: ['key' => 'value'],
    prefix: 'OAuth '
);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://api.example.com/resource');
curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: {$header['string']}"]);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['key' => 'value']));
curl_exec($curl);
curl_close($curl);
```

## Notes

- The `Helper` class provides utilities like `generateNonce`, `getNormalizedUrl`, and `build_http_query`.
- The `oauth_timestamp` is set to the current time by default in `getNormalizedParams` if not explicitly set.
- Ensure URLs and parameters are properly URL-encoded using `Helper::urlencode_rfc3986` for compliance with OAuth 1.0.

# Testing

## Prerequisites

Before running the tests, ensure the following requirements are met:

- **PHP**: Version 8.1 or higher, as required by the `anibalealvarezs/oauth-v1` package.
- **Composer**: Installed globally or locally to manage dependencies.
- **PHPUnit**: Installed via Composer as a development dependency.
- **Dependencies**: The `anibalealvarezs/oauth-v1` package must be installed via Composer.
- **Mocking**: Tests do not require mocking, as static methods (e.g., `Helper::generateNonce`) are handled by setting deterministic values via setters.

## Installation

To set up the tests, add PHPUnit and Faker to the development dependencies in your project’s `composer.json`:

```json
{
  "require": {
    "php": ">=8.1",
    "anibalealvarezs/oauth-v1": "@dev"
  },
  "repositories": [
    {
      "type": "composer",
      "url": "https://satis.anibalalvarez.com/"
    }
  ],
  "require-dev": {
    "phpunit/phpunit": "^9.5",
    "fakerphp/faker": "^1.20"
  }
}
```

Run the following command to install the package, PHPUnit, and Faker:

```bash
composer install
```

This will install the `anibalealvarezs/oauth-v1` package, PHPUnit, Faker, and any required dependencies.

## Test Setup

The `OAuthV1Test.php` test class, located in `tests/OAuthV1Test.php`, verifies the `OAuthV1` class. The test class uses:

- **Faker**: Generates random test data (e.g., UUIDs for consumer IDs, tokens, nonces).
- **PHPUnit**: Provides the testing framework for assertions and test execution.

The `setUp` method initializes the test environment by:

- Creating a Faker instance for generating test data.
- Defining OAuth 1.0 parameters, such as consumer ID, consumer secret, token, token secret, realm, signature method (`HMAC_SHA1`), version (`1.0`), nonce, and timestamp.
- Using setters (e.g., `setNonce`, `setTimestamp`) to ensure deterministic values for tests involving nonce and timestamp.

No external services or mocking are required, as the tests are self-contained and rely on direct interaction with the `OAuthV1` class.

## Running the Tests

To run the tests for the `OAuthV1` class, use the following command from the root directory of your project:

```bash
./vendor/bin/phpunit --verbose tests/OAuthV1Test.php
```

### Command Breakdown

- `./vendor/bin/phpunit`: Executes the PHPUnit binary installed via Composer.
- `--verbose`: Enables verbose output, displaying detailed information about each test case, including test names and results.
- `tests/OAuthV1Test.php`: Specifies the test file for the `OAuthV1` class.

### Expected Output

When running the command, you will see output similar to the following (assuming all 14 tests pass):

```
PHPUnit 9.6.22 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.12

..............  14 / 14 (100%)

Time: 00:00.050, Memory: 8.00 MB

OK (14 tests, 40 assertions)
```

- The dots (`.`) represent successful test cases.
- The summary indicates the total number of tests (14), execution time, memory usage, and any failures or errors.
- With `--verbose`, additional details about each test method (e.g., `testConstructorWithValidParameters`, `testGetAuthorizationHeaderWithHmacSha1`) will be displayed.

### Troubleshooting

If tests fail, check the following:

- **Dependencies**: Ensure all dependencies are installed (`composer install`) and match the required versions. Verify that `anibalealvarezs/oauth-v1` and `fakerphp/faker` are correctly installed.
- **PHP Version**: Confirm PHP 8.1 or higher is used (`php -v`).
- **Composer Autoloader**: Run `composer dump-autoload` to regenerate the autoloader if classes are not found.
- **File Path**: Ensure the test file is located at `tests/OAuthV1Test.php`. Adjust the PHPUnit command if the directory structure differs.
- **Verbose Output**: The `--verbose` flag provides detailed error messages, including stack traces for failed assertions or exceptions.

## Test Coverage

The `OAuthV1Test.php` class, located in `tests/OAuthV1Test.php`, includes 14 test methods that cover the `OAuthV1` class’s public methods, ensuring proper configuration and OAuth 1.0 signature generation. The tests include:

- **Constructor Validation**:
  - `testConstructorWithValidParameters`: Verifies that the constructor sets consumer ID, consumer secret, token, token secret, realm, signature method, version, and nonce correctly, using `setNonce` for deterministic testing.

- **Setter Methods**:
  - `testSetConsumerId`: Tests setting a new consumer ID.
  - `testSetConsumerSecret`: Tests setting a new consumer secret.
  - `testSetToken`: Tests setting a new access token.
  - `testSetTokenSecret`: Tests setting a new token secret.
  - `testSetRealm`: Tests setting a new realm.
  - `testSetSignatureMethod`: Tests setting a new signature method (e.g., `HMAC_SHA256`).
  - `testSetVersion`: Tests setting a new OAuth version.
  - `testSetNonce`: Tests setting a custom nonce.
  - `testSetTimestamp`: Tests setting a custom timestamp.

- **OAuth 1.0 Functionality**:
  - `testGetNormalizedParams`: Verifies that OAuth parameters and query parameters are normalized and sorted correctly, using `setNonce` and `setTimestamp`.
  - `testGetAuthorizationHeaderWithHmacSha1`: Tests generating an authorization header with HMAC-SHA1 signature method.
  - `testGetNormalizedParamsWithSignature`: Ensures normalized parameters include a signature based on the HTTP method and URL.
  - `testGetSignedSbsWithHmacSha1`: Tests generating a signed signature base string (SBS) with HMAC-SHA1.
  - `testGetSignedSbsWithPlaintext`: Verifies SBS generation with PLAINTEXT signature method.

To generate a test coverage report:

```bash
./vendor/bin/phpunit --verbose --coverage-text tests/OAuthV1Test.php
```

For an HTML report:

```bash
./vendor/bin/phpunit --verbose --coverage-html coverage tests/OAuthV1Test.php
```

This generates an HTML report in the `coverage/` directory, detailing coverage for the `OAuthV1` class (requires PHPUnit to be configured with coverage reporting).

## Additional Notes

- **Nonce Handling**: Tests use `setNonce` to set deterministic nonce values, as the static `Helper::generateNonce` method cannot be mocked. This ensures consistent signature generation.
- **Isolation**: Each test method is isolated, with the `setUp` method resetting the test environment to prevent state leakage.
- **Extending Tests**: To add tests for additional signature methods (e.g., `HMAC_SHA256`), edge cases (e.g., invalid URLs, empty parameters), or validation (e.g., empty consumer ID), extend `OAuthV1Test.php` with new test methods.
- **PHPUnit Configuration**: Ensure a `phpunit.xml` file in the project root includes the test directory:
  ```xml
  <phpunit>
      <testsuites>
          <testsuite name="OAuthV1 Tests">
              <directory>tests</directory>
          </testsuite>
      </testsuites>
      <php>
          <includePath>vendor/autoload.php</includePath>
      </php>
  </phpunit>
  ```
- **Directory Structure**: The test file is located at `tests/OAuthV1Test.php` to match the namespace `Tests`. Adjust the path in the PHPUnit command if your project uses a different structure.
- **Dependency**: The `anibalealvarezs/oauth-v1` package is assumed to have no external dependencies beyond PHP’s standard library. Faker is required for test data generation.
