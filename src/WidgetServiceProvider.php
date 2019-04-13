<?php

namespace Imediasun\Widgets;

use Illuminate\Support\ServiceProvider;
use App;
use Blade;


class WidgetServiceProvider extends ServiceProvider
{

    public function boot()
    {

        //Указываем, что файлы из папки config должны быть опубликованы при установке
        $this->publishes([__DIR__ . '/../config/' => config_path() . '/']);

        //Так же публикуем тестовый виджет с каталогом для пользовательских виджетов
        $this->publishes([__DIR__ . '/../app/' => app_path() . '/']);


        /*
         * Регистрируется директива для шаблонизатора Blade
         * Пример обращения к виджету: @widget('test')
        */
        Blade::directive('widget', function ($name) {
            return "<?php echo app('widget')->show($name); ?>";
        });

        /*
         * Регистрируется (добавляем) каталог для хранения шаблонов виджетов
         * app\Widgets\view
         */
        $this->loadViewsFrom(app_path() .'/Widgets/views', 'Widgets');
        /*
       * Регистрируется (добавляем) миграцию для виджета
       */
        $this->loadMigrationsFrom(__DIR__.'/../app/Widgets/migrations');
        /*
     * Добавляем роуты для виджета
     */
        include __DIR__.'/routes.php';

    }


    public function register()
    {

        App::singleton('widget', function(){
            return new \Imediasun\Widgets\Widget();
        });

    }
}