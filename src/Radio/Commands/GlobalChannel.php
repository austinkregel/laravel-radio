<?php

namespace Kregel\Radio\Commands;

use App\Models\User;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\Eloquent\Model;
use Kregel\Radio\Models\Channel;

class GlobalChannel extends Command implements SelfHandling
{
    protected $signature = 'radio:global';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Firing up');
        $channel = Channel::where('type', 'global')->first();
        if(empty($channel))
            $channel = $this->createChannel();

        $users = $this->getUsers();
        if($users->count() !== $channel->users->count())
        {
            $user = config('auth.providers.users.model');
            $channel->users()->saveMany($users);
        } else {
            $this->info('All users have the global channel: ' . $channel->uuid);
        }
        $this->info('Completed!');
    }

    private function getUsers()
    {
        $user = config('auth.providers.users.model');
        return $user::all();
    }

    private function createChannel()
    {
        $channel = Channel::create([
            'type' => 'global',
            'uuid' => Channel::uuid(openssl_random_pseudo_bytes(16))
        ]);
        return $channel;
    }

}