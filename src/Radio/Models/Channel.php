<?php

namespace Kregel\Radio\Models;

use Illuminate\Database\Eloquent\Model;
use Kregel\FormModel\Traits\Formable;

class Channel extends Model
{
    use Formable;

    protected $table = 'radio_channels';

    protected $form_name = 'uuid';

    protected $fillable = [
        'uuid', 'type'
    ];

    protected $hidden = [
        'updated_at', 'created_at', 'pivot', 'id', 'type'
    ];

    public static function boot()
    {
        self::deleting(function(Channel $channel){
            $channel->users()->sync([]);
        });
    }
    public function users()
    {
        return $this->belongsToMany(config('auth.providers.users.model'), 'radio_channel_user');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'channel_id');
    }

    public static function uuid($data){
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}