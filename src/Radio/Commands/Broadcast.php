<?php

namespace Kregel\Radio\Commands;

use App\Models\User;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\Eloquent\Model;
use \Kregel\Radio\Models\Notification;
use Kregel\Radio\Models\Channel;

class Broadcast extends Command implements SelfHandling
{
    protected $signature = 'radio:broadcast {--message=: The message you want to send}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Firing up');
        $global = Channel::where('type', 'global')->first();
        //foreach($global->users as $user)
        $user = User::find(1);
        $notification = Notification::create([
            'channel_id' => $global->id,
            'user_id' => $user->id,
            'is_unread' => true,
            'name' => 'Site update',
            'description' => 'The site is going through an important update',
            'type' => 'general'
        ]);
        $this->info('Completed!');
    }

    private function getUsers()
    {
        $user = config('auth.providers.users.model');
        return $user::all();
    }

    private function createChannelFor($user)
    {
        $channel = new Channel;
        $channel->type = 'personal';
        $channel->uuid = Channel::uuid(openssl_random_pseudo_bytes(16));
        $channel->save();

        $user->channel_id = $channel->id;
        $user->save();
    }

}