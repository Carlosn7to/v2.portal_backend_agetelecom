<?php

namespace App\Jobs;

use App\Mail\Portal\Helpers\SendQuality;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailGeneric implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    private array $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach($this->data as $email) {
            $emailValidated = filter_var($email, FILTER_VALIDATE_EMAIL);
            if($emailValidated) {
                \Mail::mailer('contact')->to($email)
                    ->send(new SendQuality());
            }
        }


    }


}
