<?php

use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\User::all();
        
        factory(\App\Notification::class, 'opus', 5)->create()->each(function ($notification) use ($users) {
            echo $notification."\n";
            foreach($users as $user) {
                $user->notifications()->attach($notification->id);
            }
        });

        factory(\App\Notification::class, 'comment', 5)->create()->each(function ($notification) use ($users) {
            echo $notification."\n";
            foreach($users as $user) {
                $user->notifications()->attach($notification->id);
            }
        });
    }
}
