<?php

namespace App\Http\Controllers\Backend\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateParcelRequest;
use App\Models\Backend\Api\ApiClient;
use App\Models\Backend\Api\ApiLog;
use App\Models\Backend\Parcel;
use App\Models\Backend\ParcelLogs;
use App\Models\District;
use App\Models\City;
use App\Enums\ParcelStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ExternalParcelController extends Controller
{
    public function create(CreateParcelRequest $request)
    {
        $apiClient = $request->get('api_client');

        if (!$apiClient->allow_duplicate_invoice) {
            $existingParcel = Parcel::where('invoice_no', $request->invoice_id)
                ->whereHas('merchant', function ($q) use ($apiClient) {
                    $q->where('id', $apiClient->merchant_id);
                })
                ->first();

            if ($existingParcel) {
                $response = [
                    'success' => false,
                    'message' => 'A parcel with this Invoice ID already exists',
                    'data' => [
                        'tracking_id' => $existingParcel->tracking_id,
                        'invoice_id' => $existingParcel->invoice_no,
                        'status' => $existingParcel->status_name,
                    ],
                ];

                $this->logRequest($request, $apiClient->id, $response, 'duplicate', 409);
                return response()->json($response, 409);
            }
        }

        if (!$apiClient->merchant_id) {
            $response = [
                'success' => false,
                'message' => 'API client is not linked to a merchant. Please contact admin.',
            ];
            $this->logRequest($request, $apiClient->id, $response, 'error', 400);
            return response()->json($response, 400);
        }

        $merchant = \App\Models\Backend\Merchant::with('user')->find($apiClient->merchant_id);
        if (!$merchant) {
            $response = [
                'success' => false,
                'message' => 'Linked merchant not found. Please contact admin.',
            ];
            $this->logRequest($request, $apiClient->id, $response, 'error', 400);
            return response()->json($response, 400);
        }

        $district = District::where('sector', 'like', '%' . $request->district . '%')->first();
        $city = City::where('name', 'like', '%' . $request->city . '%')->first();

        try {
            DB::beginTransaction();

            $parcel = new Parcel();
            $parcel->merchant_id        = $apiClient->merchant_id;
            $parcel->first_hub_id       = $merchant->user->hub_id;
            $parcel->hub_id             = $merchant->user->hub_id;
            $parcel->invoice_no         = $request->invoice_id;
            $parcel->pickup_phone       = $request->sender_phone;
            $parcel->pickup_address     = $request->sender_address;
            $parcel->customer_name      = $request->receiver_name;
            $parcel->customer_phone     = $request->receiver_phone;
            $parcel->customer_address   = $request->receiver_address;
            $parcel->district_id        = $district ? $district->id : null;
            $parcel->city_id            = $city ? $city->id : null;
            $parcel->weight             = $request->weight ?? 0;
            $parcel->cash_collection    = $request->cod_amount ?? 0;
            $parcel->delivery_charge    = $request->delivery_charge ?? 0;
            $parcel->total_delivery_amount = $request->delivery_charge ?? 0;
            $parcel->current_payable    = ($request->cod_amount ?? 0) - ($request->delivery_charge ?? 0);
            $parcel->note               = $request->remarks;
            $parcel->status             = ParcelStatus::PENDING;
            $parcel->pickup_date        = date('Y-m-d');
            $parcel->delivery_date      = date('Y-m-d', strtotime('+1 day'));
            $parcel->save();

            $trackingId = $this->generateTrackingId($apiClient->merchant_id, $district, $parcel->id);
            $parcel->tracking_id = $trackingId;
            $parcel->save();

            $log = new ParcelLogs();
            $log->merchant_id           = $apiClient->merchant_id;
            $log->hub_id                = $merchant->user->hub_id;
            $log->parcel_id             = $parcel->id;
            $log->pickup_address        = $request->sender_address;
            $log->pickup_phone          = $request->sender_phone;
            $log->customer_name         = $request->receiver_name;
            $log->customer_phone        = $request->receiver_phone;
            $log->customer_address      = $request->receiver_address;
            $log->invoice_no            = $request->invoice_id;
            $log->cash_collection       = $request->cod_amount ?? 0;
            $log->total_delivery_amount = $request->delivery_charge ?? 0;
            $log->current_payable       = ($request->cod_amount ?? 0) - ($request->delivery_charge ?? 0);
            $log->note                  = $request->remarks;
            $log->save();

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Parcel created successfully',
                'data' => [
                    'tracking_id' => $parcel->tracking_id,
                    'invoice_id' => $parcel->invoice_no,
                    'status' => 'Created',
                ],
            ];

            $this->logRequest($request, $apiClient->id, $response, 'success', 201);
            return response()->json($response, 201);

        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'success' => false,
                'message' => 'Failed to create parcel. Please try again.',
            ];

            $this->logRequest($request, $apiClient->id, $response, 'error', 500);
            return response()->json($response, 500);
        }
    }

    private function generateTrackingId($merchantId, $district, $parcelId): string
    {
        $prefix = Str::upper(settings()->par_track_prefix ?? 'DP');
        $districtCode = 'GEN';

        if ($district && !empty($district->sector)) {
            $districtCode = strtoupper(substr($district->sector, 0, 3));
        }

        return $prefix . $merchantId . $districtCode . $parcelId;
    }

    private function logRequest($request, $clientId, array $response, string $status, int $httpCode)
    {
        ApiLog::create([
            'api_client_id' => $clientId,
            'endpoint' => $request->path(),
            'request_payload' => $request->except(['api_client']),
            'response_payload' => $response,
            'ip_address' => $request->ip(),
            'status' => $status,
            'http_status_code' => $httpCode,
        ]);
    }
}
