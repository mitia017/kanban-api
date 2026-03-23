<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    protected $fillable = ['kanban_id', 'title', 'order'];

    public function kanban(): BelongsTo
    {
        return $this->belongsTo(kanban::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }
}
