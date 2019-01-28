<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User\User;
use Carbon\Carbon;

class syncV2Users extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncV2:users {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the Users with the V2 Database';

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
     * @return mixed
     */
    public function handle()
    {
        $from_date = $this->argument('date') ?? '00-00-00';
        $from_date = Carbon::createFromFormat('Y-m-d', $from_date)->startOfDay();

        \DB::connection('dbp_users_v2')->table('user')->where('created', '>', $from_date)->orderBy('id')
           ->chunk(50000, function ($users) {
               foreach($users as $user) {
                   User::firstOrCreate([
                       'v2_id'            => $user->id,
                       'name'             => $user->username ?? $user->email,
                       'password'         => bcrypt($user->password),
                       'first_name'       => $user->first_name,
                       'last_name'        => $user->last_name,
                       'token'            => str_random(24),
                       'email'            => $user->email,
                       'activated'        => (int) $user->confirmed,
                       'created_at'       => Carbon::createFromTimeString($user->created),
                       'updated_at'       => Carbon::createFromTimeString($user->updated),
                   ]);
               }
           });
    }
}
