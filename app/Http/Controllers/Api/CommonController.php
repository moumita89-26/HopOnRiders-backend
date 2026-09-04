<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CartType;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\Settings;
use App\Models\TripRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use function GuzzleHttp\json_decode;

class CommonController extends Controller
{
    public function getCharges()
    {
        $Charges = Settings::where('id', 1)->select('booking_fee', 'driver_payout_fee', 'trip_booking_fee')->first();

        return CustomHelper::SuccessResponse('Charges Fetch Successfully', CustomHelper::CapitalizeArray($Charges->toArray()));
    }

    public function cartTypeList()
    {
        $CartTypeList = CartType::get();

        return CustomHelper::SuccessResponse('Requested Ride Details Fetch Successfully', CustomHelper::CapitalizeArray($CartTypeList->toArray()));
    }

    public function cartDetails(Request $request)
    {
        $CartTypeList = User::where('id', $request->userId)->first(['vehicle_make', 'vehicle_model', 'vehicle_color', 'registration_number', 'number_of_seat', 'fuel_cost_per_km', 'car_image', 'ac', 'luggage', 'chargin', 'music', 'pets']);
        if (! $CartTypeList->vehicle_make) {
            return CustomHelper::ErrorResponse('Data not fonud');
        }

        return CustomHelper::SuccessResponse('Car Details Fetch Successfully', CustomHelper::CapitalizeArray($CartTypeList->toArray()));
    }

    public function driverDetails(Request $request)
    {
        $driverDetails = User::where('id', $request->userId)->first();
        $driverDetails->totalRide = Ride::where('driver_id', $request->userId)->where('status', 3)->count();
        $driverDetails->totalKM = 142;
        $review = Rating::with('userDetails')->where('driver_id', $request->userId)->get();

        return CustomHelper::SuccessResponse('Car Details Fetch Successfully', CustomHelper::CapitalizeArray($driverDetails->toArray()), ['review' => CustomHelper::CapitalizeArray($review->toArray())]);
    }

    public function countryCode(Request $request)
    {
        $codes = [
            [
                'code' => '+260',
                'country' => 'Zambian',
            ],

        ];

        return CustomHelper::SuccessResponse('Country Code Fetch Successfully', CustomHelper::CapitalizeArray($codes));
    }

    public function SOSSendSms(Request $request)
    {
        $userMobile = User::where('id', $request->userId)->first();
        if ($userMobile->emergency_number) {
            // send notification to admin
            $current_time = date('m-d-Y H:i');
            $locationLink = 'https://maps.google.com/?q='.$request->lat.','.$request->long;
            $response = Http::withHeaders([
                'X-Account-ID' => '1776',
                'X-API-Key' => '4de94ebaf03649c49fa573b74fed3e99',
                'Content-Type' => 'application/json',
            ])->post('https://api.esmsafrica.io/api/sms/send', [
                'phoneNumber' => $userMobile->emergency_number,
                'text' => 'SOS: I cannot speak right now. I need help immediately. Please come to my location or contact authorities. Time: '.$current_time.' Location: '.$locationLink,
                'senderId' => 'HopOnZambia',
            ]);
            if ($response->failed()) {
                if ($response->failed()) {
                    return CustomHelper::ErrorResponse(
                        'Message send failed',
                        [
                            'status' => $response->status(),
                            'body' => $response->body(),
                            'json' => $response->json(),
                        ]
                    );
                }

                return CustomHelper::ErrorResponse('SOS Send failed');
            }

            return CustomHelper::SuccessResponse('Message Sent Successfully', $response->json(), ['link' => $locationLink]);
        } else {
            // send notification to admin
            return CustomHelper::ErrorResponse('Message send failed no emergency number');
        }
    }

    public function SendSms(Request $request)
    {
        $otp = mt_rand(1000, 9999);
        $response = Http::withHeaders([
            'X-Account-ID' => '1776',
            'X-API-Key' => '4de94ebaf03649c49fa573b74fed3e99',
            // 'X-API-Key'    => '4ac17633b3064abe88f3b1dd02cef2fb',
            'Content-Type' => 'application/json',
        ])->post('https://api.esmsafrica.io/api/sms/send', [
            'phoneNumber' => $request->mobileNo,
            'text' => "OTP HopOn : {$otp} (valid for 10 mins). Don`t share this code.",
            'senderId' => 'HopOnZambia',
        ]);
        if ($response->failed()) {
            return CustomHelper::ErrorResponse($response->json());
        }

        return CustomHelper::SuccessResponse('Message Sent Successfully', $response->json(), ['otp' => $otp]);
    }

    // paymemt gateway integration
    public function MerchentLogin()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://openapi.digitalpaygo.com/portal/gateway/login/merchant', [
            'username' => 'Hopon',
            'password' => 'Hopon@2026',
        ]);
        if ($response->failed()) {
            return [
                'status' => $response->status(),
                'body' => $response->body(),
            ];
        }

        return json_decode($response->body());
    }

    public function MoneyCollection(Request $request)
    {
        $merchantToken = $this->MerchentLogin();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$merchantToken->access_token,
        ])->post('https://openapi.digitalpaygo.com/mno/airtel/collect', [
            'userRef' => 'Hopon',
            'mobileNumber' => '7845784578',
            'amount' => '100',
            'remark' => 'test',

        ]);
        if ($response->failed()) {
            return [
                'status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json(),
            ];
        }

        return [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json(),
        ];
    }

    private $baseUrl = 'https://openapi.digitalpaygo.com';

    private $username = 'Hopon';

    private $password = 'Hopon@2026';

    // 🔐 1. Login & Get Token
    public function login()
    {
        $response = Http::post($this->baseUrl.'/portal/gateway/login/merchant', [
            'username' => $this->username,
            'password' => $this->password,
        ]);
        $data = $response->json();

        return $data;
    }

    // 💰 2. Collect Payment
    public function collectMoney(Request $request)
    {
        if ($request->filled('provider')) {
            $request->merge(['provider' => strtolower((string) $request->input('provider'))]);
        }

        $validated = $request->validate([
            'provider' => 'nullable|string|in:airtel,mtn,zamtel',
            'mobileNumber' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0.01',
            'remark' => 'nullable|string|max:255',
            'userRef' => 'nullable|string|max:100',
        ]);

        // Step 1: Get Token
        $login = $this->login();

        if (! isset($login['access_token'])) {
            return response()->json([
                'status' => false,
                'message' => 'Login failed',
                'data' => $login,
            ]);
        }

        $token = $login['access_token'];
        $provider = $validated['provider'] ?? 'airtel';
        $userRef = $validated['userRef'] ?? 'HOPON_'.now()->format('YmdHis').'_'.random_int(1000, 9999);

        if (Payment::where('payment_reference', $userRef)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Merchant reference has already been used.',
            ], 409);
        }

        // Persist before contacting PayGo so an accepted collection can never be
        // left without a local reconciliation record.
        Payment::create([
            'payment_method' => 'mobile_money',
            'payment_provider' => $provider,
            'amount' => $validated['amount'],
            'status' => 'pending',
            'payment_reference' => $userRef,
        ]);

        // Step 2: Call Collect API
        $response = Http::withToken($token)->post(
            $this->baseUrl.'/mno/'.$provider.'/collect',
            [
                'header' => [
                    'userRef' => $userRef,
                ],
                'mobileNumber' => $validated['mobileNumber'],
                'amount' => $validated['amount'],
                'remark' => $validated['remark'] ?? 'Payment from HopOn app',
            ]
        );

        $data = $response->json() ?? [];
        if (! $response->successful() && ! data_get($data, 'status')) {
            $data['status'] = 'FAILED';
        }

        $paymentRequest = clone $request;
        $paymentRequest->merge([
            'provider' => $provider,
            'amount' => $validated['amount'],
            'merRef' => data_get($data, 'header.userRef', $userRef),
            'paygoReqRef' => data_get($data, 'header.reqRef'),
        ]);
        $this->storePaymentResult($paymentRequest, $data);

        $accepted = $response->successful() && ! in_array(strtoupper((string) data_get($data, 'status')), ['FAIL', 'FAILED', 'ERROR'], true);

        return response()->json([
            'status' => $accepted,
            'provider' => $provider,
            'merchantReference' => $userRef,
            'data' => $data,
        ], $response->successful() ? 200 : $response->status());
    }

    // Verify an Airtel, MTN or Zamtel mobile-money account before collection.
    public function lookupMnoAccount(Request $request)
    {
        if ($request->filled('provider')) {
            $request->merge(['provider' => strtolower((string) $request->input('provider'))]);
        }

        $validated = $request->validate([
            'provider' => 'required|string|in:airtel,mtn,zamtel',
            'mobileNumber' => 'required|string|max:20',
            'userRef' => 'nullable|string|max:100',
        ]);

        $login = $this->login();
        if (! isset($login['access_token'])) {
            return response()->json([
                'status' => false,
                'message' => 'Login failed',
                'data' => $login,
            ]);
        }

        $userRef = $validated['userRef'] ?? 'HOPON_LOOKUP_'.now()->format('YmdHis').'_'.random_int(1000, 9999);
        $response = Http::withToken($login['access_token'])->post(
            $this->baseUrl.'/mno/'.$validated['provider'].'/lookup',
            [
                'header' => ['userRef' => $userRef],
                'mobileNumber' => $validated['mobileNumber'],
            ]
        );

        $data = $response->json();
        $accepted = $response->successful() && ! in_array(strtoupper((string) data_get($data, 'status')), ['FAIL', 'FAILED', 'ERROR'], true);

        return response()->json([
            'status' => $accepted,
            'provider' => $validated['provider'],
            'merchantReference' => $userRef,
            'data' => $data,
        ], $response->successful() ? 200 : $response->status());
    }

    // 🔍 3. Check Payment Status
    public function statusMoney(Request $request)
    {
        $login = $this->login();

        if (! isset($login['access_token'])) {
            return response()->json([
                'status' => false,
                'message' => 'Login failed',
                'data' => $login,
            ]);
        }

        $token = $login['access_token'];

        $response = Http::withToken($token)->post(
            $this->baseUrl.'/mno/query/txn/mno',
            [
                'paygoReqRef' => $request->paygoReqRef,
                'merRef' => $request->merRef,
            ]
        );

        $data = $response->json();
        $this->storePaymentResult($request, $data);

        return $data;
    }

    public function statusCallback(Request $request)
    {
        $login = $this->login();
        if (! isset($login['access_token'])) {
            return response()->json([
                'status' => false,
                'message' => 'Login failed',
                'data' => $login,
            ]);
        }
        $token = $login['access_token'];
        $response = Http::withToken($token)->post(
            $this->baseUrl.'/mno/query/txn/mno',
            [
                'paygoReqRef' => $request->paygoReqRef,
                'merRef' => $request->merRef,
            ]
        );
        $data = $response->json();
        $this->storePaymentResult($request, $data);

        return $data;
    }

    private function storePaymentResult(Request $request, array $response): void
    {
        $merchantReference = $request->input('merRef')
            ?? data_get($response, 'txn.merRef')
            ?? data_get($response, 'header.userRef')
            ?? data_get($response, 'data.header.userRef');
        $paygoRequestReference = $request->input('paygoReqRef')
            ?? data_get($response, 'txn.reqRef')
            ?? data_get($response, 'header.reqRef')
            ?? data_get($response, 'data.header.reqRef');
        $paygoTransactionReference = data_get($response, 'txn.mnoRef')
            ?? data_get($response, 'hostRef')
            ?? data_get($response, 'data.hostRef')
            ?? $paygoRequestReference;

        $hasBookingLookup = $request->filled('tripId') && $request->filled('userId');
        $hasPaymentReference = filled($merchantReference) || filled($paygoRequestReference) || filled($paygoTransactionReference);
        if (! $request->filled('bookingId') && ! $request->filled('tripRequestId') && ! $hasBookingLookup && ! $hasPaymentReference) {
            return;
        }

        $booking = $request->filled('bookingId')
            ? Booking::find($request->input('bookingId'))
            : ($hasBookingLookup
                ? Booking::where('trip_id', $request->input('tripId'))
                    ->where('passenger_id', $request->input('userId'))
                    ->latest('id')
                    ->first()
                : null);
        $tripRequest = $request->filled('tripRequestId') ? TripRequest::find($request->input('tripRequestId')) : null;

        if (! $booking && ! $tripRequest && ! $hasPaymentReference) {
            return;
        }

        $rawStatus = collect([
            data_get($response, 'txn.txnStatus'),
            data_get($response, 'txn.status'),
            data_get($response, 'txn.transactionStatus'),
            data_get($response, 'data.status'),
            data_get($response, 'transactionStatus'),
            data_get($response, 'data.transactionStatus'),
            data_get($response, 'responseCode'),
            data_get($response, 'data.responseCode'),
            data_get($response, 'status'),
        ])->first(fn ($value) => $value !== null && $value !== '');

        $normalized = strtolower((string) $rawStatus);
        $status = match (true) {
            in_array($normalized, ['0', '00', 's', 'success', 'successful', 'completed', 'paid'], true) => 'completed',
            in_array($normalized, ['e', 'f', 't', 'error', 'timeout', 'failed', 'failure', 'declined', 'cancelled', 'canceled'], true) => 'failed',
            default => 'pending',
        };

        try {
            $payment = $booking ? Payment::where('booking_id', $booking->id)->first() : null;
            if (! $payment && $tripRequest) {
                $payment = Payment::where('trip_request_id', $tripRequest->id)->first();
            }
            if (! $payment && filled($merchantReference)) {
                $payment = Payment::where('payment_reference', $merchantReference)->first();
            }
            if (! $payment && filled($paygoTransactionReference)) {
                $payment = Payment::where('paygo_transaction_reference', $paygoTransactionReference)->first();
            }
            if (! $payment && filled($paygoRequestReference)) {
                $payment = Payment::where('paygo_transaction_reference', $paygoRequestReference)->first();
            }

            $attributes = [
                'booking_id' => $booking?->id ?? $payment?->booking_id,
                'trip_request_id' => $tripRequest?->id ?? $payment?->trip_request_id,
                'trip_bid_id' => $request->input('tripBidId', $payment?->trip_bid_id),
                'payment_method' => 'mobile_money',
                'payment_provider' => $request->input('provider', $payment?->payment_provider ?? 'airtel'),
                'amount' => $booking?->total_fare
                    ?? $request->input('totalFare')
                    ?? $request->input('amount')
                    ?? data_get($response, 'txn.amount')
                    ?? $payment?->amount
                    ?? 0,
                'status' => $status,
                'payment_reference' => $merchantReference ?? $payment?->payment_reference,
                'paygo_transaction_reference' => $paygoTransactionReference ?? $payment?->paygo_transaction_reference,
            ];

            if ($payment) {
                $payment->update($attributes);
            } else {
                Payment::create($attributes);
            }
        } catch (\Throwable $exception) {
            // Preserve the existing PayGo API response if local persistence fails.
            report($exception);
        }
    }

    // end paymemt gateway integration

    // {
    //     dd(phpinfo());

    //     $url = 'https://api.esmsafrica.io/api/sms/send';
    //     $headers = [
    //         'X-Account-ID: 1776',
    //         'X-API-Key: 4ac17633b3064abe88f3b1dd02cef2fb',
    //         'Content-Type: application/json'
    //     ];
    //     $otp  = mt_rand(1000, 9999);
    //     $text = "OTP: " . $otp . " (valid for 10 mins). Don`t share this code.";
    //     $data = [
    //         'phoneNumber' => $request->mobileNo,
    //         'text' => $text,
    //         'senderId' => 'HopOnZambia'
    //     ];

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //     $response = curl_exec($ch);
    //     if (curl_errno($ch)) {
    //         return CustomHelper::ErrorResponse("Message Send Failed", CustomHelper::CapitalizeArray($ch));
    //     } else {
    //         return CustomHelper::SuccessResponse("Message Send Successfully", CustomHelper::CapitalizeArray($response), ['otp' => $otp]);
    //     }
    //     curl_close($ch);
    // }
}
