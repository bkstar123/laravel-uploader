<?php
/**
 * LaravelUploaderServiceProvider
 *
 * @author: tuanha
 * @last-mod: 15-06-2019
 */
namespace Bkstar123\LaravelUploader;

use Illuminate\Support\ServiceProvider;
use Bkstar123\LaravelUploader\Services\FileUpload;
use Bkstar123\LaravelUploader\Contracts\FileUpload as FileUploadContract;

class LaravelUploaderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/bkstar123_fileupload.php', 'bkstar123_fileupload');

        $this->publishes([
            __DIR__.'/Resources/Assets' => public_path('vendor/fileupload'),
        ], 'bkstar123_fileupload.assets');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FileUploadContract::class, FileUpload::class);
    }
}
