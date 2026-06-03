@extends('backend.partials.master')
@section('title')
API Clients
@endsection
@section('maincontent')
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}" class="breadcrumb-link">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link active">API Clients</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">API Clients</h5>
                    <a href="{{ route('api-clients.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Client
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Client Name</th>
                                    <th>Merchant</th>
                                    <th>API Key</th>
                                    <th>Status</th>
                                    <th>Duplicate Invoice</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $client)
                                <tr>
                                    <td>{{ $client->id }}</td>
                                    <td>{{ $client->client_name }}</td>
                                    <td>{{ $client->merchant ? $client->merchant->business_name : 'N/A' }}</td>
                                    <td>
                                        <div class="input-group input-group-sm" style="max-width: 320px;">
                                            <input type="text" class="form-control" value="{{ $client->api_key }}" id="apiKey{{ $client->id }}" readonly style="font-size: 11px;">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyApiKey({{ $client->id }})">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($client->status)
                                            <span class="badge badge-pill badge-success">Active</span>
                                        @else
                                            <span class="badge badge-pill badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($client->allow_duplicate_invoice)
                                            <span class="badge badge-pill badge-warning">Allowed</span>
                                        @else
                                            <span class="badge badge-pill badge-info">Blocked</span>
                                        @endif
                                    </td>
                                    <td>{{ $client->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('api-clients.edit', $client->id) }}" class="btn btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('api-clients.toggle-status', $client->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn {{ $client->status ? 'btn-warning' : 'btn-success' }}" title="{{ $client->status ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas {{ $client->status ? 'fa-ban' : 'fa-check' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('api-clients.regenerate-key', $client->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to regenerate the API key? The old key will stop working immediately.')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-secondary" title="Regenerate Key">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('api-clients.destroy', $client->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this API client?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No API clients found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyApiKey(id) {
    var input = document.getElementById('apiKey' + id);
    input.select();
    document.execCommand('copy');
    alert('API Key copied to clipboard!');
}
</script>
@endsection
