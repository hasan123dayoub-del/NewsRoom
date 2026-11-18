<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $reportData = [];
    public $logOutput;
    protected $isDryRun;
    public function __construct($isDryRun = false)
    {
        $this->isDryRun = $isDryRun;
    }

    public function handle()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $writers = User::where('role', 'writer')
            ->whereHas('articles', fn($q) => $q->where('status', 'published')->where('created_at', '>=', $startOfMonth))
            ->withCount(['articles' => fn($q) => $q->where('status', 'published')->where('created_at', '>=', $startOfMonth)])
            ->get();

        $this->logOutput = "Monthly Writers Productivity Report - " . Carbon::now()->format('F Y') . "\n";

        foreach ($writers as $writer) {
            $name = $writer->profile ? "{$writer->profile->first_name} {$writer->profile->last_name}" : $writer->name;
            $this->reportData[] = ['ID' => $writer->id, 'Name' => $name, 'Articles' => $writer->articles_count];
            $this->logOutput .= "- Writer: {$name} | Published: {$writer->articles_count}\n";
        }

        if (!$this->isDryRun) {
            Log::channel(config('logging.report_channel'))->info($this->logOutput);
        }
    }
}
