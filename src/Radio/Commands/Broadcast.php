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
    protected $signature = 'radio:broadcast';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Firing up');
        $global = Channel::where('type', 'global')->first();

        $title = $this->ask('What is the title of this broadcast?');
        $message = $this->ask('What is your message to your users?');

        if(empty($message) || empty($title)) {
            $this->error('You must fillout both the title and the message.');
            return;
        }
        $this->info('Making your notification, please know if you have a lot of users this may take a while...');
        foreach($global->users as $user)
            Notification::create([
                'channel_id' => $user->channel->id,
                'user_id' => $user->id,
                'is_unread' => true,
                'name' => $title,
                'description' => $message,
                'type' => 'general'
            ]);
        $this->info('Completed!');
    }

}
