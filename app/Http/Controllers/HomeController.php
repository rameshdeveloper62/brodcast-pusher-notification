<?php

namespace App\Http\Controllers;

use App\Events\MessageEvent;
use App\Message;
use App\Notifications\SendMessage;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users=User::where('id','!=',auth()->id())->pluck('name','id')->all();

        $user = auth()->user();
        $notifications=$user->notifications()->paginate(5);
        $user_notification=[];
        foreach ($notifications as $notification) {
            if(isset($notification->data['to_user']) && $notification->data['to_user']==auth()->id())
            {
                $username=isset($users[$notification->data['from_user']])?$users[$notification->data['from_user']]:'';
                $user_notification[]=['username'=>$username,'message'=>$notification->data['message'],'date_time'=>$notification->created_at->diffForHumans()];    
            }
        }
        return view('home',compact('users','user_notification'));
    }

    public function store(Request $request)
    {
        $data=$request->except('_token');
        $data['updated_at']=$data['created_at']=Carbon::now();
        if(Message::insert($data))
        {

            $to_user=User::find($data['to_user']);
            if($to_user)
            {
                $notification=$request->only('to_user','from_user','message');
                // send notification
                // $to_user->notify(new SendMessage($notification));
                $notification['username']=$to_user->name;
                $notification['date_time']=Carbon::now()->diffForHumans();
                //fire event
                // event(new MessageEvent($notification));
                broadcast(new MessageEvent($notification))->toOthers();
            }

            return response()->json(['message'=>'message saved success.'],Response::HTTP_CREATED);
        }
        else
        {
            return response()->json(['message'=>'message not saved.'],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
