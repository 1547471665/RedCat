<?php

namespace App\Jobs;

use App\Models\Car;

class WithmoneyJob extends Job
{

    public $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        //
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        Car::create($this->params);
    }
}
