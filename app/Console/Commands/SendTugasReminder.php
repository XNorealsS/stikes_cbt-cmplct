<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tugas;
use App\Models\User;
use App\Models\Notification;

class SendTugasReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tugas:send-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily notification reminder for assignments due tomorrow (H-1)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrowStart = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        // Fetch active tasks due tomorrow
        $tasksDueTomorrow = Tugas::with('course')
            ->where('is_aktif', true)
            ->whereBetween('deadline', [$tomorrowStart, $tomorrowEnd])
            ->get();

        $this->info("Found " . $tasksDueTomorrow->count() . " assignments due tomorrow.");

        foreach ($tasksDueTomorrow as $task) {
            $classId = $task->class_id;

            // Get students who have not submitted yet
            $studentsQuery = User::where('role', 'mahasiswa')
                ->whereDoesntHave('submissions', function ($q) use ($task) {
                    $q->where('tugas_id', $task->id);
                });

            if ($classId) {
                $studentsQuery->where('class_id', $classId);
            }

            $students = $studentsQuery->get();

            $this->info("Sending H-1 reminders for task '{$task->judul}' to " . $students->count() . " students.");

            foreach ($students as $student) {
                Notification::create([
                    'user_id' => $student->id,
                    'title'   => 'Reminder: Tugas H-1!',
                    'body'    => 'Tugas "' . $task->judul . '" untuk mata kuliah ' . $task->course->name . ' akan segera habis waktu pengumpulannya besok pada ' . $task->deadline->format('H:i') . ' WIB.',
                    'is_read' => false,
                ]);
            }
        }

        $this->info("All H-1 assignment reminders sent successfully.");
    }
}
