<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = ['column_id', 'title', 'description', 'order', 'priority', 'assigned_to'];

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
