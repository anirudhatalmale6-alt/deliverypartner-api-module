@extends('backend.partials.master')
@section('title')
API Log Detail
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
                            <li class="breadcrumb-item"><a href="{{ route('api-logs.index') }}" class="breadcrumb-link">API Logs</a></li>
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link active">Log #{{ $log->id }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Request Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 35%;">Log ID</th>
                            <td>{{ $log->id }}</td>
                        </tr>
                        <tr>
                            <th>Client</th>
                            <td>{{ $log->apiClient ? $log->apiClient->client_name : 'Unknown / Unauthenticated' }}</td>
                        </tr>
                        <tr>
                            <th>Endpoint</th>
                            <td><code>{{ $log->endpoint }}</code></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($log->status == 'success')
                                    <span class="badge badge-success">Success</span>
                                @elseif($log->status == 'auth_failed')
                                    <span class="badge badge-danger">Auth Failed</span>
                                @elseif($log->status == 'duplicate')
                                    <span class="badge badge-warning">Duplicate</span>
                                @else
                                    <span class="badge badge-danger">Error</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>HTTP Status Code</th>
                            <td>{{ $log->http_status_code }}</td>
                        </tr>
                        <tr>
                            <th>IP Address</th>
                            <td>{{ $log->ip_address }}</td>
                        </tr>
                        <tr>
                            <th>Date/Time</th>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Request Payload</h5>
                </div>
                <div class="card-body">
                    <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 12px;">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Response Payload</h5>
                </div>
                <div class="card-body">
                    <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 12px;">{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
