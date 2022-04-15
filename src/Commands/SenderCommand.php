<?php

namespace App\Packages\Sender\Commands;

use Illuminate\Console\Command;

class SenderCommand extends Command
{
    public $signature = 'sender-laravel';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
