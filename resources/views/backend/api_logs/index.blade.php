@extends('backend.partials.master')
@section('title')
API Logs
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
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link active">API Logs</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">API Request Logs</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('api-logs.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>API Client</label>
                                <select name="client_id" class="form-control">
                                    <option value="">All Clients</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->client_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                    <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Error</option>
                                    <option value="auth_failed" {{ request('status') == 'auth_failed' ? 'selected' : '' }}>Auth Failed</option>
                                    <option value="duplicate" {{ request('status') == 'duplicate' ? 'selected' : '' }}>Duplicate</option>
                                    <option value="validation" {{ request('status') == 'validation' ? 'selected' : '' }}>Validation</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="form-group col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('api-logs.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Client</th>
                                    <th>Endpoint</th>
                                    <th>Status</th>
                                    <th>HTTP Code</th>
                                    <th>IP Address</th>
                                    <th>Date/Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->apiClient ? $log->apiClient->client_name : 'Unknown' }}</td>
                                    <td><code>{{ $log->endpoint }}</code></td>
                                    <td>
                                        @if($log->status == 'success')
                                            <span class="badge badge-success">Success</span>
                                        @elseif($log->status == 'auth_failed')
                                            <span class="badge badge-danger">Auth Failed</span>
                                        @elseif($log->status == 'duplicate')
                                            <span class="badge badge-warning">Duplicate</span>
                                        @elseif($log->status == 'validation')
                                            <span class="badge badge-info">Validation</span>
                                        @else
                                            <span class="badge badge-danger">Error</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->http_status_code }}</td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('api-logs.detail', $log->id) }}" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No API logs found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
