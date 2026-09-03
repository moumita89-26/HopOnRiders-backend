<?php 
namespace App\Http\Controllers\Admin;

use \App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
	function getIndex()
    {      
    	$request = request();
        $data = [];
        $data['page_title'] = 'Notifications';
        $data['limit'] = $limit = (!empty($request->get('limit'))?$request->get('limit'):20);
        $data['rows'] = Notification::where('user_id', AdminHelper::myId())->latest()->paginate($limit);
        
        //dd($data['rows']); 
           
        return view('admin.notifications.index', $data);
    }

    function readNotification($id)
    {
        Notification::where('id',$id)->update(['is_read'=>1, 'updated_at'=>date('Y-m-d H:i:s')]);
        return redirect()->route('admin.notification');
    }

    function deleteNotification($id)
    {
      if(!empty($id))
        {
            Notification::where('id',$id)->delete();
            return redirect()->back()->withSuccess('Notification deleted successfully!');
        }
    }

    public function postActionSelected(Request $request)
    {        
        $id_selected = $request->input('checkbox');
        $button_name = $request->input('button_name');
        $message = "No action taken";
        if (empty($id_selected)) {
            AdminHelper::redirect($_SERVER['HTTP_REFERER'], 'Please select at least one data!', 'warning');
        }

        if ($button_name == 'delete') {  
            Notification::whereIn('id', $id_selected)->delete();

            AdminHelper::insertLog("Deleted data ".implode(',', $id_selected)." by ".AdminHelper::myName()." ip: ".$request->ip());            

            $message = "The selected notification deleted successfully !";

            return redirect()->back()->withSuccess($message);
        }

        if($button_name == 'read')      
        {
            Notification::whereIn('id', $id_selected)->update(['is_read'=>'1', 'updated_at'=>date('Y-m-d H:i:s')]);

            AdminHelper::insertLog("Updated data ".implode(',', $id_selected)." by ".AdminHelper::myName()." ip: ".$request->ip()); 

            $message = "The selected notification read successfully !";
            return redirect()->back()->withSuccess($message);
        }

        return redirect()->back()->withError($message);
    }
    
}