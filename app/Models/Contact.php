<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = [
        'company_id',
        'assigned_to',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'deal_value',
        'expected_close_date',
        'notes',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Note::class)->latest();
    }

    protected function casts(): array
    {
        return [
            'deal_value' => 'decimal:2',
            'expected_close_date' => 'date',
        ];
    }
}
