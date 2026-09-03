<?php

namespace App\Helpers;

use App\Models\Cart;
use App\Models\Driver;
use Cache;
use DB;
use Image;
use Request;
use Route;
use Validator;
use Schema;
use Session;
use Storage;
use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FireNotification;
use Kreait\Firebase\Exception\MessagingException;
use Illuminate\Support\Facades\Log;

class CustomHelper
{
    public static function NotifyMultipleUsers($tokens,  $title, $body, $data)
    {
        $message = CloudMessage::new()
            ->withNotification(FireNotification::create($title, $body))
            ->withData($data)
            ->withAndroidConfig([
                'priority' => 'high',
            ])
            ->withApnsConfig([
                'headers' => [
                    'apns-priority' => '10',
                ],
            ]);

        try {
            // Send the message to multiple device tokens
            $messaging = (new Factory)
                ->withServiceAccount(public_path('serviceAccountKey.json'))
                ->withProjectId('hopon-f5697')
                ->createMessaging();

            $response = $messaging->sendMulticast($message, $tokens);
            Log::error("message send successfully:" . json_encode($response));
            // Handle success and failures
            if ($response->hasFailures()) {
                foreach ($response->failures()->getItems() as $failure) {
                    $failedToken = $failure->target()->value();
                    $error = $failure->error()->getMessage();
                    Log::error("Failed to send notification to token: $failedToken, Error: $error");
                }
            } else {
                Log::info('Notification sent successfully to all tokens.');
            }
        } catch (MessagingException $e) {
            Log::error('Error sending notification: ' . $e->getMessage());
        }
    }

    // upload Image Base 64
    public static function UploadImageBase64($destinationPath, $DBPath, $ImageData, $flagName)
    {
        $imageBase64 = base64_decode($ImageData);
        $filename = $flagName . time() . rand(5, 1500) . '.jpg';
        $ImagePath = $destinationPath . $filename;
        $DBImagePath = $DBPath . $filename;
        $file = $destinationPath . $filename;
        if (file_put_contents($file, $imageBase64)) {
            return $DBImagePath;
        }
    }

    // upload Image file From data
    public static function UploadImageFile($destinationPath, $DBPath, $KeyName, $flagName)
    {
        if (Request::hasFile($KeyName)) {
            $image = Request::file($KeyName);
            $name = $flagName . time() . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $name);
            return $DBPath . $name;
        }
        return null;
    }


    public static function Notify($deviceToken, $title, $messageBody, $data)
    {
        try {
            $serviceAccountPath = asset('serviceAccountKey.json');
            $accessToken = self::getAccessToken($serviceAccountPath);
            $projectId = 'hopon-f5697';

            // Notification message
            $message = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $messageBody,
                    ],
                    "data" => $data
                ],
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

            $response = curl_exec($ch);
            curl_close($ch);
            // Output the response
            return $response;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function getAccessToken($serviceAccountPath)
    {
        try {
            $credentials = json_decode(file_get_contents($serviceAccountPath), true);
            $jwtHeader = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $jwtClaimSet = json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => time() + 3600,
                'iat' => time(),
            ]);

            $jwt = base64_encode($jwtHeader) . '.' . base64_encode($jwtClaimSet);
            $signature = '';
            openssl_sign($jwt, $signature, html_entity_decode($credentials['private_key']), 'SHA256');
            $jwt .= '.' . base64_encode($signature);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]));

            $response = curl_exec($ch);
            curl_close($ch);
            // dd($response);
            $data = json_decode($response, true);
            return $data['access_token'];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function CheckUserExits($id = "")
    {
        $request = Request::all();
        if ($id) {
            $CheckUser = User::where('id', $id)->first(['id as userId', 'users.*']);
        } else {
            $CheckUser = User::where('id', $request['userId'])->first(['id as userId', 'users.*']);
        }
        if ($CheckUser) {
            return $CheckUser;
        } else {
            return false;
        }
    }

    public static function SuccessResponse($message, $data, $newKey = '')
    {
        $data = [
            'responseCode' => 1,
            'responseText' => $message,
            'responseData' => $data,
        ];
        if (is_array($newKey)) {
            if (
                count($newKey) > 0
            ) {
                foreach ($newKey as $key => $value) {
                    $data[$key] = !is_array($value) ? (string) $value : $value;
                }
            }
        }
        return response()->json($data);
    }

    public static function SuccessWithoutData($message, $newKey = '')
    {
        $data = [
            'responseCode' => 1,
            'responseText' => $message,
        ];
        if (is_array($newKey)) {
            if (
                count($newKey) > 0
            ) {
                foreach ($newKey as $key => $value) {
                    $data[$key] = (string) $value;
                }
            }
        }
        return response()->json($data);
    }

    public static function ErrorResponse($message, $newKey = '')
    {
        $data = [
            'responseCode' => 0,
            'responseText' => $message,
        ];
        if (is_array($newKey)) {
            if (
                count($newKey) > 0
            ) {
                foreach ($newKey as $key => $value) {
                    $data[$key] = (string) $value;
                }
            }
        }
        return response()->json($data);
    }


    public static function ValidateField($keyArray)
    {
        $validator = Validator::make(Request::all(), $keyArray);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    if ($key == "password" && strpos($error, "format") !== false) {
                        return "Password must contain at least one number, one alphabet, one capital, one special character, and be a minimum of 6 characters long.";
                    }
                    return $error;
                }
            }
        }
    }


    public static function transformKey($key)
    {
        $parts = explode('_', $key);
        $result = $parts[0];
        for ($i = 1; $i < count($parts); $i++) {
            $result .= ucfirst(substr($parts[$i], 0, 1)) . substr($parts[$i], 1);
        }
        return $result;
    }

    public static function transformValue($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'transformValue'], $value);
        }
        if (stripos($value, '.jpg') !== false || stripos($value, '.png') !== false || stripos($value, '.gif') !== false || stripos($value, '.mp4') !== false) {
            return (string) asset($value);
        } else {
            return (string) $value;
        }
    }

    public static function ConvertCapitalizeObject($data)
    {
        $keys = array_map([self::class, 'transformKey'], array_keys($data));
        $values = array_map([self::class, 'transformValue'], $data);
        $transformedData = array_combine($keys, $values);
        return $transformedData;
    }

    public static function capitalizeKeys($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        $result = [];
        foreach ($data as $key => $value) {
            $parts = explode('_', $key);
            $partsone = $parts[0];
            for ($i = 1; $i < count($parts); $i++) {
                $partsone .= ucfirst(substr($parts[$i], 0, 1)) . substr($parts[$i], 1);
            }
            $newKey = $partsone;
            $result[$partsone] = self::capitalizeKeys($value);
        }
        return $result;
    }

    public static function convertToString($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'convertToString'], $value);
        } else {
            if (stripos($value, '.jpg') !== false || stripos($value, '.png') !== false || stripos($value, '.gif') !== false || stripos($value, '.mp4') !== false || stripos($value, '.webp') !== false || stripos($value, '.jpeg') !== false) {
                return (string) asset($value);
            } else {
                if (stripos($value, '.000000Z') !== false) {
                    $date = new \DateTime($value);
                    // Format the date as YYMMDD
                    $outputDateString = $date->format('d M,Y H:i');
                    return (string) $outputDateString;
                }
                if (stripos($value, '.0000') !== false) {
                    return number_format((float) $value, 2);
                }
                return (string) $value;
            }
        }
    }

    public static function CapitalizeArray($data)
    {
        $capitalizedData = self::capitalizeKeys($data);
        $transformedData = self::convertToString($capitalizedData);
        return $transformedData;
    }


    public static function calculateFare($distance, $gasCostPerLiter, $fuelEfficiency)
    {
        // Cost per kilometer
        $costPerKm = $gasCostPerLiter / $fuelEfficiency;
        // Total cost calculation
        $totalCost = $distance * $costPerKm;
        // Return result
        return $totalCost;
    }
}
