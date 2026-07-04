<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileShare;
use App\Models\User;
use App\Models\Department;
use App\Models\AuditLog;
use App\Services\File\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FileShareController extends Controller
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display a listing of files shared with the current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get shares directly targeting this user OR their department
        $shares = FileShare::with(['file.user', 'sharedBy'])
            ->where(function ($q) use ($user) {
                $q->where('shared_with_user_id', $user->id);
                if ($user->department_id) {
                    $q->orWhere('department_id', $user->department_id);
                }
            })
            ->latest()
            ->paginate(15);

        return view('files.shared', compact('shares'));
    }

    /**
     * Share a file with a user or department.
     */
    public function store(Request $request, File $file)
    {
        $user = $request->user();

        // Authorization: only owner, department manager, or admin can share
        if (!$this->canManageFileShare($user, $file)) {
            abort(403, 'Não tem permissões para partilhar este ficheiro.');
        }

        $request->validate([
            'share_type' => ['required', 'in:user,department'],
            'email' => ['required_if:share_type,user', 'nullable', 'email', 'exists:users,email'],
            'department_id' => ['required_if:share_type,department', 'nullable', 'integer', 'exists:departments,id'],
        ]);

        $shareType = $request->input('share_type');
        $targetUserId = null;
        $targetDeptId = null;
        $auditMetadata = ['file_name' => $file->name];

        if ($shareType === 'user') {
            $targetUser = User::where('email', $request->input('email'))->firstOrFail();
            
            // Cannot share with oneself
            if ($targetUser->id === $user->id) {
                return back()->withErrors(['email' => 'Não pode partilhar um ficheiro consigo mesmo.']);
            }

            $targetUserId = $targetUser->id;
            $auditMetadata['shared_with_user'] = $targetUser->email;

            // Check if already shared with this user
            $exists = FileShare::where('file_id', $file->id)
                ->where('shared_with_user_id', $targetUserId)
                ->exists();
            if ($exists) {
                return back()->withErrors(['email' => 'Este ficheiro já foi partilhado com este utilizador.']);
            }
        } else {
            $targetDeptId = $request->input('department_id');
            $dept = Department::findOrFail($targetDeptId);
            $auditMetadata['shared_with_department'] = $dept->name;

            // Check if already shared with this department
            $exists = FileShare::where('file_id', $file->id)
                ->where('department_id', $targetDeptId)
                ->exists();
            if ($exists) {
                return back()->withErrors(['department_id' => 'Este ficheiro já foi partilhado com este departamento.']);
            }
        }

        $share = FileShare::create([
            'file_id' => $file->id,
            'shared_by_user_id' => $user->id,
            'shared_with_user_id' => $targetUserId,
            'department_id' => $targetDeptId,
        ]);

        // Send notification
        if ($shareType === 'user') {
            $targetUser->notify(new \App\Notifications\FileSharedNotification($file, $user));
        } else {
            $usersInDept = User::where('department_id', $targetDeptId)->get();
            \Illuminate\Support\Facades\Notification::send($usersInDept, new \App\Notifications\FileSharedNotification($file, $user));
        }

        // Audit
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'file_shared',
            'entity_type' => FileShare::class,
            'entity_id' => $share->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $auditMetadata
        ]);

        $targetName = $shareType === 'user' ? $request->input('email') : $dept->name;
        return redirect()->back()->with('status', "Ficheiro partilhado com sucesso com {$targetName}.");
    }

    /**
     * Download a shared file after verifying share permissions.
     */
    public function download(Request $request, File $file)
    {
        $user = $request->user();

        // Check if user has permission to download this shared file
        if (!$this->hasShareAccess($user, $file)) {
            abort(403, 'Acesso negado. Este ficheiro não foi partilhado consigo.');
        }

        try {
            $tempPath = $this->fileService->decryptForDownload($file);

            // Audit
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'shared_file_downloaded',
                'entity_type' => File::class,
                'entity_id' => $file->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['message' => 'Ficheiro partilhado descarregado.', 'file_name' => $file->name]
            ]);

            return response()->download($tempPath, $file->original_name, [
                'Content-Type' => $file->mime_type,
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Falha na decriptação do ficheiro partilhado: ' . $e->getMessage()]);
        }
    }

    /**
     * Revoke a file share.
     */
    public function destroy(Request $request, FileShare $share)
    {
        $user = $request->user();
        $file = $share->file;

        // Authorization: owner, admin, or the sharing user can revoke
        if ($user->id !== $share->shared_by_user_id && !$this->canManageFileShare($user, $file)) {
            abort(403, 'Não tem permissões para revogar esta partilha.');
        }

        $targetName = $share->shared_with_user_id ? $share->sharedWith->email : $share->department->name;
        $fileName = $file->name;

        $share->delete();

        // Audit
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'file_share_revoked',
            'entity_type' => FileShare::class,
            'entity_id' => $share->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['file_name' => $fileName, 'revoked_for' => $targetName]
        ]);

        return redirect()->back()->with('status', "Partilha com {$targetName} revogada com sucesso.");
    }

    /**
     * Helper to verify if user is allowed to share/manage shares of the file.
     */
    private function canManageFileShare($user, File $file): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->hasRole('Gestor') && $user->department_id === $file->department_id) {
            return true;
        }

        return $user->id === $file->user_id;
    }

    /**
     * Helper to check if a user has access to a file via sharing.
     */
    private function hasShareAccess($user, File $file): bool
    {
        // Owner/Admin/Gestor (if dept matches) have native access
        if ($user->hasRole('Admin')) {
            return true;
        }
        if ($user->id === $file->user_id) {
            return true;
        }
        if ($user->hasRole('Gestor') && $user->department_id === $file->department_id) {
            return true;
        }

        // Check if there is an active share for this user
        $userShare = FileShare::where('file_id', $file->id)
            ->where('shared_with_user_id', $user->id)
            ->exists();
        if ($userShare) {
            return true;
        }

        // Check if there is an active share for their department
        if ($user->department_id) {
            $deptShare = FileShare::where('file_id', $file->id)
                ->where('department_id', $user->department_id)
                ->exists();
            if ($deptShare) {
                return true;
            }
        }

        return false;
    }
}
