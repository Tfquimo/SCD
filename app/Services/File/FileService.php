<?php

namespace App\Services\File;

use App\Models\File;
use App\Models\User;
use App\Services\Crypto\CryptoServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use RuntimeException;

class FileService
{
    private CryptoServiceInterface $cryptoService;
    private const STORAGE_DISK = 'local';
    private const ENCRYPTED_DIR = 'encrypted_files';

    public function __construct(CryptoServiceInterface $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    /**
     * Store and encrypt an uploaded file.
     *
     * @param UploadedFile $uploadedFile The incoming file.
     * @param User $user The user uploading the file.
     * @param string|null $customName A custom name for the file, defaults to original name.
     * @return File
     */
    public function storeFile(UploadedFile $uploadedFile, User $user, ?string $customName = null): File
    {
        $originalName = $uploadedFile->getClientOriginalName();
        $name = $customName ?: $originalName;
        $mimeType = $uploadedFile->getClientMimeType() ?: 'application/octet-stream';
        $size = $uploadedFile->getSize();

        // 1. Move uploaded file to a temporary location safely.
        // We use a local temp file. UploadedFile is already in a temp path, but we can't always rely on it staying there.
        $tempPath = tempnam(sys_get_temp_dir(), 'scd_upload_');
        copy($uploadedFile->getRealPath(), $tempPath);

        // 2. Generate a secure random filename for the encrypted payload
        $encryptedFilename = Str::uuid()->toString() . '.enc';
        $encryptedRelativePath = self::ENCRYPTED_DIR . '/' . $encryptedFilename;
        $encryptedAbsolutePath = Storage::disk(self::STORAGE_DISK)->path($encryptedRelativePath);

        // Ensure directory exists
        if (!Storage::disk(self::STORAGE_DISK)->exists(self::ENCRYPTED_DIR)) {
            Storage::disk(self::STORAGE_DISK)->makeDirectory(self::ENCRYPTED_DIR);
        }

        try {
            // 3. Generate a DEK (Data Encryption Key) of 32 bytes (AES-256)
            $dek = random_bytes(32);
            $encryptedDek = Crypt::encryptString(base64_encode($dek));

            // 4. Encrypt the file using the DEK
            $this->cryptoService->encryptFile($tempPath, $encryptedAbsolutePath, $dek);

            // 5. Create the File record with KEK-encrypted DEK
            $fileRecord = File::create([
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'name' => $name,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size' => $size,
                'path' => $encryptedRelativePath,
                'encryption_key' => $encryptedDek,
            ]);

            return $fileRecord;
        } finally {
            // 5. Clean up the plaintext temporary file securely
            $this->secureDelete($tempPath);
        }
    }

    /**
     * Decrypt a file to a temporary location for download.
     * 
     * @param File $file The file model.
     * @return string The absolute path to the decrypted temporary file.
     */
    public function decryptForDownload(File $file): string
    {
        $encryptedAbsolutePath = Storage::disk(self::STORAGE_DISK)->path($file->path);

        if (!file_exists($encryptedAbsolutePath)) {
            throw new RuntimeException("O ficheiro físico não foi encontrado no servidor.");
        }

        $tempDecryptedPath = tempnam(sys_get_temp_dir(), 'scd_download_');

        try {
            $dek = null;
            if ($file->encryption_key) {
                // Decrypt the DEK from the KEK (APP_KEY)
                $dek = base64_decode(Crypt::decryptString($file->encryption_key));
            }

            $this->cryptoService->decryptFile($encryptedAbsolutePath, $tempDecryptedPath, $dek);
            return $tempDecryptedPath;
        } catch (\Exception $e) {
            $this->secureDelete($tempDecryptedPath);
            throw $e;
        }
    }

    /**
     * Delete a file completely.
     */
    public function deleteFile(File $file): bool
    {
        // Delete the physical encrypted file
        if (Storage::disk(self::STORAGE_DISK)->exists($file->path)) {
            Storage::disk(self::STORAGE_DISK)->delete($file->path);
        }

        // We use Force delete since we don't need soft deletes keeping the DB record 
        // if the physical file is destroyed (unless audit purposes require it).
        // Since we have AuditLogs, soft delete is fine. But we deleted physical data.
        return $file->delete();
    }

    /**
     * Securely overwrite and delete a temporary file.
     */
    private function secureDelete(string $path): void
    {
        if (file_exists($path) && is_file($path)) {
            $size = filesize($path);
            if ($size > 0) {
                $fp = fopen($path, 'r+b');
                if ($fp) {
                    // Overwrite with random bytes to prevent recovery from disk
                    // Note: on SSDs this is not 100% guaranteed due to wear leveling, but it's best practice
                    $chunk = 8192;
                    $written = 0;
                    while ($written < $size) {
                        $toWrite = min($chunk, $size - $written);
                        fwrite($fp, random_bytes($toWrite));
                        $written += $toWrite;
                    }
                    fclose($fp);
                }
            }
            unlink($path);
        }
    }
}
