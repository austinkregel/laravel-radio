<?php
/**
 * Created by PhpStorm.
 * User: sodium-chloride
 * Date: 5/25/2016
 * Time: 2:46 PM
 */

namespace Kregel\Radio\Models;

use JWTAuth;

trait Radio
{
    public static function boot()
    {
        parent::boot();
        self::created(function ($mah_self) {
            $channel = new Channel;
            $channel->type = 'personal';
            $channel->uuid = Channel::uuid(openssl_random_pseudo_bytes(16));
            $channel->save();

            $mah_self->channel_id = $channel->id;
            $mah_self->save();
        });
    }

    public function channels(){
        return $this->belongsToMany(Channel::class, 'radio_channel_user');
    }
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
    public function notifications(){
        return $this->hasMany(Notification::class);
    }
    public function token()
    {
        return JWTAuth::fromUser($this);
    }


}