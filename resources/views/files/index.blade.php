@extends('layouts.app')

@section('title', 'Gestão de Ficheiros Seguros')
@section('page-title', 'Os Meus Ficheiros')

@section('content')
{{-- ── Cabeçalho da página ──────────────────────────────── --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 style="font-size:1.35rem;font-weight:700;margin:0 0 .2rem;color:var(--scd-text);">Cofre de Ficheiros</h2>
        <p style="margin:0;font-size:.85rem;color:var(--scd-text-muted);">Todos os ficheiros estão protegidos com criptografia AES-256.</p>
    </div>
    <div>
        <button class="btn-scd-primary w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload me-2"></i>Upload Seguro
        </button>
    </div>
</div>

{{-- ── Mensagens flash ──────────────────────────────────── --}}
@if (session('status'))
    <div class="alert-scd-success mb-3 anim-fade-up" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
    </div>
@endif
@if($errors->any())
    <div class="alert-scd-danger mb-3" role="alert">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Tabela de ficheiros — estilo "Last File" da imagem ── --}}
<div class="scd-card">
    {{-- Cabeçalho do card --}}
    <div style="padding:1rem 1.4rem;border-bottom:1px solid var(--scd-border);display:flex;align-items:center;justify-content:space-between;">
        <h3 style="font-size:.9rem;font-weight:600;margin:0;color:var(--scd-text);">Todos os Ficheiros</h3>
        <span style="font-size:.78rem;color:var(--scd-text-muted);">{{ $files->total() }} ficheiro(s)</span>
    </div>

    <div class="table-responsive">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--scd-border);">
                    <th style="padding:.75rem 1.4rem;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--scd-text-muted);text-align:left;">Nome do Ficheiro</th>
                    <th style="padding:.75rem 1rem;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--scd-text-muted);">Tamanho</th>
                    <th style="padding:.75rem 1rem;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--scd-text-muted);">Tipo</th>
                    <th style="padding:.75rem 1rem;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--scd-text-muted);">Proprietário</th>
                    <th style="padding:.75rem 1rem;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--scd-text-muted);">Data</th>
                    <th style="padding:.75rem 1.4rem;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--scd-text-muted);text-align:right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                <tr style="border-bottom:1px solid var(--scd-border);transition:background .15s;" onmouseover="this.style.background='var(--scd-surface-2)'" onmouseout="this.style.background='transparent'">
                    {{-- Nome do ficheiro com ícone colorido --}}
                    <td style="padding:.85rem 1.4rem;">
                        <div style="display:flex;align-items:center;gap:.8rem;">
                            {{-- Ícone de ficheiro colorido (inspirado nos ícones coloridos da imagem) --}}
                            <div style="width:36px;height:36px;border-radius:8px;background:rgba(91,79,207,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-file-earmark-lock2-fill" style="color:var(--scd-accent);font-size:.95rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:.88rem;font-weight:500;color:var(--scd-text);">{{ $file->name }}</div>
                                <div style="font-size:.74rem;color:var(--scd-text-muted);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $file->original_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:.85rem 1rem;font-size:.86rem;color:var(--scd-text-muted);">{{ number_format($file->size / 1024, 1) }} KB</td>
                    <td style="padding:.85rem 1rem;">
                        <span style="background:var(--scd-surface-2);border:1px solid var(--scd-border);border-radius:6px;padding:.2rem .6rem;font-size:.74rem;color:var(--scd-text-muted);font-weight:500;">
                            {{ Str::limit($file->mime_type, 18) }}
                        </span>
                    </td>
                    <td style="padding:.85rem 1rem;font-size:.86rem;color:var(--scd-text);">
                        {{-- Avatar do proprietário --}}
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div style="width:26px;height:26px;border-radius:50%;background:var(--scd-primary);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#fff;flex-shrink:0;">
                                {{ strtoupper(substr($file->user->name ?? 'S', 0, 1)) }}
                            </div>
                            <span style="font-size:.84rem;color:var(--scd-text);">{{ $file->user->name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td style="padding:.85rem 1rem;font-size:.84rem;color:var(--scd-text-muted);">{{ $file->created_at->format('d M, Y') }}</td>
                    <td style="padding:.85rem 1.4rem;text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:.4rem;">
                            {{-- Botão partilhar --}}
                            <button style="background:rgba(91,79,207,.08);border:1px solid rgba(91,79,207,.18);color:var(--scd-accent);border-radius:6px;padding:.3rem .7rem;font-size:.8rem;cursor:pointer;transition:background .15s;"
                                    onmouseover="this.style.background='rgba(91,79,207,.16)'" onmouseout="this.style.background='rgba(91,79,207,.08)'"
                                    data-bs-toggle="modal" data-bs-target="#shareModal{{ $file->id }}">
                                <i class="bi bi-share"></i>
                            </button>
                            {{-- Botão descarregar --}}
                            <a href="{{ route('files.download', $file) }}"
                               style="background:rgba(126,184,164,.1);border:1px solid rgba(126,184,164,.22);color:var(--scd-primary-dark);border-radius:6px;padding:.3rem .7rem;font-size:.8rem;transition:background .15s;display:inline-flex;align-items:center;"
                               onmouseover="this.style.background='rgba(126,184,164,.2)'" onmouseout="this.style.background='rgba(126,184,164,.1)'">
                                <i class="bi bi-download"></i>
                            </a>
                            {{-- Botão eliminar --}}
                            <form action="{{ route('files.destroy', $file) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Tem a certeza que deseja eliminar permanentemente este ficheiro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="background:rgba(229,62,62,.08);border:1px solid rgba(229,62,62,.18);color:var(--scd-danger);border-radius:6px;padding:.3rem .7rem;font-size:.8rem;cursor:pointer;transition:background .15s;"
                                        onmouseover="this.style.background='rgba(229,62,62,.16)'" onmouseout="this.style.background='rgba(229,62,62,.08)'">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:3rem;text-align:center;color:var(--scd-text-muted);">
                        <i class="bi bi-folder-x" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.25;"></i>
                        <div style="font-size:.9rem;font-weight:500;margin-bottom:.3rem;">Nenhum ficheiro encontrado</div>
                        <div style="font-size:.82rem;">O seu cofre está vazio. Faça o upload do seu primeiro ficheiro.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    @if($files->hasPages())
    <div style="padding:.85rem 1.4rem;border-top:1px solid var(--scd-border);">
        {{ $files->links() }}
    </div>
    @endif
</div>

{{-- ── Modal de Upload ── --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:1px solid var(--scd-border);box-shadow:0 8px 32px rgba(0,0,0,0.12);">
            <form id="uploadForm" action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="padding:1.3rem 1.4rem;border-bottom:1px solid var(--scd-border);display:flex;align-items:center;justify-content:space-between;">
                    <h5 id="uploadModalLabel" style="margin:0;font-size:.95rem;font-weight:600;color:var(--scd-text);">
                        <i class="bi bi-shield-lock-fill me-2" style="color:var(--scd-primary);"></i>Upload Seguro
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div style="padding:1.3rem 1.4rem;">
                    <div style="background:rgba(126,184,164,.1);border:1px solid rgba(126,184,164,.25);border-radius:8px;padding:.75rem 1rem;display:flex;align-items:center;gap:.6rem;margin-bottom:1.2rem;font-size:.84rem;color:var(--scd-text-muted);">
                        <i class="bi bi-info-circle" style="color:var(--scd-primary);"></i>
                        O ficheiro será encriptado com AES-256 antes de ser guardado.
                    </div>

                    <div class="mb-3">
                        <label for="upload_name" class="form-label">Nome de Apresentação <small class="text-muted">(Opcional)</small></label>
                        <input type="text" class="form-control" id="upload_name" name="name" placeholder="Ex: Relatório Financeiro Q3">
                    </div>

                    <div class="mb-1">
                        <label for="upload_file" class="form-label">Ficheiro *</label>
                        <input class="form-control" type="file" id="upload_file" name="file" required>
                        <small class="text-muted">Tamanho máximo: 50 MB.</small>
                    </div>
                </div>

                <div style="padding:.9rem 1.4rem;border-top:1px solid var(--scd-border);display:flex;justify-content:flex-end;gap:.6rem;">
                    <button type="button" class="btn-scd-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnUploadSubmit" class="btn-scd-primary">
                        <i class="bi bi-lock-fill"></i> Encriptar e Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



            {{-- Overlay de progresso (cobre o modal quando o upload começa) --}}
            <div id="uploadProgressOverlay"
                 style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;
                        background:var(--scd-surface);z-index:10;
                        align-items:center;justify-content:center;flex-direction:column;
                        padding:2.5rem;text-align:center;">
                <i class="bi bi-shield-lock" style="font-size:2.5rem;color:var(--scd-primary);margin-bottom:1rem;"></i>
                <h5 id="progressTitle" style="margin-bottom:.4rem;font-weight:700;color:var(--scd-text);">A preparar segurança...</h5>
                <p id="progressDesc" style="font-size:.85rem;color:var(--scd-text-muted);margin-bottom:1.5rem;">A iniciar rotina AES-256</p>
                <div style="width:100%;background:var(--scd-border);border-radius:10px;height:8px;overflow:hidden;margin-bottom:.5rem;">
                    <div id="progressBar" style="height:100%;width:0%;background:var(--scd-primary);transition:width 0.4s ease;"></div>
                </div>
                <div id="progressPercent" style="font-size:.75rem;color:var(--scd-text-muted);font-weight:600;">0%</div>
            </div>

        </div>
    </div>
</div>

{{-- Modais de Partilha fora da tabela --}}
@foreach($files as $file)
<div class="modal fade" id="shareModal{{ $file->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:1px solid var(--scd-border);box-shadow:0 8px 32px rgba(0,0,0,0.12);">

            {{-- Cabeçalho do modal --}}
            <div style="padding:1.3rem 1.4rem;border-bottom:1px solid var(--scd-border);display:flex;align-items:center;justify-content:space-between;">
                <h5 style="margin:0;font-size:.95rem;font-weight:600;color:var(--scd-text);">
                    <i class="bi bi-share-fill me-2" style="color:var(--scd-accent);"></i>Partilhar Ficheiro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- Formulário principal de nova partilha --}}
            <form action="{{ route('files.share', $file) }}" method="POST">
                @csrf
                <div style="padding:1.3rem 1.4rem;">
                    <p style="font-size:.84rem;color:var(--scd-text-muted);margin-bottom:1.2rem;">
                        A partilhar: <strong style="color:var(--scd-text);">{{ $file->name }}</strong>
                    </p>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Partilha</label>
                        <select class="form-select" name="share_type" id="share_type_{{ $file->id }}" onchange="toggleShareFields('{{ $file->id }}')">
                            <option value="user">Utilizador Único (por Email)</option>
                            <option value="department">Departamento Inteiro</option>
                        </select>
                    </div>

                    <div class="mb-3" id="user_field_{{ $file->id }}">
                        <label for="email_{{ $file->id }}" class="form-label">Email do Utilizador *</label>
                        <input type="email" class="form-control" id="email_{{ $file->id }}" name="email" placeholder="exemplo@empresa.com" required>
                    </div>

                    <div class="mb-3 d-none" id="dept_field_{{ $file->id }}">
                        <label for="department_id_{{ $file->id }}" class="form-label">Departamento *</label>
                        <select class="form-select" id="department_id_{{ $file->id }}" name="department_id">
                            <option value="">Selecione um departamento...</option>
                            @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="padding:.9rem 1.4rem;border-top:1px solid var(--scd-border);display:flex;justify-content:flex-end;gap:.6rem;">
                    <button type="button" class="btn-scd-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-scd-primary">
                        <i class="bi bi-check-lg"></i> Confirmar Partilha
                    </button>
                </div>
            </form>

            {{-- Partilhas activas — formulários de revogar FORA do form principal --}}
            @if($file->shares->count() > 0)
            <div style="padding:0 1.4rem 1.2rem;">
                <hr style="border-color:var(--scd-border);margin:0 0 .9rem;">
                <h6 style="font-size:.82rem;font-weight:600;color:var(--scd-text);margin-bottom:.75rem;">
                    <i class="bi bi-people-fill me-1" style="color:var(--scd-text-muted);"></i>Partilhas Activas
                </h6>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.4rem;">
                    @foreach($file->shares as $share)
                    <li style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .7rem;background:var(--scd-surface-2);border-radius:8px;font-size:.83rem;">
                        <span style="color:var(--scd-text-muted);">
                            @if($share->shared_with_user_id)
                                <i class="bi bi-person me-1"></i>{{ $share->sharedWith->email }}
                            @else
                                <i class="bi bi-building me-1"></i>{{ $share->department->name }}
                            @endif
                        </span>
                        <form action="{{ route('shares.destroy', $share) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Revogar este acesso?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none;border:none;color:var(--scd-danger);cursor:pointer;padding:.2rem .4rem;font-size:.85rem;">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    /* Alterna os campos do modal de partilha entre "utilizador" e "departamento" */
    function toggleShareFields(fileId) {
        const typeSelect = document.getElementById('share_type_' + fileId);
        const userField  = document.getElementById('user_field_' + fileId);
        const deptField  = document.getElementById('dept_field_' + fileId);
        const emailInput = document.getElementById('email_' + fileId);
        const deptSelect = document.getElementById('department_id_' + fileId);

        if (typeSelect.value === 'user') {
            userField.classList.remove('d-none');
            deptField.classList.add('d-none');
            emailInput.setAttribute('required', 'required');
            deptSelect.removeAttribute('required');
        } else {
            userField.classList.add('d-none');
            deptField.classList.remove('d-none');
            emailInput.removeAttribute('required');
            deptSelect.setAttribute('required', 'required');
        }
    }
    /* ── Upload: mostrar spinner no botão ao submeter ── */
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function () {
            const btn = document.getElementById('btnUploadSubmit');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>A encriptar...';
            }
        });
    }
</script>
@endpush
@endsection
