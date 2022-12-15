<?php

namespace App\Triats;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 */
trait HasUser
{
    public function user(): User
    {
        return $this->userRelation;
    }

    public function userRelation(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ????????
    public function isAuthourdBy(User $user): bool
    {
        return $this->user()->matches($user);
    }

    public function authoredBy(User $user)
    {
        return $this->userRelation()->associate($user);
    }
}