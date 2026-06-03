@extends('backend.partials.master')
@section('title')
Edit API Client
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
                            <li class="breadcrumb-item"><a href="{{ route('api-clients.index') }}" class="breadcrumb-link">API Clients</a></li>
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link active">Edit</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit API Client: {{ $client->client_name }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Current API Key:</strong>
                        <code>{{ $client->api_key }}</code>
                    </div>

                    <form action="{{ route('api-clients.update', $client->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="client_name">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" id="client_name" class="form-control @error('client_name') is-invalid @enderror" value="{{ old('client_name', $client->client_name) }}" required>
                            @error('client_name')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="merchant_id">Linked Merchant <span class="text-danger">*</span></label>
                            <select name="merchant_id" id="merchant_id" class="form-control @error('merchant_id') is-invalid @enderror" required>
                                <option value="">Select Merchant</option>
                                @foreach($merchants as $merchant)
                                <option value="{{ $merchant->id }}" {{ old('merchant_id', $client->merchant_id) == $merchant->id ? 'selected' : '' }}>
                                    {{ $merchant->business_name }} ({{ $merchant->user->name ?? 'N/A' }})
                                </option>
                                @endforeach
                            </select>
                            @error('merchant_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="status" name="status" {{ $client->status ? 'checked' : '' }}>
                                <label class="custom-control-label" for="status">Active</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="allow_duplicate_invoice" name="allow_duplicate_invoice" {{ $client->allow_duplicate_invoice ? 'checked' : '' }}>
                                <label class="custom-control-label" for="allow_duplicate_invoice">Allow Duplicate Invoice IDs</label>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Client
                            </button>
                            <a href="{{ route('api-clients.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
