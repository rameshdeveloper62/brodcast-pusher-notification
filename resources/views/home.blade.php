@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Real time notification demo</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="alert alert-success hide">
                        Message was sent successfully.
                    </div>
                    
                    <div class="alert alert-danger hide">
                        Message was not sent successfully.
                    </div>

                    <form id="form">
                        <input type="hidden" name="from_user" value="{{auth()->id()}}"/>
                      <div class="form-group">
                        <label for="to_user">Select User:</label>
                        <select id="to_user" name="to_user" class="form-control">
                            <option value="">Select User</option>
                            @if(isset($users) && count($users))
                                @foreach($users as $user_id=>$user)
                                    <option value="{{$user_id}}">{{$user}}</option>
                                @endforeach
                            @endif
                        </select>

                      </div>
                      <div class="form-group">
                        <label for="pwd">Message:</label>
                        <textarea class="form-control" id="message" name="message"></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('script')

<script>

    $(function(){
        
        $( "#form" ).validate({
            'errorClass':'text-danger',
            rules: {
                to_user: {
                  required: true,
                },
                message: {
                  required: true,
                }
            },
            messages:{
                to_user:{
                    required:'Please select user.'
                },
                message:{
                    required:'Message field is required.'
                }
            },
            submitHandler:function(form){
                 sendMessage(form);
            }
        });
        // send message
        sendMessage=function(form)
        {
            $.ajax({
                url: '{{action('HomeController@store')}}',
                type: 'post',
                dataType: 'json',
                data: $(form).serialize(),
            })
            .done(function() {
                $(form)[0].reset();
                $('.alert-success').removeClass('hide');
                $('.alert-success').show();
                $('.alert-success').fadeOut(5000);
                console.log("success");
            })
            .fail(function() {
                console.log("error");
                $('.alert-danger').removeClass('hide');
                $('.alert-danger').show();
                $('.alert-danger').fadeOut(5000);
            });
        }

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
        data=data.data;
         console.log(data);

        var listItem = $('<li style="padding:2px;" />');
        var message=$('<div class="clearfix"/>');
        var username=$('<small class="pull-left"/>');
        var datetime=$('<small class="pull-right"/>');
        var hr=$('<hr/>');

        message.text(data.message);
        username.text(data.username);
        datetime.text(data.date_time);

        listItem.append(message);
        listItem.append(username);
        listItem.append(datetime);
        listItem.append(hr);
        $('.no_message').addClass('hide');
    
        var myFunction = function() {
          alert('Click function');
        };

        var options = {
                  body: 'testing',
                  icon: ''
            }

        // var n;
        // if(window.Notification && Notification.permission !== "denied") {
        //     Notification.requestPermission(function(status) {  // status is "granted", if accepted by user
        //         n = new Notification(data.username, { 
        //             body: data.message,
        //             icon: '' // optional
        //         }); 

        //         n.close();
        //     });
        // }
        
        $('.notification').addClass('text-success').css('font-weight','bold');
        $('#messages').prepend(listItem.html());
    }
  })
</script>
@endpush
@endsection
