<?php

namespace Kregel\Radio\Commands;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\Eloquent\Model;
use Kregel\Radio\Models\Channel;

class CreateUserChannels extends Command implements SelfHandling
{
    protected $signature = 'radio:users';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Firing up');
        $users = $this->getUsers();
        foreach($users as $user)
        {
            $this->info('Creating channel for user: ' . $user->id);
            $this->createChannelFor($user);
        }
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