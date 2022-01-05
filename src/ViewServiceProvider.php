<?php
/*
|----------------------------------------------------------------------------
| TopWindow [ Internet Ecological traffic aggregation and sharing platform ]
|----------------------------------------------------------------------------
| Copyright (c) 2006-2019 http://yangrong1.cn All rights reserved.
|----------------------------------------------------------------------------
| Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
|----------------------------------------------------------------------------
| Author: yangrong <yangrong2@gmail.com>
|----------------------------------------------------------------------------
*/
namespace Learn\View;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\ViewFinderInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\Factory;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use Learn\View\Compilers\CompilesLoad;
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->extendFactory();
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->extendBladeCompiler();
    }
    /**
     * extend the view.
     *
     * @return void
     */
    protected function extendFactory()
    {
        $this->app->extend('view', function ($view, $app) {
            return new class($app['view.engine.resolver'], $app['view.finder'], $app['events']) extends Factory
            {
                public function __construct(EngineResolver $engines, ViewFinderInterface $finder, Dispatcher $events)
                {
                    parent::__construct($engines, $finder, $events);
                    $extension = config('view.suffix', 'htm');
                    foreach ((array) $extension as $ext) {
                        $this->finder->addExtension($ext);
                        $this->extensions[$ext] = 'blade';
                    }
                }
            };
        });
        $this->app->extend('blade.compiler', function ($blade, $app) {
            return new class($app['files'], $app['config']['view.compiled']) extends BladeCompiler
            {
                /**
                 * Determine if the view at the given path is expired.
                 *
                 * @param  string  $path
                 * @return bool
                 */
                public function isExpired($path)
                {
                    if (!(bool) config('view.tpl_cache', true)) {
                        return true;
                    }
                    return parent::isExpired($path);
                }
            };
        });
    }
    /**
     * extend the Blade compiler implementation.
     *
     * @return void
     */
    protected function extendBladeCompiler()
    {
        Blade::directive('load', function ($expression) {
            return \sprintf("<?php echo \\%s::render(%s); ?>", CompilesLoad::class, $expression);
        });
    }
}