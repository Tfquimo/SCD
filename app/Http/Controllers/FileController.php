<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\File\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display a listing of the user's/department's files.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Admin sees all, Managers see department, Employees see their own.
        // We'll enforce this via Eloquent query + Policies, but for simplicity:
        $query = File::query()->with(['user', 'department', 'shares.sharedWith', 'shares.department']);

        if ($user->hasRole('Admin')) {
            // Can see all
        } elseif ($user->hasRole('Gestor')) {
            // Can see all in their department
            $query->where('department_id', $user->department_id);
        } else {
            // Regular user sees only their own
            $query->where('user_id', $user->id);
        }

        $files = $query->latest()->paginate(10);

        return view('files.index', compact('files'));
    }

    /**
     * Store a newly uploaded file.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'], // max 50MB
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->fileService->storeFile(
                $request->file('file'),
                $request->user(),
                $request->input('name')
            );

            // Audit
            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'file_uploaded',
                'entity_type' => \App\Models\File::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'name' => $request->input('name') ?? $request->file('file')->getClientOriginalName(),
                    'message' => 'Ficheiro encriptado e carregado com sucesso.'
                ]
            ]);

            return redirect()->route('files.index')->with('status', 'Ficheiro carregado e encriptado com sucesso.');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Erro ao processar o ficheiro: ' . $e->getMessage()]);
        }
    }

    /**
     * Download and decrypt the file.
     */
    public function download(Request $request, File $file)
    {
        // Authorization: Ensure user can access this file
        if (!$this->canAccessFile($request->user(), $file)) {
            abort(403, 'Não tem permissões para aceder a este ficheiro.');
        }

        try {
            // Decrypt to a temporary file
            $tempPath = $this->fileService->decryptForDownload($file);

            // Audit
            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'file_downloaded',
                'entity_type' => \App\Models\File::class,
                'entity_id' => $file->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['message' => 'Ficheiro decriptado e descarregado.']
            ]);

            // Stream response and delete the temporary file after it is sent
            return response()->download($tempPath, $file->original_name, [
                'Content-Type' => $file->mime_type,
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Falha na decriptação ou integridade comprometida: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified file.
     */
    public function destroy(Request $request, File $file)
    {
        // Authorization
        if (!$this->canAccessFile($request->user(), $file)) {
            abort(403, 'Não tem permissões para apagar este ficheiro.');
        }

        try {
            $this->fileService->deleteFile($file);

            // Audit
            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'file_deleted',
                'entity_type' => \App\Models\File::class,
                'entity_id' => $file->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['message' => 'Ficheiro apagado.']
            ]);

            return redirect()->route('files.index')->with('status', 'Ficheiro eliminado com sucesso.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao eliminar o ficheiro: ' . $e->getMessage()]);
        }
    }

    /**
     * Simple access check logic (ideally goes in a Policy).
     */
    private function canAccessFile($user, File $file): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->hasRole('Gestor') && $user->department_id === $file->department_id) {
            return true;
        }

        return $user->id === $file->user_id;
    }
}
