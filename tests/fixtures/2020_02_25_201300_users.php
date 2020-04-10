<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('users')->insert([
            'id' => 1,
            'name' => 'User 1',
            'email' => 'user1@gimball.tv',
            'password' => \Illuminate\Support\Facades\Hash::make('password-1'),
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        \Db::table('oauth_clients')->insert([
            'id' => 1,
            'user_id' => null,
            'name' => 'Laravel Personal Access Client',
            'secret' => 'UPM8d5yPiQHIh9nn5yTea2fDU9kir7qy5saMDSSz',
            'redirect' => 'http://localhost',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        \Db::table('oauth_clients')->insert([
            'id' => 2,
            'user_id' => null,
            'name' => 'Laravel Personal Grant Client',
            'secret' => 'SDtSj3Um2f7XuK1X9dpxd2BYln7H6p1wYMRW2sHQ',
            'redirect' => 'http://localhost',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        \Db::table('oauth_clients')->insert([
            'id' => 3,
            'user_id' => null,
            'name' => 'laravel-client',
            'secret' => 'QipazIVpqfPGUN7HKawsvSLrbZm2DmIl5QHezKus',
            'redirect' => '',
            'personal_access_client' => 0,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }
}
