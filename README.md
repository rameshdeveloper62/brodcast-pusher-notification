- laravel pusher brodacast notification
(1) first of all we should create table for store notification
	php artisan notifications:table
	php artisan migrate
	
(2)	You meed to install * composer require pusher/pusher-php-server * for laravel(server)
	and include pusher.js for javascript(client)


(3) we creates sendMessage notification class
	App\Notifications\SendMessage.php
	-> php artisan make:notification SendMessage
	-> we used datatable type of notification 

	    public function via($notifiable)
	    {
	        return ['database'];
	    }
	-> toArray() method for store data in notification table
	
	    public function toArray($notifiable)
	    {
	        return $this->message;
	    }

(4) we used by default user reference table, Notifiable trait is already defined in user model 

(5) we send notification when user add new message
	
	$to_user->notify(new SendMessage($notification));

(6) we can display current login user notification by $user->notifications.
	if we want to display only read,unread,all but we can able to display all type of notification

	-> read and unread = $user->notifications
	-> unread = $user->unreadNotifications
	-> Marking Notifications As Read

		$user = App\User::find(1);

		foreach ($user->unreadNotifications as $notification) {
		    $notification->markAsRead();
		}
		// all notification read by current user object
		$user->unreadNotifications->markAsRead();
		
		// mark as read by update method
		$user->unreadNotifications()->update(['read_at' => now()]);

	-> delete notification
		$user->notifications()->delete();
(7) we can change in config/app.php file for enable brodcast service provider
	uncomment this line :  App\Providers\BroadcastServiceProvider::class,

(8) change of BROADCAST_DRIVER=log to pusher in .env file
	BROADCAST_DRIVER=pusher

(9) add pusher key,secret,cluster in .env file

(10) create event for send notification 
	if you didn't add event name and listener so these commands are run. it will automatically add in app/Providers/EventServiceprovider.php.
	
	The ShouldBroadcast interface requires you to implement a single method: broadcastOn. The broadcastOn method should return a channel or array of channels that the event should broadcast on. The channels should be instances of Channel, PrivateChannel, or  PresenceChannel


	php artisan make:event MessageEvent
	
	class ServerCreated implements ShouldBroadcast
	{
	//define channel name

		public function broadcastOn()
	    {
	        return ['my-channel'];
	    }
	}

(11) when user send message to other user.
	HomeController@store method
	
	event(new MessageEvent($notification));
	
	brodcast() helper is working same like event() helper but if some time you need to send user will not receive thier own notification so we can use toOthers().

	broadcast(new MessageEvent($notification))->toOthers();

(12) when current login user send message it will automatically display in top bar 	notification bar

	In home.blade.php

	  //instantiate a Pusher object with our Credential's key
      var pusher = new Pusher('{{env('PUSHER_APP_KEY')}}', {
          cluster: '{{env('PUSHER_APP_CLUSTER')}}',
          encrypted: true
      });

      //Subscribe to the channel we specified in our Laravel Event
      var channel = pusher.subscribe('my-channel');

      //Bind a function to a Event (the full Laravel class)
      channel.bind('App\\Events\\MessageEvent', addMessage);

      function addMessage(data) {
        console.log(data);
      }



Private channel: it is authenticate user can access this channel.

Present channel :  it is private by default private.

public channel : all user access this channel publically.

for use private channel:
we have to define the authentication logic. The first one checks if the current user is logged in. Only logged in users can listen in on the updates channel.

Broadcast::channel('updates', function ($user) {
    return auth()->check();
});

for present channel: 
The second checks to see if the user can listen on the online presence channel. Unlike the first, the presence channel does not return a boolean. It returns details of the user if the user is authorized.

```
Broadcast::channel('online', function ($user) {
    if (auth()->check()) {
        return $user->toArray();
    }
});
```
