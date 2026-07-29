<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class SendMoodReminders extends Command
{
    protected $signature = 'app:send-mood-reminders';
    protected $description = 'Send in-app notifications to users who have not logged their mood today';

    public function handle(): void
    {
        $users = User::where('reminder_enabled', true)->get();
        $sent = 0;

        foreach ($users as $user) {
            $tz = $user->timezone ?? 'Africa/Tunis';
            $now = Carbon::now($tz);
            $reminder = Carbon::parse($user->reminder_time ?? '20:00:00', $tz);

            if ($now->diffInMinutes($reminder, true) > 15) {
                continue;
            }

            $hasMoodToday = $user->moods()->whereDate('date', $now->toDateString())->exists();

            if ($hasMoodToday) {
                continue;
            }

            $alreadySent = DatabaseNotification::where('notifiable_id', $user->id)
                ->where('notifiable_type', User::class)
                ->where('type', 'mood_reminder')
                ->whereDate('created_at', $now->toDateString())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $user->notifications()->create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'mood_reminder',
                'data' => ['message' => "N'oublie pas de logger ton mood du jour \u{1F319}"],
            ]);

            $sent++;
        }

        $this->info("Sent {$sent} mood reminder(s).");
    }
}
