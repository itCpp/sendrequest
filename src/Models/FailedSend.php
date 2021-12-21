<?php

namespace ItCpp\Sendrequest\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedSend extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_count',
        'request_data',
        'response_code',
        'response_data',
        'fail_at',
    ];

    /**
     * The attributes that should be cast
     *
     * @var array
     */
    protected $casts = [
        'request_data' => 'object',
        'response_data' => 'object',
        'fail_at' => 'datetime',
    ];
}
