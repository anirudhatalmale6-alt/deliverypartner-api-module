# DeliveryPartner.lk External API v1

## Overview

This API allows third-party systems (ecommerce platforms, ERPs, POS systems) to create parcels directly in the DeliveryPartner.lk courier management system.

## Base URL

```
https://your-domain.com/api/v1
```

## Authentication

All API requests require Bearer token authentication using an API key generated from the admin panel.

```
Authorization: Bearer {YOUR_API_KEY}
Content-Type: application/json
Accept: application/json
```

### Getting an API Key

1. Login to the DeliveryPartner.lk admin panel
2. Navigate to API Management > API Clients in the sidebar
3. Click "Add New Client"
4. Fill in the client name and select the linked merchant
5. The API key will be generated automatically
6. Copy the API key and use it in your application

## Rate Limiting

- 60 requests per minute per API client
- HTTP 429 (Too Many Requests) returned when limit exceeded

---

## Endpoints

### POST /parcels/create

Create a new parcel in the system.

#### Request Headers

| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {API_KEY} | Yes |
| Content-Type | application/json | Yes |
| Accept | application/json | Yes |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| invoice_id | string | Yes | Your external Order/Invoice ID (max 100 chars) |
| sender_name | string | Yes | Sender/merchant name (max 191 chars) |
| sender_phone | string | Yes | Sender phone number (max 20 chars) |
| sender_address | string | Yes | Sender/pickup address (max 500 chars) |
| receiver_name | string | Yes | Receiver/customer name (max 191 chars) |
| receiver_phone | string | Yes | Receiver phone number (max 20 chars) |
| receiver_address | string | Yes | Receiver/delivery address (max 500 chars) |
| city | string | Yes | Delivery city name |
| district | string | Yes | Delivery district name |
| parcel_description | string | No | Description of parcel contents (max 500 chars) |
| weight | number | No | Parcel weight in kg (default: 0) |
| cod_amount | number | No | Cash on delivery amount in LKR (default: 0) |
| delivery_charge | number | No | Delivery charge in LKR (default: 0) |
| remarks | string | No | Additional notes (max 500 chars) |

#### Example Request

```bash
curl -X POST https://your-domain.com/api/v1/parcels/create \
  -H "Authorization: Bearer YOUR_API_KEY_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "invoice_id": "INV-10025",
    "sender_name": "ABC Online Store",
    "sender_phone": "0771234567",
    "sender_address": "123 Main Street, Colombo 03",
    "receiver_name": "John Perera",
    "receiver_phone": "0769876543",
    "receiver_address": "456 Temple Road, Kandy",
    "city": "Kandy",
    "district": "Kandy",
    "parcel_description": "Electronics - Mobile Phone",
    "weight": 0.5,
    "cod_amount": 25000.00,
    "delivery_charge": 350.00,
    "remarks": "Handle with care, fragile item"
  }'
```

#### Success Response (201 Created)

```json
{
    "success": true,
    "message": "Parcel created successfully",
    "data": {
        "tracking_id": "DP10KAN589",
        "invoice_id": "INV-10025",
        "status": "Created"
    }
}
```

#### Validation Error (422 Unprocessable Entity)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "receiver_phone": [
            "Receiver phone number is required"
        ],
        "city": [
            "City is required"
        ]
    }
}
```

#### Unauthorized (401)

```json
{
    "success": false,
    "message": "Invalid or missing API key"
}
```

#### Deactivated Client (403)

```json
{
    "success": false,
    "message": "API access has been deactivated"
}
```

#### Duplicate Invoice (409 Conflict)

When duplicate invoice prevention is enabled (default), attempting to create a parcel with the same invoice_id returns:

```json
{
    "success": false,
    "message": "A parcel with this Invoice ID already exists",
    "data": {
        "tracking_id": "DP10KAN589",
        "invoice_id": "INV-10025",
        "status": "Pending"
    }
}
```

#### Rate Limit Exceeded (429)

```json
{
    "message": "Too Many Attempts."
}
```

---

## Tracking ID Format

The system generates tracking IDs in the format: `{PREFIX}{MERCHANT_ID}{DISTRICT_CODE}{PARCEL_ID}`

- PREFIX: System-configured prefix (default: "DP")
- MERCHANT_ID: The linked merchant's internal ID
- DISTRICT_CODE: First 3 letters of the district sector name
- PARCEL_ID: Auto-incremented parcel database ID

Example: `DP10KAN589` = DP + Merchant 10 + Kandy (KAN) + Parcel 589

---

## Integration Examples

### PHP (using cURL)

```php
<?php
$apiKey = 'YOUR_API_KEY';
$url = 'https://your-domain.com/api/v1/parcels/create';

$data = [
    'invoice_id' => 'INV-' . time(),
    'sender_name' => 'My Store',
    'sender_phone' => '0771234567',
    'sender_address' => '123 Main St, Colombo',
    'receiver_name' => 'Customer Name',
    'receiver_phone' => '0769876543',
    'receiver_address' => '456 Temple Rd, Kandy',
    'city' => 'Kandy',
    'district' => 'Kandy',
    'cod_amount' => 5000.00,
    'delivery_charge' => 350.00,
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json',
    'Accept: application/json',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($result['success']) {
    echo 'Tracking ID: ' . $result['data']['tracking_id'];
} else {
    echo 'Error: ' . $result['message'];
}
```

### JavaScript (Node.js / Fetch)

```javascript
const apiKey = 'YOUR_API_KEY';
const url = 'https://your-domain.com/api/v1/parcels/create';

const response = await fetch(url, {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${apiKey}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    body: JSON.stringify({
        invoice_id: 'INV-10025',
        sender_name: 'My Store',
        sender_phone: '0771234567',
        sender_address: '123 Main St, Colombo',
        receiver_name: 'Customer Name',
        receiver_phone: '0769876543',
        receiver_address: '456 Temple Rd, Kandy',
        city: 'Kandy',
        district: 'Kandy',
        cod_amount: 5000.00,
        delivery_charge: 350.00,
    }),
});

const result = await response.json();

if (result.success) {
    console.log('Tracking ID:', result.data.tracking_id);
} else {
    console.error('Error:', result.message);
}
```

### Python (using requests)

```python
import requests

api_key = 'YOUR_API_KEY'
url = 'https://your-domain.com/api/v1/parcels/create'

data = {
    'invoice_id': 'INV-10025',
    'sender_name': 'My Store',
    'sender_phone': '0771234567',
    'sender_address': '123 Main St, Colombo',
    'receiver_name': 'Customer Name',
    'receiver_phone': '0769876543',
    'receiver_address': '456 Temple Rd, Kandy',
    'city': 'Kandy',
    'district': 'Kandy',
    'cod_amount': 5000.00,
    'delivery_charge': 350.00,
}

response = requests.post(url, json=data, headers={
    'Authorization': f'Bearer {api_key}',
    'Accept': 'application/json',
})

result = response.json()
if result['success']:
    print(f"Tracking ID: {result['data']['tracking_id']}")
else:
    print(f"Error: {result['message']}")
```

### WooCommerce Integration (WordPress Plugin Snippet)

```php
// Add to your WooCommerce order processing hook
add_action('woocommerce_order_status_processing', 'create_delivery_partner_parcel');

function create_delivery_partner_parcel($order_id) {
    $order = wc_get_order($order_id);
    
    $data = [
        'invoice_id'       => 'WC-' . $order_id,
        'sender_name'      => get_option('blogname'),
        'sender_phone'     => get_option('woocommerce_store_phone', ''),
        'sender_address'   => get_option('woocommerce_store_address', ''),
        'receiver_name'    => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
        'receiver_phone'   => $order->get_billing_phone(),
        'receiver_address' => $order->get_shipping_address_1() . ', ' . $order->get_shipping_city(),
        'city'             => $order->get_shipping_city(),
        'district'         => $order->get_shipping_state(),
        'cod_amount'       => $order->get_payment_method() === 'cod' ? $order->get_total() : 0,
        'delivery_charge'  => $order->get_shipping_total(),
        'remarks'          => $order->get_customer_note(),
    ];
    
    $response = wp_remote_post('https://your-domain.com/api/v1/parcels/create', [
        'body'    => json_encode($data),
        'headers' => [
            'Authorization' => 'Bearer YOUR_API_KEY',
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ],
    ]);
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($body['success']) {
        $order->update_meta_data('_dp_tracking_id', $body['data']['tracking_id']);
        $order->add_order_note('DeliveryPartner Tracking ID: ' . $body['data']['tracking_id']);
        $order->save();
    }
}
```

---

## Error Codes Summary

| HTTP Code | Meaning |
|-----------|---------|
| 201 | Parcel created successfully |
| 400 | Bad request (merchant not linked, etc.) |
| 401 | Invalid or missing API key |
| 403 | API access deactivated |
| 409 | Duplicate invoice ID |
| 422 | Validation failed |
| 429 | Rate limit exceeded |
| 500 | Server error |

---

## Admin Panel Features

### API Client Management
- Create/edit/delete API clients
- Generate and regenerate API keys
- Activate/deactivate API access
- Link clients to merchants
- Configure duplicate invoice handling per client

### API Logs
- View all API requests and responses
- Filter by client, status, date range
- View full request/response payloads
- Track IP addresses

---

## Future Endpoints (Planned)

- `GET /api/v1/parcels/track/{tracking_id}` - Track parcel status
- `GET /api/v1/parcels/status/{tracking_id}` - Get current parcel status
- `POST /api/v1/parcels/cancel/{tracking_id}` - Cancel a parcel
- `POST /api/v1/parcels/bulk-create` - Create multiple parcels
- `POST /api/v1/webhooks/register` - Register status update webhooks
- `GET /api/v1/settlements` - COD settlement reports
