<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'website',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
