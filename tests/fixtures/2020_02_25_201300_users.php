<?php

use Illuminate\Database\Migrations\Migration;

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

        \Db::table('oauth_access_tokens')->insert([
            'id' => 'd8ebe93ce08347772a975e568264b685d391be7252872ab4697d4c98390e6d6d6c5ffb795ec05b53',
            'user_id' => 1,
            'client_id' => 2,
            'name' => '',
            'scopes' => '["profile"]',
            'revoked' => 0,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        \Db::table('oauth_access_tokens')->insert([
            'id' => 'd8ebe93ce08347772a975e568264b685d391be7252872ab4697d4c98390e6d6d6c5ffb795ec1483a',
            'user_id' => null,
            'client_id' => 3,
            'name' => '',
            'scopes' => '["profile"]',
            'revoked' => 0,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }
}
