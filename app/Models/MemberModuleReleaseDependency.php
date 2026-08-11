<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberModuleReleaseDependency extends Model
{
    protected $fillable = ['member_module_id', 'required_member_module_id', 'minimum_progress_percent'];

    protected function casts(): array
    {
        return ['minimum_progress_percent' => 'integer'];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(MemberModule::class, 'member_module_id');
    }

    public function requiredModule(): BelongsTo
    {
        return $this->belongsTo(MemberModule::class, 'required_member_module_id');
    }
}
