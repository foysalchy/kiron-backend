<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File; 

class ListModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:list-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
  public function handle()
{
    $models = collect(File::allFiles(app_path('Models')))
        ->map(function ($file) {
            return 'App\\Models\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );
        })
        ->filter(fn ($class) => class_exists($class) && is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class))
        ->values();

    $this->info(json_encode($models, JSON_PRETTY_PRINT));
}
}
