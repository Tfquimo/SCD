<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ─── Query base de ficheiros (scope por role) ─────────────────
        $filesQuery = File::query();
        if ($user->hasRole('Gestor') && !$user->hasRole('Admin')) {
            $filesQuery->where('department_id', $user->department_id);
        } elseif ($user->hasRole('Funcionário')) {
            $filesQuery->where('user_id', $user->id);
        }

        $totalFiles  = $filesQuery->count();
        $totalSizeKb = $filesQuery->sum('size') / 1024;
        $totalStorage = $totalSizeKb >= 1024
            ? round($totalSizeKb / 1024, 2) . ' MB'
            : round($totalSizeKb, 1) . ' KB';

        // ─── Métricas criptográficas ──────────────────────────────────
        // Total de ficheiros com DEK individualizada (Envelope Encryption)
        $deksActive = (clone $filesQuery)->whereNotNull('encryption_key')->count();

        // Ficheiros legados (antes do Envelope Encryption)
        $filesLegacy = $totalFiles - $deksActive;

        // ─── Auditoria ────────────────────────────────────────────────
        $auditQuery = AuditLog::query();
        if (!$user->hasRole('Admin')) {
            $auditQuery->where('user_id', $user->id);
        }
        $auditCount = $auditQuery->count();

        // Eventos de segurança críticos (falhas de login + locks)
        $securityEvents = AuditLog::whereIn('action', ['login_failed', 'account_locked'])
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // ─── Utilizadores ativos (Admin vê todos) ────────────────────
        $activeUsers = $user->hasRole('Admin') ? User::where('active', true)->count() : null;
        $lockedUsers = $user->hasRole('Admin') ? User::whereNotNull('locked_until')->where('locked_until', '>', now())->count() : null;

        // ─── Atividade semanal real para o gráfico ───────────────────
        $weeklyUploads = [];
        $weeklyLabels  = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $weeklyLabels[] = $day->locale('pt')->isoFormat('ddd D/M');
            $count = File::whereDate('created_at', $day->toDateString());
            if ($user->hasRole('Gestor') && !$user->hasRole('Admin')) {
                $count->where('department_id', $user->department_id);
            } elseif ($user->hasRole('Funcionário')) {
                $count->where('user_id', $user->id);
            }
            $weeklyUploads[] = $count->count();
        }

        // ─── Actividade recente ───────────────────────────────────────
        $recentActivity = AuditLog::with('user')
            ->when(!$user->hasRole('Admin'), fn($q) => $q->where('user_id', $user->id))
            ->latest('created_at')
            ->limit(8)
            ->get();

        // ─── Backups ─────────────────────────────────────────────────
        $backupPath   = storage_path('app/backups');
        $totalBackups = file_exists($backupPath) ? count(glob($backupPath . '/*.zip')) : 0;

        return view('dashboard', [
            // Stats gerais
            'totalFiles'       => $totalFiles,
            'storageUsed'      => $totalStorage,
            'auditEvents'      => $auditCount,
            'totalBackups'     => $totalBackups,
            // Cripto
            'deksActive'       => $deksActive,
            'filesLegacy'      => $filesLegacy,
            'securityEvents'   => $securityEvents,
            // Utilizadores (Admin)
            'activeUsers'      => $activeUsers,
            'lockedUsers'      => $lockedUsers,
            // Gráfico
            'weeklyUploads'    => $weeklyUploads,
            'weeklyLabels'     => $weeklyLabels,
            // Feed
            'recentActivities' => $recentActivity,
        ]);
    }
}
