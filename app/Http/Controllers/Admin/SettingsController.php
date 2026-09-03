<?php

namespace App\Http\Controllers\Admin;

use \App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use App\Models\Settings;
use App\Models\EmailSetting;

class SettingsController extends Controller
{
	function getGeneralSettings()
	{
		if (!AdminHelper::isView() && !AdminHelper::isUpdate() && !AdminHelper::isRead()) {
			return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
		}
		$data = [];
		$data['page_title'] = "General Settings";
		$data['row'] = Settings::find(1);
		//dd($data['row']);

		return view('admin.settings.general', $data);
	}

	function postSaveGeneralSettings(Request $request)
	{
		/*if (!AdminHelper::isUpdate()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }*/
		$request->validate([
			'site_title' => 'required|string|max:250',
			'logo' => 'mimes:jpeg,jpg,png,gif,svg|max:2000',
			'footer_logo' => 'mimes:jpeg,jpg,png,gif,svg|max:2000',
			'favicon' => 'mimes:jpeg,jpg,png,svg,ico|max:2000',
			'home_banner' => 'mimes:jpeg,jpg,png,svg,ico|max:2000',
			'site_email' => 'email|max:255|nullable',
			'site_phone_number' => 'string|max:50|nullable',
			'site_phone_actual' => 'string|max:50|nullable',
			'site_address' => 'string|max:250|nullable',
			'facebook_link' => 'url|max:250|max:250|nullable',
			'instagram_link' => 'url|max:250|max:250|nullable',
			'twitter_link' => 'url|max:250|max:250|nullable',
			'linkedin_link' => 'url|max:250|max:250|nullable',
		]);

		$settings = Settings::find(1);
		$logo = '';
		$footer_logo = '';
		$favicon = '';
		$home_banner = '';
		$created_by = AdminHelper::myId();
		$updated_by = AdminHelper::myId();
		$user_ip = $request->ip();
		$date = date('Y-m-d H:i:s');

		/*if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $name = 'logo-'.time().'.'.$image->getClientOriginalExtension();
            $destinationPath = storage_path('app/uploads/images/');
            $image->move($destinationPath, $name);
            $logo = 'storage/uploads/images/'.$name;
        }
*/

		if ($request->hasFile('logo')) {
			$image = $request->file('logo');
			$name = 'logo-' . time() . '.' . $image->getClientOriginalExtension();
			$destinationPath = public_path('uploads/site/logo/');
			$image->move($destinationPath, $name);
			$logo = 'uploads/site/logo/' . $name;
		}
		if ($request->hasFile('footer_logo')) {
			$image = $request->file('footer_logo');
			$name = 'footer_logo-' . time() . '.' . $image->getClientOriginalExtension();
			$destinationPath = public_path('uploads/site/logo/');
			$image->move($destinationPath, $name);
			$footer_logo = 'uploads/site/logo/' . $name;
		}

		if ($request->hasFile('favicon')) {
			$image = $request->file('favicon');
			$name = 'favicon-' . time() . '.' . $image->getClientOriginalExtension();
			$destinationPath = public_path('uploads/site/favicon/');
			$image->move($destinationPath, $name);
			$favicon = 'uploads/site/favicon/' . $name;
		}
		$payment_method = '';
		if ($request->hasFile('payment_method')) {
			$image = $request->file('payment_method');
			$name = 'payment_method-' . time() . '.' . $image->getClientOriginalExtension();
			$destinationPath = public_path('uploads/site/');
			$image->move($destinationPath, $name);
			$payment_method = 'uploads/site/' . $name;
		}
		if (!empty($settings)) {
			$settings->appname = $request->input('site_title');
			if (!empty($logo)) {
				$settings->logo = $logo;
			}
			if (!empty($footer_logo)) {
				$settings->footer_logo = $footer_logo;
			}
			if (!empty($favicon)) {
				$settings->favicon = $favicon;
			}
			if (!empty($payment_method)) {
				$settings->payment_method = $payment_method;
			}
			$settings->site_email = $request->input('site_email');
			$settings->site_address = $request->input('site_address');
			$settings->booking_fee = $request->input('booking_fee');
			$settings->trip_booking_fee = $request->input('trip_booking_fee');
			$settings->driver_payout_fee = $request->input('driver_payout_fee');
			$settings->site_phone_number = $request->input('site_phone_number');
			$settings->facebook_link = $request->input('facebook_link');
			$settings->twitter_link = $request->input('twitter_link');
			$settings->instagram_link = $request->input('instagram_link');
			$settings->youtube_link = $request->input('youtube_link');
			$settings->updated_at = $date;
			$settings->save();
		} else {
			Settings::insert([
				'appname' => $request->input('site_title'),
				'logo' => $logo,
				'footer_logo' => $footer_logo,
				'favicon' => $favicon,
				'home_banner' => $home_banner,
				'site_email' => $request->input('site_email'),
				'site_address' => $request->input('site_address'),
				'booking_fee' => $request->input('booking_fee'),
				'trip_booking_fee' => $request->input('trip_booking_fee'),
				'driver_payout_fee' => $request->input('driver_payout_fee'),
				'comment_instruction' => $request->input('comment_instruction'),
				'site_phone_number' => $request->input('site_phone_number'),
				'site_about' => NULL,
				'opening_hours' => $request->input('opening_hours'),
				'facebook_link' => $request->input('facebook_link'),
				'twitter_link' => $request->input('twitter_link'),
				'instagram_link' => $request->input('instagram_link'),
				'linkedin_link' => $request->input('linkedin_link'),
				'maintenance_mode' => 0,
				'user_ip' => $user_ip,
				'created_at' => $date
			]);
		}
		return redirect()->back()->withSuccess('Settings updated successfully!');
	}

	function getEmailSettings()
	{
		/*if (!AdminHelper::isView() && !AdminHelper::isUpdate() && !AdminHelper::isRead()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }*/
		$data = [];
		$data['page_title'] = "Email Settings";
		$data['row'] = EmailSetting::find(1);

		return view('admin.settings.email', $data);
	}

	function postSaveEmailSettings(Request $request)
	{
		/*if (!AdminHelper::isUpdate()) {
            return redirect(AdminHelper::adminPath())->withError('Sorry you do not have privilege to access this area !');
        }*/
		$request->validate([
			'email_sender' => 'required|email|max:250',
			'mail_driver' => 'required|max:250',
			'smtp_host' => 'required|max:250',
			'smtp_port' => 'required|max:250',
			'smtp_username' => 'required|max:250',
			'smtp_password' => 'required|max:250',
		]);

		$email_settings = EmailSetting::find(1);
		$date = date('Y-m-d H:i:s');

		if (!empty($email_settings)) {
			$email_settings->email_sender = $request->input('email_sender');
			$email_settings->mail_driver = $request->input('mail_driver');
			$email_settings->smtp_host = $request->input('smtp_host');
			$email_settings->smtp_port = $request->input('smtp_port');
			$email_settings->smtp_username = $request->input('smtp_username');
			$email_settings->smtp_password = $request->input('smtp_password');

			$email_settings->updated_at = $date;
			$email_settings->save();
		} else {
			EmailSetting::insert([
				'email_sender' => $request->input('email_sender'),
				'mail_driver' => $request->input('mail_driver'),
				'smtp_host' => $request->input('smtp_host'),
				'smtp_port' => $request->input('smtp_port'),
				'smtp_username' => $request->input('smtp_username'),
				'smtp_password' => $request->input('smtp_password'),
				'created_at' => $date
			]);
		}

		return redirect()->back()->withSuccess('Email settings updated successfully!');
	}
}
