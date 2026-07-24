<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Project;
use App\Models\Post;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Morph map: kolom target_type di tabel `interaction`/`comments` menyimpan
        // alias pendek ('project'/'post'), bukan nama class penuh. Map ini membuat
        // relasi polymorphic (interactions, comments) & withCount-nya cocok dengan
        // data. Sengaja memakai morphMap() non-strict (bukan enforceMorphMap): model
        // polymorphic baru yang belum terdaftar otomatis fallback ke nama class penuh
        // (perilaku default Laravel) tanpa melempar exception ke developer lain.
        Relation::morphMap([
            'project' => Project::class,
            'post'    => Post::class,
        ]);
    }
}
