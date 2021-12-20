<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if(App::environment() !== 'local') {
            return;
        }

        $email = 's.walbrun@consolinno.de';
        $user = User::query()->where(User::COL_EMAIL, '=', $email)->first();

        $user ??= new User();
        $user->email = $email;
        $user->password = Hash::make('LetMeIn');
        $user->name = 'Sebastian';
        $user->save();
    }
}
