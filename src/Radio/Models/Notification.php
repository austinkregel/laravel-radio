<?php
namespace Kregel\Radio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Kregel\FormModel\Traits\Formable;
use Kregel\Warden\Traits\Wardenable;

class Notification extends Model
{
    use Formable;

    protected $table = 'radio_notifications';

    protected $form_name = 'name';


    protected $fillable = [
        'channel_id', 'user_id', 'is_unread', 'name', 'description' , 'link', 'type'
    ];

    public static function boot()
    {
        Notification::created(function (Notification $notify){
            $data = array_merge($notify->toArray(), [
                'uuid' => $notify->user->channel->uuid,
                'is_unread' => 1
            ]);
            Redis::publish($notify->channel->uuid, collect($data));
        });

    }

    protected $casts = [
        'is_unread' => 'bool'
    ];
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}