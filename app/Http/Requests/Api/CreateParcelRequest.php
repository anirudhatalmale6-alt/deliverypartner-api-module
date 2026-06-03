<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateParcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id'        => 'required|string|max:100',
            'sender_name'       => 'required|string|max:191',
            'sender_phone'      => 'required|string|max:20',
            'sender_address'    => 'required|string|max:500',
            'receiver_name'     => 'required|string|max:191',
            'receiver_phone'    => 'required|string|max:20',
            'receiver_address'  => 'required|string|max:500',
            'city'              => 'required|string|max:100',
            'district'          => 'required|string|max:100',
            'parcel_description'=> 'nullable|string|max:500',
            'weight'            => 'nullable|numeric|min:0',
            'cod_amount'        => 'nullable|numeric|min:0',
            'delivery_charge'   => 'nullable|numeric|min:0',
            'remarks'           => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_id.required'       => 'Invoice ID is required',
            'sender_name.required'      => 'Sender name is required',
            'sender_phone.required'     => 'Sender phone number is required',
            'sender_address.required'   => 'Sender address is required',
            'receiver_name.required'    => 'Receiver name is required',
            'receiver_phone.required'   => 'Receiver phone number is required',
            'receiver_address.required' => 'Receiver address is required',
            'city.required'             => 'City is required',
            'district.required'         => 'District is required',
            'weight.numeric'            => 'Weight must be a number',
            'cod_amount.numeric'        => 'COD amount must be a number',
            'delivery_charge.numeric'   => 'Delivery charge must be a number',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
