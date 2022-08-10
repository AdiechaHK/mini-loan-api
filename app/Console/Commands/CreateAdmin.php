<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'become:admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will promote user to admin access.';

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
        $email = $this->argument('email');
        $user = User::whereEmail($email)->first();
        if(!@$user) {
            $this->error("404 - user not found with given email.");
            return;
        }
        $roles = $user->roles;
        if ($roles != null && in_array("admin", $roles)) {
            $this->warn("The user '" . $user->name . "' is already have admin access.");
            return;
        }
        if ($this->confirm(
            "Are you sure you want to promote `" . $user->name . "' to become an admin?"
        )) {
            $user->roles = $roles === null ? ["admin"] : ["admin", ...$roles];
            $user->save();
            $this->info("User '" . $user->name . "' has been promoted to admin access.");
        }
    }
}
