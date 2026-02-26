<?php

namespace Giogiosw\Finder;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Giogiosw\Finder\Http\Livewire\FileManager;
use Giogiosw\Finder\Http\Livewire\FileTree;
use Giogiosw\Finder\Http\Livewire\FileList;
use Giogiosw\Finder\Http\Livewire\Toolbar;
use Giogiosw\Finder\Http\Livewire\Modals\UploadModal;
use Giogiosw\Finder\Http\Livewire\Modals\CreateFolderModal;
use Giogiosw\Finder\Http\Livewire\Modals\RenameModal;
use Giogiosw\Finder\Http\Livewire\Modals\PropertiesModal;
use Giogiosw\Finder\Http\Livewire\Modals\ArchiveModal;
use Giogiosw\Finder\Http\Livewire\Modals\EditTextModal;

class FinderServiceProvider extends ServiceProvider
{
    public const VERSION = '1.0';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/finder.php', 'finder');

        $this->app->singleton(FinderManager::class, function ($app) {
            return new FinderManager($app['config']['finder']);
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'finder');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'finder');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register Livewire components
        Livewire::component('finder::file-manager', FileManager::class);
        Livewire::component('finder::file-tree', FileTree::class);
        Livewire::component('finder::file-list', FileList::class);
        Livewire::component('finder::toolbar', Toolbar::class);
        Livewire::component('finder::upload-modal', UploadModal::class);
        Livewire::component('finder::create-folder-modal', CreateFolderModal::class);
        Livewire::component('finder::rename-modal', RenameModal::class);
        Livewire::component('finder::properties-modal', PropertiesModal::class);
        Livewire::component('finder::archive-modal', ArchiveModal::class);
        Livewire::component('finder::edit-text-modal', EditTextModal::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/finder.php' => config_path('finder.php'),
            ], 'finder-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/finder'),
            ], 'finder-views');

            $this->publishes([
                __DIR__ . '/../resources/css' => public_path('vendor/finder/css'),
                __DIR__ . '/../resources/js' => public_path('vendor/finder/js'),
            ], 'finder-assets');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'finder-migrations');

            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/finder'),
            ], 'finder-lang');
        }
    }
}
