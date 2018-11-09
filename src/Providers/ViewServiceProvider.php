<?php

/**
 *  ViewServiceProvider
 *
 *  The major concept of this service provider is to replace CompilerEngine, PhpEngine and BladeCompiler
 *  We need these replacement to fix some I/O issue
 *
 */

namespace PIXNET\MemcachedView\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewServiceProvider as LaravelViewProvider;
use Illuminate\View\Compilers\BladeCompiler as LaravelBladeCompiler;
use Illuminate\View\Engines\CompilerEngine as LaravelCompilerEngine;
use Illuminate\View\Engines\PhpEngine as LaravelPhpEngine;
use PIXNET\MemcachedView\Engines\CompilerEngine;
use PIXNET\MemcachedView\Engines\PhpEngine;
use PIXNET\MemcachedView\Compilers\BladeCompiler;

class ViewServiceProvider extends LaravelViewProvider
{
    protected $isMemcachedEnabled;
    /**
     * registerBladeEngine
     *
     * Replace filesystem with MemcacheStorage for BladeCompiler
     *
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $app = $this->app;
        $this->isMemcachedEnabled = in_array(config('cache.default'), ['memcached', 'redis']);

        $app->singleton('blade.compiler', function () {
            if(!$this->isMemcachedEnabled)
            {
                return new LaravelBladeCompiler(
                    $this->app['files'], $this->app['config']['view.compiled']
                );
            }
            $storage = $this->app->make('MemcacheStorage');
            $cache = $this->app['config']['view.compiled'];
            
            return new BladeCompiler($storage, $this->app['files'], $cache);
        });

        $resolver->register('blade', function () {
            if(!$this->isMemcachedEnabled)
            {
                return new LaravelCompilerEngine($this->app['blade.compiler']);
            }
            $storage = $this->app->make('MemcacheStorage');
            return new CompilerEngine($this->app['blade.compiler'], $storage);
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        if(!$this->isMemcachedEnabled)
        {
            $resolver->register('php', function () {
                return new LaravelPhpEngine;
            });
        } else {
            $resolver->register('php', function () {
                return new PhpEngine;
            });
        }

    }
}
