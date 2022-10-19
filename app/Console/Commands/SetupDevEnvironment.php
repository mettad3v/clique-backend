<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupDevEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up the development environment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Setting up development environment');
        $this->MigrateAndSeedDatabase();

        $this->createUser('John Doe', 'john@example.com', 'admin');
        $this->createUser('Jane Doe', 'jane@example.com');

        $this->info('All done. Bye!');
    }

    /**
     * @param User $user
     */
    public function createPersonalAccessTokenForUser(User $user): void
    {
        $this->info(PHP_EOL);
        $this->info("Creating personal access client and token for {$user->name}");
        $this->CreatePersonalAccessToken($user);
        $this->info(PHP_EOL);
    }

    public function MigrateAndSeedDatabase()
    {
        $this->call('migrate:fresh');
        $this->call('db:seed');
    }

    public function createUser($name, $email, $password = 'secret')
    {
        $this->info(PHP_EOL);
        $this->info("Creating {$name} ");
        $user =  User::factory()->make([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        $user->save();

        $this->createPersonalAccessTokenForUser($user);
        $this->info("Done");
    }

    public function CreatePersonalAccessToken($user)
    {
        $token = $user->createToken('Development Token');
        $this->info('Personal access token created successfully.');
        $this->warn("Personal access token:");
        $this->line($token->plainTextToken);
    }
}
