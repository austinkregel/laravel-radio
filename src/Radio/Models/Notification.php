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

    protected $fillable = [
        'channel_id', 'user_id', 'is_unread', 'name', 'description' , 'link', 'type'
    ];

    public static function boot()
    {
        Notification::created(function (Notification $notify){
            $data = collect($notify->toArray())->merge([
                'uuid' => $notify->user->channel->uuid,
                'is_unread' => 1
            ]);
            Redis::publish($data['uuid'], collect($data));
        });

    }

    protected $casts = [
        'is_unread' => 'bool'
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}