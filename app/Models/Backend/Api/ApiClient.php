<?php

namespace App\Models\Backend\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Merchant;

class ApiClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'api_key',
        'status',
        'allow_duplicate_invoice',
        'merchant_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'allow_duplicate_invoice' => 'boolean',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function logs()
    {
        return $this->hasMany(ApiLog::class);
    }

    public static function generateApiKey(): string
    {
        return bin2hex(random_bytes(32));
    }
}
