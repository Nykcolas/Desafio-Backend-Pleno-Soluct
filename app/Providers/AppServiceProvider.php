<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Task; // IMPORTAR O MODELO TASK
use App\Observers\TaskObserver; // IMPORTAR O OBSERVER

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }


    public function boot(): void
    {
        Task::observe(TaskObserver::class);
    }
}
