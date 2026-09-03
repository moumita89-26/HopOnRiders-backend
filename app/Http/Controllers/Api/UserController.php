<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CommonHelper;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            $language = $request->language ?? 'en';
            // where('is_verified', 1)->
            $userCheck = User::where('phone', $request->phone)->orWhere('email', $request->phone)->first();
            if ($userCheck) {
                if (Hash::check($request->password, $userCheck->password)) {
                    $userDetails = CustomHelper::CheckUserExits($userCheck->id);
                    if ($request->deviceToken) {
                        User::where('id', $userCheck->id)->update([
                            "device_token" => $request->deviceToken,
                            "device_type" => $request->deviceType,
                        ]);
                    }
                    return CustomHelper::SuccessResponse(trans('Login successfully.', [], $language), CustomHelper::CapitalizeArray($userDetails->toArray()));
                }
                return CustomHelper::ErrorResponse(trans('Your phone and password are wrong.', [], $language));
            } else {
                return CustomHelper::ErrorResponse(trans('Your phone and password are wrong or your account is not activated.', [], $language));
            }
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse(trans('Something Wrong Please try again.', [], $language));
        }
    }

    public function register(Request $request)
    {
        try {
            $language = $request->language ?? 'en';
            $data = User::where('email', $request->email)->first();
            if (!empty($data)) {
                return CustomHelper::ErrorResponse(trans('Email Already Registered.', [], $language));
            } else {
                $MobileData = User::where('phone', $request->phone)->first();
                if (!empty($MobileData)) {
                    return CustomHelper::ErrorResponse(trans('The mobile number already exists!', [], $language));
                } else {
                    $destinationPath = public_path('users/Document/');
                    $DBPath = 'users/Document/';
                    $destinationPath = public_path('users/Document/');
                    $DBPath = 'users/Document/';
                    $nrcFront = '';
                    if ($request->nrcFront) {
                        $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'nrcFront', 'nrcFront-');
                        $nrcFront = $imagePath;
                    }
                    $nrcBack = '';
                    if ($request->nrcBack) {
                        $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'nrcBack', 'nrcBack-');
                        $nrcBack = $imagePath;
                    }

                    $licenseBack = '';
                    if ($request->licenseBack) {
                        $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'licenseBack', 'licenseBack-');
                        $licenseBack = $imagePath;
                    }

                    $licenseFront = '';
                    if ($request->licenseFront) {
                        $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'licenseFront', 'licenseFront-');
                        $licenseFront = $imagePath;
                    }

                    $userId = User::insertGetId([
                        'name' => $request->name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'unique_id' => $this->generateUniqueId($request->name),
                        'role' => $request->role,
                        'dob' => $request->dob,
                        'is_verified' => 0,
                        'driver_experience' => $request->driverExperience,
                        'nrc_no' => $request->nrcNo,
                        'license_no' => $request->licenseNo,
                        'emergency_number' => $request->emergencyNumber,
                        'emergency_name' => $request->emergencyName,
                        'nrc_back' => $nrcBack,
                        'nrc_front' => $nrcFront,
                        'license_back' => $licenseBack,
                        'license_front' => $licenseFront,
                        'travel_preferences' => $request->travelPreferences,
                        'password' => Hash::make($request->password),
                    ]);
                    if ($userId) {
                        $userDetails = CustomHelper::CheckUserExits($userId);
                        $data = array(
                            'email' => $request->email,
                            'name' => $request->name
                        );
                        try {
                            CommonHelper::sendEmail(['to' =>  $request->email, 'data' => $data, 'template' => 'user-registration']);
                        } catch (\Exception $e) {
                        }
                        return CustomHelper::SuccessResponse(trans('The account has been created successfully.', [], $language), CustomHelper::CapitalizeArray($userDetails->toArray()));
                    } else {
                        return CustomHelper::ErrorResponse(trans('Something Wrong Please try again.', [], $language));
                    }
                }
            }
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse(trans('Something Wrong Please try again.', [], $language));
        }
    }

    public function updateCartInfo(Request $request)
    {
        try {
            $checkUser = CustomHelper::CheckUserExits();
            if (!$checkUser) {
                return CustomHelper::ErrorResponse('User not found');
            }
            $destinationPath = public_path('users/Car/');
            $DBPath = 'users/Car/';
            $carImage = '';
            if ($request->carImage) {
                $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'carImage', 'CarIMG-');
                $carImage = $imagePath;
            }
            $User = User::find($checkUser->userId);
            $User->vehicle_make = $request->vehicleMake;
            $User->vehicle_model = $request->vehicleModel;
            $User->vehicle_color = $request->vehicleColor;
            $User->registration_number = $request->registrationNumber;
            $User->number_of_seat = $request->numberOfSeat;
            $User->fuel_cost_per_km = $request->fuelCostPerKm;
            $User->ac = $request->ac;
            $User->luggage = $request->luggage;
            $User->chargin = $request->chargin;
            $User->music = $request->music;
            $User->pets = $request->pets;
            if ($carImage) {
                $User->car_image = $carImage;
            }
            $User->save();
            return CustomHelper::SuccessWithoutData('Car Details updated successfully!');
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse('Something Wrong Please try again.');
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $language = $request->language ?? 'en';
            $checkUser = CustomHelper::CheckUserExits();
            if (!$checkUser) {
                return CustomHelper::ErrorResponse('User not found');
            }
            $destinationPath = public_path('users/Document/');
            $DBPath = 'users/Document/';
            $nrcFront = '';
            if ($request->nrcFront) {
                $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'nrcFront', 'nrcFront-');
                $nrcFront = $imagePath;
            }
            $nrcBack = '';
            if ($request->nrcBack) {
                $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'nrcBack', 'nrcBack-');
                $nrcBack = $imagePath;
            }

            $licenseBack = '';
            if ($request->licenseBack) {
                $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'licenseBack', 'licenseBack-');
                $licenseBack = $imagePath;
            }

            $licenseFront = '';
            if ($request->licenseFront) {
                $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'licenseFront', 'licenseFront-');
                $licenseFront = $imagePath;
            }

            $User = User::find($checkUser->userId);
            $User->name = $request->name;
            $User->email = $request->email;
            $User->phone = $request->phone;
            $User->dob = $request->dob;
            $User->nrc_no = $request->nrcNo;
            $User->license_no = $request->licenseNo;
            $User->driver_experience = $request->driverExperience;
            if ($licenseBack) {
                $User->license_back = $licenseBack;
            }
            if ($nrcBack) {
                $User->nrc_back = $nrcBack;
            }
            if ($nrcFront) {
                $User->nrc_front = $nrcFront;
            }
            if ($licenseFront) {
                $User->license_front = $licenseFront;
            }

            if (!$User->is_verified && $licenseBack && $nrcBack && $nrcFront && $licenseFront) {
                // send email to admin for verification
                $data = array(
                    'name' => $request->name
                );
                try {
                    CommonHelper::sendEmail(['to' =>  $request->email, 'data' => $data, 'template' => 'acknowledging']);
                } catch (\Exception $e) {
                    return CustomHelper::ErrorResponse($e->getMessage());
                }
            }

            $User->travel_preferences = $request->travelPreferences;
            $User->emergency_number = $request->emergencyNumber;
            $User->emergency_name = $request->emergencyName;
            $User->save();
            return CustomHelper::SuccessResponse(trans('Profile updated successfully.', [], $language), CustomHelper::CapitalizeArray($checkUser->toArray()));
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse(trans('Something Wrong Please try again.', [], $language));
        }
    }

    public function updateOnlineStatus(Request $request)
    {
        try {
            $language = $request->language ?? 'en';
            $checkUser = CustomHelper::CheckUserExits();
            if (!$checkUser) {
                return CustomHelper::ErrorResponse('User not found');
            }
            $User = User::find($checkUser->userId);
            $User->is_online = $request->isOnline;
            $User->save();
            return CustomHelper::SuccessWithoutData(trans('Status updated successfully!', [], $language));
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse(trans('Something Wrong Please try again.', [], $language));
        }
    }

    public function userDetails(Request $request)
    {
        try {
            $language = $request->language ?? 'en';
            $checkUser = CustomHelper::CheckUserExits();
            if (!$checkUser) {
                return CustomHelper::ErrorResponse('User not found');
            }
            $ratingData = Rating::select('rating')
                ->where('driver_id', $request->userId)
                ->orderBy('rating', 'desc')
                ->avg('rating');
            $checkUser->isSubmitted = $checkUser->license_back ? 1 : 0;
            $checkUser->rating = ($ratingData) ? round($ratingData) : 0;
            return CustomHelper::SuccessResponse('Profile Fetch successfully!', CustomHelper::CapitalizeArray($checkUser->toArray()));
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse(trans('Something Wrong Please try again.', [], $language));
        }
    }
    public function updateProfileImage(Request $request)
    {
        try {
            $language = $request->language ?? 'en';
            $checkUser = CustomHelper::CheckUserExits();
            if (!$checkUser) {
                return CustomHelper::ErrorResponse('User not found');
            }
            if ($request->profileImage) {
                $destinationPath = public_path('users/profile/');
                $DBPath = 'users/profile/';
                $profile = '';
                if ($request->profileImage) {
                    $imagePath = CustomHelper::UploadImageFile($destinationPath, $DBPath, 'profileImage', 'IMG-');
                    $profile = $imagePath;
                }
                $userDetails = User::where('id', $checkUser->userId)->update(['profile_picture' => $profile]);
                if ($userDetails) {
                    return CustomHelper::SuccessWithoutData(trans('Profile picture updated successfully!', [], $language));
                } else {
                    return CustomHelper::ErrorResponse(trans('Something Wrong Please try again.', [], $language));
                }
            }
            return CustomHelper::ErrorResponse('Please select file');
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $language = $request->language ?? 'en';
            $checkUser = CustomHelper::CheckUserExits();
            if (!$checkUser) {
                return CustomHelper::ErrorResponse('User not found');
            }
            $userCheck = User::where('id', $checkUser->userId)->first();
            if (Hash::check($request->oldPassword, $userCheck->password)) {
                $User = User::find($checkUser->userId);
                $User->password = Hash::make($request->password);
                $User->save();
                return CustomHelper::SuccessWithoutData(trans('Password changed successfully', [], $language));
            }
            return CustomHelper::ErrorResponse(trans('The old password does not match.', [], $language));
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse(trans('Something Wrong Please try again.', [], $language));
        }
    }
    public function deleteAccount(Request $request)
    {
        try {
            $checkUser = CustomHelper::CheckUserExits();
            if (!$checkUser) {
                return CustomHelper::ErrorResponse('User not found');
            }
            $userCheck = User::where('id', $checkUser->userId)->delete();
            return CustomHelper::SuccessWithoutData('Account Delete successfully');
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }
    public function sendMobileOTP(Request $request)
    {
        try {
            $otp = mt_rand(100000, 999999);
            UserOtp::where('source', $request->input('phone'))->delete();
            UserOtp::insert([
                'source' => $request->input('phone'),
                'otp' => $otp,
                'created_at' => date('Y-m-d H:i:s')
            ]);


            return CustomHelper::SuccessResponse("OTP send Successfully", $otp);
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }
    public function sendEmailOTP(Request $request)
    {
        try {
            $otp = mt_rand(1000, 9999);
            UserOtp::where('source', $request->input('email'))->delete();
            UserOtp::insert([
                'source' => $request->input('email'),
                'otp' => $otp,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $data = array(
                'name' => "",
                'email' => $request->email,
                'otp' => $otp
            );
            try {
                CommonHelper::sendEmail(['to' =>  $request->email, 'data' => $data, 'template' => 'email-otp-api']);
            } catch (\Exception $e) {
                dd($e);
            }

            return CustomHelper::SuccessResponse("OTP send Successfully", $otp);
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }
    public function verifyOTP(Request $request)
    {
        try {
            $otp = $request->input('otp');
            if (!UserOtp::where('source', $request->phone)->where('otp', $otp)->exists()) {
                return response()->json([
                    'responseCode' => 0,
                    'responseText' => 'OTP is not valid.',
                ]);
            }
            return CustomHelper::SuccessWithoutData('OTP Validation successfully ');
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }
    public function verifyEmailOTP(Request $request)
    {
        try {
            $otp = $request->input('otp');
            if (!UserOtp::where('source', $request->email)->where('otp', $otp)->exists()) {
                return response()->json([
                    'responseCode' => 0,
                    'responseText' => 'OTP is not valid.',
                ]);
            }

            User::where('email', $request->email)->update(['is_email_verify' => 1]);
            return CustomHelper::SuccessWithoutData('OTP Validation successfully ');
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse($e->getMessage());
        }
    }

    public function forgotPassword(Request $request)
    {
        if (!User::where('email', $request->email)->exists()) {
            return CustomHelper::ErrorResponse('We cannot find a user with this email address');
        }
        try {
            $user_dtls = User::where('email', $request->email)->first();
            $token = \Str::random(64);
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $link = url('reset-password/' . $token);
            $data = [
                'link' => $link,
                'username' => $user_dtls->name,
            ];
            CommonHelper::sendEmail(['to' => $request->email, 'data' => $data, 'template' => 'forgot-password']);
        } catch (\Exception $e) {

            return CustomHelper::ErrorResponse($e->getMessage());
        }
        return CustomHelper::SuccessWithoutData("We've sent you an email containing a link to reset your password.");
    }


    public function generateUniqueId($name)
    {
        $namePart = strtolower(str_replace(' ', '', $name)); // Remove spaces, lowercase
        $namePart = substr($namePart, 0, 4); // Take first 4 letters

        do {
            $randomDigits = rand(1000, 9999); // Generate 4 random digits
            $uniqueId = $randomDigits . $namePart; // Combine digits and name part
            $exists = User::where('unique_id', $uniqueId)->exists(); // Check if it exists
        } while ($exists); // Keep generating if it already exists

        return $uniqueId;
    }

    public function driverCarInformation(Request $request)
    {
        try {
            $language = $request->language ?? 'en';
            $checkUser = CustomHelper::CheckUserExits();
            if (!$checkUser) {
                return CustomHelper::ErrorResponse('User not found');
            }
            return CustomHelper::SuccessResponse('Profile Fetch successfully!', CustomHelper::CapitalizeArray($checkUser->toArray()));
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse(trans('Something Wrong Please try again.', [], $language));
        }
    }
}
