# DeliveryPartner.lk REST API Module - Installation Guide

## Step 1: Copy Files

Copy the following directories into your existing project (merge with existing structure):

```
app/Http/Controllers/Backend/Api/     -> 2 files (ExternalParcelController.php, ApiClientController.php)
app/Http/Middleware/                   -> 1 file (ApiKeyAuthMiddleware.php)
app/Http/Requests/Api/                -> 1 file (CreateParcelRequest.php)
app/Models/Backend/Api/               -> 2 files (ApiClient.php, ApiLog.php)
database/migrations/                  -> 2 files (api_clients and api_logs tables)
resources/views/backend/api_clients/  -> 3 files (index, create, edit blade views)
resources/views/backend/api_logs/     -> 2 files (index, detail blade views)
docs/                                 -> 2 files (API documentation + Postman collection)
```

## Step 2: Register Middleware

Open `app/Http/Kernel.php` and add this line to the `$middlewareAliases` array:

```php
'ApiKeyAuth' => \App\Http\Middleware\ApiKeyAuthMiddleware::class,
```

## Step 3: Add API Routes

Open `routes/api.php` and add at the bottom:

```php
/*
|--------------------------------------------------------------------------
| External API v1 - Third-Party Parcel Creation
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(['ApiKeyAuth', 'throttle:60,1'])->group(function () {
    Route::post('/parcels/create', [\App\Http\Controllers\Backend\Api\ExternalParcelController::class, 'create']);
});
```

## Step 4: Add Admin Routes

Open `routes/web.php` and add inside the admin route group (inside `Route::group(['prefix' => 'admin'], function () {`):

```php
// API Client Management
Route::get('api-clients',                    [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'index'])->name('api-clients.index');
Route::get('api-clients/create',             [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'create'])->name('api-clients.create');
Route::post('api-clients/store',             [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'store'])->name('api-clients.store');
Route::get('api-clients/edit/{id}',          [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'edit'])->name('api-clients.edit');
Route::put('api-clients/update/{id}',        [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'update'])->name('api-clients.update');
Route::patch('api-clients/toggle-status/{id}', [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'toggleStatus'])->name('api-clients.toggle-status');
Route::patch('api-clients/regenerate-key/{id}', [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'regenerateKey'])->name('api-clients.regenerate-key');
Route::delete('api-clients/delete/{id}',     [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'destroy'])->name('api-clients.destroy');
// API Logs
Route::get('api-logs',                       [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'logs'])->name('api-logs.index');
Route::get('api-logs/detail/{id}',           [\App\Http\Controllers\Backend\Api\ApiClientController::class, 'logDetail'])->name('api-logs.detail');
```

## Step 5: Add Sidebar Menu

Open `resources/views/backend/partials/sidebar.blade.php` and add before the closing `</ul>`:

```blade
<li class="nav-item">
    <a class="nav-link {{ request()->is('admin/api-clients*', 'admin/api-logs*') ? 'active' : '' }}"
        href="#" data-toggle="collapse" aria-expanded="false" data-target="#api-manage"
        aria-controls="api-manage"><i class="fas fa-plug"></i> API Management</a>
    <div id="api-manage"
        class="{{ request()->is('admin/api-clients*', 'admin/api-logs*') ? '' : 'collapse' }} submenu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/api-clients*') ? 'active' : '' }}"
                    href="{{ route('api-clients.index') }}">API Clients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/api-logs*') ? 'active' : '' }}"
                    href="{{ route('api-logs.index') }}">API Logs</a>
            </li>
        </ul>
    </div>
</li>
```

## Step 6: Run Migration

Run the database migration to create the two new tables:

```bash
php artisan migrate
```

Or visit: `https://your-domain.com/migrate`

This creates:
- `api_clients` table - stores API client details and keys
- `api_logs` table - stores all API request/response logs

## Step 7: Create First API Client

1. Login to admin panel
2. Go to API Management > API Clients
3. Click "Add New Client"
4. Enter client name and select the merchant
5. Copy the generated API key

## Step 8: Test the API

Use the included Postman collection (`docs/DeliveryPartner_API_v1_Postman.json`) or test with curl:

```bash
curl -X POST https://your-domain.com/api/v1/parcels/create \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "invoice_id": "TEST-001",
    "sender_name": "Test Store",
    "sender_phone": "0771234567",
    "sender_address": "123 Main St, Colombo",
    "receiver_name": "John Doe",
    "receiver_phone": "0769876543",
    "receiver_address": "456 Temple Rd, Kandy",
    "city": "Kandy",
    "district": "Kandy",
    "cod_amount": 1000,
    "delivery_charge": 350
  }'
```

## Notes

- The module uses the SAME tracking ID generation logic as the admin panel
- Each API client must be linked to a merchant (parcels are created under that merchant)
- Rate limiting is set to 60 requests/minute per client
- All requests and responses are logged in the api_logs table
- Duplicate invoice IDs are prevented by default (configurable per client)
