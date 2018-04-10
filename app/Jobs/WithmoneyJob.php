<?php

namespace App\Jobs;

class WithmoneyJob extends Job
{
    public $str;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($str)
    {
        //
        $this->str = $str;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        echo 'hellow ' . $this->str;
    }
}
