<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_member_id', 'permission'])]
class PermissionGrant extends Model
{
    protected $table = 'event_permissions';

    public function member()
    {
        return $this->belongsTo(EventMember::class, 'event_member_id');
    }
}
