<?php

namespace App\Service;

class TokenGenerator
{
    /**
     * Generates random tokens that can be used in url.
     * I.e: "zl-R5gGDcBYw7pz7jniZWfUA69V_iEffqFQQatt-F54"
     */
    public function generateUrlToken(): string
    {
        $bytes = random_bytes(32);

        return sodium_bin2base64($bytes, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
    }
}
