<?php

namespace App\Http\Controllers\Backend\Api;

use App\Http\Controllers\Controller;
use App\Models\Backend\Api\ApiClient;
use App\Models\Backend\Api\ApiLog;
use App\Models\Backend\Merchant;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class ApiClientController extends Controller
{
    public function index()
    {
        $clients = ApiClient::with('merchant')->latest()->paginate(20);
        return view('backend.api_clients.index', compact('clients'));
    }

    public function create()
    {
        $merchants = Merchant::with('user')->where('status', 1)->get();
        return view('backend.api_clients.create', compact('merchants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:191',
            'merchant_id' => 'required|exists:merchants,id',
        ]);

        ApiClient::create([
            'client_name' => $request->client_name,
            'api_key' => ApiClient::generateApiKey(),
            'merchant_id' => $request->merchant_id,
            'status' => $request->has('status') ? 1 : 0,
            'allow_duplicate_invoice' => $request->has('allow_duplicate_invoice') ? 1 : 0,
        ]);

        Toastr::success('API Client created successfully');
        return redirect()->route('api-clients.index');
    }

    public function edit($id)
    {
        $client = ApiClient::findOrFail($id);
        $merchants = Merchant::with('user')->where('status', 1)->get();
        return view('backend.api_clients.edit', compact('client', 'merchants'));
    }

    public function update(Request $request, $id)
    {
        $client = ApiClient::findOrFail($id);

        $request->validate([
            'client_name' => 'required|string|max:191',
            'merchant_id' => 'required|exists:merchants,id',
        ]);

        $client->update([
            'client_name' => $request->client_name,
            'merchant_id' => $request->merchant_id,
            'status' => $request->has('status') ? 1 : 0,
            'allow_duplicate_invoice' => $request->has('allow_duplicate_invoice') ? 1 : 0,
        ]);

        Toastr::success('API Client updated successfully');
        return redirect()->route('api-clients.index');
    }

    public function regenerateKey($id)
    {
        $client = ApiClient::findOrFail($id);
        $client->update(['api_key' => ApiClient::generateApiKey()]);

        Toastr::success('API Key regenerated successfully');
        return redirect()->route('api-clients.index');
    }

    public function toggleStatus($id)
    {
        $client = ApiClient::findOrFail($id);
        $client->update(['status' => !$client->status]);

        $statusText = $client->status ? 'activated' : 'deactivated';
        Toastr::success("API Client {$statusText} successfully");
        return redirect()->route('api-clients.index');
    }

    public function destroy($id)
    {
        $client = ApiClient::findOrFail($id);
        $client->delete();

        Toastr::success('API Client deleted successfully');
        return redirect()->route('api-clients.index');
    }

    public function logs(Request $request)
    {
        $query = ApiLog::with('apiClient')->latest();

        if ($request->filled('client_id')) {
            $query->where('api_client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);
        $clients = ApiClient::all();

        return view('backend.api_logs.index', compact('logs', 'clients'));
    }

    public function logDetail($id)
    {
        $log = ApiLog::with('apiClient')->findOrFail($id);
        return view('backend.api_logs.detail', compact('log'));
    }
}
