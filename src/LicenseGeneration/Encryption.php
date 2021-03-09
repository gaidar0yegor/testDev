<?php

namespace App\LicenseGeneration;

use App\LicenseGeneration\Exception\EncryptionKeysException;
use RuntimeException;

/**
 * Encrypt license data with private and public keys.
 */
class Encryption
{
    private $privateKeyFile;

    private $publicKeyFile;

    public function __construct(string $privateKeyFile, string $publicKeyFile)
    {
        $this->privateKeyFile = $privateKeyFile;
        $this->publicKeyFile = $publicKeyFile;
    }

    /**
     * @throws EncryptionKeysException If private or public filename are empty.
     */
    private function checkKeys(): void
    {
        if ('' === $this->privateKeyFile || '' === $this->publicKeyFile) {
            throw new EncryptionKeysException(
                'Filenames of private and public keys are required to encrypt licenses content.'
            );
        }
    }

    public function privateEncryptBinary(string $data): string
    {
        $this->checkKeys();

        $privateKey = openssl_pkey_get_private('file://'.$this->privateKeyFile);

        if (false === $privateKey) {
            throw new EncryptionKeysException('Private key expected to be found at: '.$this->privateKeyFile);
        }

        openssl_private_encrypt($data, $crypted, $privateKey);
        openssl_free_key($privateKey);

        if (null === $crypted) {
            throw new RuntimeException(
                'Error while license encryption with the key at '
                .$this->privateKeyFile
                .' ; SSL error: '
                .openssl_error_string()
            );
        }

        return $crypted;
    }

    public function publicDecryptBinary(string $cryptedData): string
    {
        $this->checkKeys();

        $publicKey = openssl_pkey_get_public('file://'.$this->publicKeyFile);

        if (false === $publicKey) {
            throw new EncryptionKeysException('Public key expected to be found at: '.$this->publicKeyFile);
        }

        openssl_public_decrypt($cryptedData, $decrypted, $publicKey);
        openssl_free_key($publicKey);

        return $decrypted;
    }

    /**
     * Encrypt with private key (sign data),
     * and returns safe text (chunked base64) instead of binary.
     */
    public function privateEncrypt(string $data): string
    {
        $data = $this->privateEncryptBinary($data);
        $data = base64_encode($data);
        $data = chunk_split($data, 64, PHP_EOL);

        return $data;
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
