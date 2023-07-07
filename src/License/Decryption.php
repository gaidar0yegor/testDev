<?php

namespace App\License;

use App\License\Exception\DecryptionException;
use App\License\Exception\PublicKeyNotFoundException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Decryption
{
    private HttpClientInterface $httpClient;

    private string $licensePublicKeyUrl;

    private string $licensePublicKeyFilename;

    public function __construct(
        HttpClientInterface $httpClient,
        string $licensePublicKeyUrl,
        string $licensePublicKeyFilename
    ) {
        $this->httpClient = $httpClient;
        $this->licensePublicKeyUrl = $licensePublicKeyUrl;
        $this->licensePublicKeyFilename = $licensePublicKeyFilename;
    }

    public function getDownloadUrl(): string
    {
        return $this->licensePublicKeyUrl;
    }

    public function getPublicKeyFilename(): string
    {
        return $this->licensePublicKeyFilename;
    }

    public function hasPublicKey(): bool
    {
        return file_exists($this->licensePublicKeyFilename);
    }

    public function downloadPublicKey(): void
    {
        $response = $this->httpClient->request('GET', $this->licensePublicKeyUrl, ['timeout' => 5]);
        $publicKeyContent = $response->getContent();

        file_put_contents($this->licensePublicKeyFilename, $publicKeyContent);
    }

    public function publicDecryptBinary(string $cryptedData): string
    {
        if (!$this->hasPublicKey()) {
            $this->downloadPublicKey();
        }

        $publicKey = openssl_pkey_get_public('file://'.$this->licensePublicKeyFilename);

        if (false === $publicKey) {
            throw new PublicKeyNotFoundException($this->licensePublicKeyFilename);
        }

        openssl_public_decrypt($cryptedData, $decrypted, $publicKey);
        openssl_free_key($publicKey);

        if (null === $decrypted) {
            throw new DecryptionException(
                'Impossible to decrypt this license file with the given public key.'
                .' Maybe this license is encrypted with another private key,'
                .' or is not a license file.'
            );
        }

        return $decrypted;
    }

    /**
     * Convert from chunked base64 to binary data,
     * and decrypt with public key.
     */
    public function publicDecrypt(string $cryptedData): string
    {
        $cryptedData = str_replace(PHP_EOL, '', $cryptedData);
        $cryptedData = base64_decode($cryptedData);
        $cryptedData = $this->publicDecryptBinary($cryptedData);

        return $cryptedData;
    }
}
