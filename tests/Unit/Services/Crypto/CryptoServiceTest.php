<?php

namespace Tests\Unit\Services\Crypto;

use App\Services\Crypto\CryptoService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CryptoServiceTest extends TestCase
{
    private CryptoService $cryptoService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure a 32-byte key is set for testing
        Config::set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        
        $this->cryptoService = new CryptoService();
    }

    public function test_it_encrypts_and_decrypts_strings_successfully(): void
    {
        $plainText = 'Este é um texto super secreto 123!@#';

        $encrypted = $this->cryptoService->encryptString($plainText);

        $this->assertNotEquals($plainText, $encrypted);
        $this->assertIsString($encrypted);

        $decrypted = $this->cryptoService->decryptString($encrypted);

        $this->assertEquals($plainText, $decrypted);
    }

    public function test_it_throws_exception_on_invalid_mac_for_strings(): void
    {
        $plainText = 'Texto para ser adulterado';
        $encrypted = $this->cryptoService->encryptString($plainText);

        // Decode payload and tamper with it
        $payload = json_decode(base64_decode($encrypted), true);
        $payload['value'] = base64_encode(base64_decode($payload['value']) ^ '1'); // Flip a bit
        
        $tampered = base64_encode(json_encode($payload));

        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('MAC inválido. Os dados foram adulterados.');

        $this->cryptoService->decryptString($tampered);
    }

    public function test_it_encrypts_and_decrypts_files_successfully(): void
    {
        $sourceFile = storage_path('framework/testing/source.txt');
        $encryptedFile = storage_path('framework/testing/encrypted.dat');
        $decryptedFile = storage_path('framework/testing/decrypted.txt');

        // Create a test file (approx 2MB to test chunking)
        $content = str_repeat("Teste de ficheiro com bastante conteúdo para encriptação. ", 40000);
        file_put_contents($sourceFile, $content);

        // Encrypt
        $result = $this->cryptoService->encryptFile($sourceFile, $encryptedFile);
        $this->assertTrue($result);
        $this->assertFileExists($encryptedFile);
        $this->assertNotEquals(md5_file($sourceFile), md5_file($encryptedFile));

        // Decrypt
        $result = $this->cryptoService->decryptFile($encryptedFile, $decryptedFile);
        $this->assertTrue($result);
        $this->assertFileExists($decryptedFile);
        $this->assertEquals(md5_file($sourceFile), md5_file($decryptedFile));

        // Cleanup
        @unlink($sourceFile);
        @unlink($encryptedFile);
        @unlink($decryptedFile);
    }

    public function test_it_throws_exception_on_tampered_file_signature(): void
    {
        $sourceFile = storage_path('framework/testing/source2.txt');
        $encryptedFile = storage_path('framework/testing/encrypted2.dat');
        $decryptedFile = storage_path('framework/testing/decrypted2.txt');

        file_put_contents($sourceFile, "Conteúdo super secreto do ficheiro.");

        // Encrypt
        $this->cryptoService->encryptFile($sourceFile, $encryptedFile);

        // Tamper with the encrypted file (modify a byte in the middle)
        $fp = fopen($encryptedFile, 'r+b');
        fseek($fp, 20);
        fwrite($fp, 'X');
        fclose($fp);

        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('A integridade do ficheiro está comprometida (MAC inválido).');

        // Decrypt should fail
        $this->cryptoService->decryptFile($encryptedFile, $decryptedFile);

        // Cleanup
        @unlink($sourceFile);
        @unlink($encryptedFile);
        @unlink($decryptedFile);
    }
}
