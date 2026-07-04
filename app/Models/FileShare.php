<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'shared_by_user_id',
        'shared_with_user_id',
        'department_id',
    ];

    /**
     * Get the file being shared.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who shared the file.
     */
    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    /**
     * Get the user with whom the file is shared.
     */
    public function sharedWith(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }

    /**
     * Get the department with which the file is shared.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
