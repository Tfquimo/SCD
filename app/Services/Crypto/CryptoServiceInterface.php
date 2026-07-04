<?php

namespace App\Services\Crypto;

interface CryptoServiceInterface
{
    /**
     * Encrypt a string value using AES-256-CBC.
     *
     * @param string $value The plain text value to encrypt.
     * @return string The base64 encoded encrypted string (includes IV and MAC).
     */
    public function encryptString(string $value): string;

    /**
     * Decrypt a string value using AES-256-CBC.
     *
     * @param string $payload The base64 encoded payload.
     * @return string The decrypted plain text.
     * @throws \Illuminate\Contracts\Encryption\DecryptException If the payload is invalid or MAC check fails.
     */
    public function decryptString(string $payload): string;

    /**
     * Encrypt a file securely chunk by chunk using AES-256-CBC.
     *
     * @param string $sourcePath The absolute path to the plain text file.
     * @param string $destinationPath The absolute path where the encrypted file will be saved.
     * @param string|null $key Optional DEK. If null, the APP_KEY will be used.
     * @return bool True if successful.
     */
    public function encryptFile(string $sourcePath, string $destinationPath, ?string $key = null): bool;

    /**
     * Decrypt a file securely chunk by chunk using AES-256-CBC.
     *
     * @param string $sourcePath The absolute path to the encrypted file.
     * @param string $destinationPath The absolute path where the plain text file will be saved.
     * @param string|null $key Optional DEK. If null, the APP_KEY will be used.
     * @return bool True if successful.
     * @throws \Illuminate\Contracts\Encryption\DecryptException If MAC check fails or file is corrupt.
     */
    public function decryptFile(string $sourcePath, string $destinationPath, ?string $key = null): bool;
}
