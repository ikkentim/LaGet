<?php

namespace Laget\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Laget\User;
use Symfony\Component\Console\Input\InputArgument;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {name} {email} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info('Generating API key...');
        do
        {
            $key = str_random(32);
        } while(User::where('apikey', $key)->count() > 0);

        $this->info('Saving user...');
        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make(empty($password) ? str_random() : $password);

        $user->apikey = $key;
        $user->save();

        $this->info('Done!');
        $this->info("Generated API key is '$key'");
    }
}
