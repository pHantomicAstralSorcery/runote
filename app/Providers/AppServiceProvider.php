<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notebook;
use App\Models\Page;
use App\Models\Block;
use App\Models\ResponseField;
use App\Observers\AuditableObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // ...
    }

    public function boot()
    {
        Notebook::observe(AuditableObserver::class);
        Page::observe(AuditableObserver::class);
        Block::observe(AuditableObserver::class);
        ResponseField::observe(AuditableObserver::class);
    }
}
