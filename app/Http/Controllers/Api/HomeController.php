<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\CmsPages;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    function Home(Request $request)
    {
        $language = (!empty($request->language) ? $request->language : 'fr');
        $data = [];
        return CustomHelper::SuccessResponse(trans('Data fetch successfully', [], $language), CustomHelper::CapitalizeArray($data));
    }

    public function slugPage(Request $request)
    {
        $language = $request->language ?? 'en';
        try {
            $slugPageData = CmsPages::where('page_slug', $request->slug)->first();
            if (!$slugPageData) {
                return CustomHelper::ErrorResponse(trans('Data not found', [], $language));
            }
            return CustomHelper::SuccessResponse(trans('Data fetch successfully', [], $language), CustomHelper::CapitalizeArray($slugPageData->toArray()));
        } catch (\Exception $e) {
            return CustomHelper::ErrorResponse(trans('Something went wrong', [], $language));
        }
    }
}
