<?php

namespace App\Services\Crypto;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class CryptoService implements CryptoServiceInterface
{
    private const CIPHER = 'AES-256-CBC';
    private const HASH_ALGO = 'sha256';

    /**
     * The encryption key.
     */
    private string $key;

    public function __construct()
    {
        $key = Config::get('app.key');
        
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (empty($key) || strlen($key) !== 32) {
            throw new RuntimeException('SCD requer uma APP_KEY de 32 bytes (AES-256) configurada.');
        }

        $this->key = $key;
    }

    /**
     * Encrypt a string using AES-256-CBC and append a MAC for authenticity.
     */
    public function encryptString(string $value): string
    {
        $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));

        $value = \openssl_encrypt($value, self::CIPHER, $this->key, 0, $iv);

        if ($value === false) {
            throw new EncryptException('Não foi possível encriptar os dados.');
        }

        // Generate HMAC
        $mac = hash_hmac(self::HASH_ALGO, $iv . $value, $this->key);

        $json = json_encode([
            'iv' => base64_encode($iv),
            'value' => $value,
            'mac' => $mac
        ], JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Erro a codificar payload (JSON).');
        }

        return base64_encode($json);
    }

    /**
     * Decrypt a string and verify its authenticity via MAC.
     */
    public function decryptString(string $payload): string
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);
        $value = $payload['value'];

        $decrypted = \openssl_decrypt($value, self::CIPHER, $this->key, 0, $iv);

        if ($decrypted === false) {
            throw new DecryptException('Não foi possível decriptar os dados. Chave ou ficheiro inválido.');
        }

        return $decrypted;
    }

    /**
     * Validates the payload and MAC.
     */
    private function getJsonPayload(string $payload): array
    {
        $payload = json_decode(base64_decode($payload), true);

        if (! $this->validPayload($payload)) {
            throw new DecryptException('Payload de criptografia inválido.');
        }

        if (! $this->validMac($payload)) {
            throw new DecryptException('MAC inválido. Os dados foram adulterados.');
        }

        return $payload;
    }

    /**
     * Verify that the payload array contains the necessary keys.
     */
    private function validPayload(mixed $payload): bool
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']) &&
               strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length(self::CIPHER);
    }

    /**
     * Determine if the MAC for the given payload is valid.
     */
    private function validMac(array $payload): bool
    {
        $calcMac = $this->hash($payload['iv'], $payload['value']);

        return hash_equals($payload['mac'], $calcMac);
    }

    /**
     * Create a MAC for the given value.
     */
    private function hash(string $iv, string $value): string
    {
        return hash_hmac(self::HASH_ALGO, base64_decode($iv) . $value, $this->key);
    }

    /**
     * Encrypt a file chunk by chunk to avoid memory exhaustion on large files.
     */
    public function encryptFile(string $sourcePath, string $destinationPath, ?string $key = null): bool
    {
        $encryptionKey = $key ?? $this->key;
        if (! file_exists($sourcePath) || ! is_readable($sourcePath)) {
            throw new \InvalidArgumentException("O ficheiro de origem não existe ou não pode ser lido: $sourcePath");
        }

        $source = fopen($sourcePath, 'rb');
        $dest = fopen($destinationPath, 'wb');

        if (! $source || ! $dest) {
            throw new RuntimeException("Não foi possível abrir os ficheiros para encriptação.");
        }

        $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));
        fwrite($dest, $iv);

        $chunkSize = 8192; // Multiple of 16
        $currentIv = $iv;

        // Read the first chunk
        $currentChunk = fread($source, $chunkSize);

        while ($currentChunk !== false && $currentChunk !== '') {
            // Read ahead the next chunk to determine if current is the last one
            $nextChunk = fread($source, $chunkSize);

            if ($nextChunk === false || $nextChunk === '') {
                // This is the last chunk, use standard padding (PKCS7)
                $encryptedChunk = \openssl_encrypt($currentChunk, self::CIPHER, $encryptionKey, OPENSSL_RAW_DATA, $currentIv);
            } else {
                // Intermediate chunk, encrypt without padding (must be a multiple of 16)
                $encryptedChunk = \openssl_encrypt($currentChunk, self::CIPHER, $encryptionKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $currentIv);
                $currentIv = substr($encryptedChunk, -16);
            }

            if ($encryptedChunk === false) {
                fclose($source);
                fclose($dest);
                throw new EncryptException('Erro ao encriptar bloco do ficheiro.');
            }

            fwrite($dest, $encryptedChunk);
            $currentChunk = $nextChunk;
        }

        fclose($source);
        fclose($dest);

        // Add MAC to the end of the encrypted file for authenticity validation
        return $this->signFile($destinationPath);
    }

    /**
     * Decrypt a file chunk by chunk.
     */
    public function decryptFile(string $sourcePath, string $destinationPath, ?string $key = null): bool
    {
        $decryptionKey = $key ?? $this->key;
        if (! file_exists($sourcePath) || ! is_readable($sourcePath)) {
            throw new \InvalidArgumentException("O ficheiro encriptado não existe ou não pode ser lido: $sourcePath");
        }

        // Verify authenticity before decrypting
        if (! $this->verifyFileSignature($sourcePath)) {
            throw new DecryptException('A integridade do ficheiro está comprometida (MAC inválido).');
        }

        $source = fopen($sourcePath, 'rb');
        $dest = fopen($destinationPath, 'wb');

        if (! $source || ! $dest) {
            throw new RuntimeException("Não foi possível abrir os ficheiros para decriptação.");
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = fread($source, $ivLength);

        // File size without IV and MAC
        $fileSize = filesize($sourcePath);
        $dataSize = $fileSize - $ivLength - 64; 

        $chunkSize = 8192 + 16; // Read slightly larger chunks to handle padding correctly
        // Wait, since we wrote exact 8192 byte blocks (which are already multiple of 16),
        // the encrypted chunk size is exactly the same as plaintext size (8192) EXCEPT the last one!
        // So we can read exactly 8192 bytes.
        $chunkSize = 8192;
        $currentIv = $iv;
        $read = 0;

        while ($read < $dataSize) {
            $toRead = min($chunkSize, $dataSize - $read);
            $chunk = fread($source, $toRead);
            if ($chunk === false || $chunk === '') break;

            // If we are not at the very last chunk, we decrypt without padding
            if ($read + $toRead < $dataSize) {
                $decryptedChunk = \openssl_decrypt($chunk, self::CIPHER, $decryptionKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $currentIv);
                $currentIv = substr($chunk, -16);
            } else {
                // Last chunk, use standard padding (PKCS7)
                $decryptedChunk = \openssl_decrypt($chunk, self::CIPHER, $decryptionKey, OPENSSL_RAW_DATA, $currentIv);
            }

            if ($decryptedChunk === false) {
                fclose($source);
                fclose($dest);
                throw new DecryptException('Erro ao decriptar bloco do ficheiro. Ficheiro corrompido.');
            }

            fwrite($dest, $decryptedChunk);
            $read += $toRead;
        }

        fclose($source);
        fclose($dest);

        return true;
    }

    /**
     * Generate and append an HMAC signature to the end of the encrypted file.
     */
    private function signFile(string $filePath): bool
    {
        $ctx = hash_init(self::HASH_ALGO, HASH_HMAC, $this->key);
        hash_update_file($ctx, $filePath);
        $mac = hash_final($ctx);
        
        $fp = fopen($filePath, 'ab');
        if (! $fp) return false;
        
        fwrite($fp, $mac); // append 64 chars of sha256 hex string
        fclose($fp);
        
        return true;
    }

    /**
     * Verify the HMAC signature at the end of the encrypted file.
     */
    private function verifyFileSignature(string $filePath): bool
    {
        $size = filesize($filePath);
        if ($size < 64 + openssl_cipher_iv_length(self::CIPHER)) {
            return false;
        }

        // Read the last 64 bytes (MAC)
        $fp = fopen($filePath, 'rb');
        fseek($fp, -64, SEEK_END);
        $storedMac = fread($fp, 64);
        fclose($fp);

        // Calculate MAC for the file content except the last 64 bytes
        $ctx = hash_init(self::HASH_ALGO, HASH_HMAC, $this->key);
        
        $fp = fopen($filePath, 'rb');
        $dataSize = $size - 64;
        $read = 0;
        while (!feof($fp) && $read < $dataSize) {
            $buffer = fread($fp, min(8192, $dataSize - $read));
            hash_update($ctx, $buffer);
            $read += strlen($buffer);
        }
        fclose($fp);
        
        $calculatedMac = hash_final($ctx);

        return hash_equals($storedMac, $calculatedMac);
    }
}
