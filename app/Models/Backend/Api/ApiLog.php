<?php

namespace App\Models\Backend\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_client_id',
        'endpoint',
        'request_payload',
        'response_payload',
        'ip_address',
        'status',
        'http_status_code',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function apiClient()
    {
        return $this->belongsTo(ApiClient::class);
    }
}
