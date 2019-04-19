<?php
namespace Shop_products\Modules\Cli;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Shop_products\Modules\Cli\Services\IndexingService;
use Shop_products\Services\ProductsService;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            'Shop_products\Modules\Cli\Tasks' => __DIR__ . '/tasks/',
        ]);

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        // Register indexing service as a service
        $di->set('indexing', function() {
            return new IndexingService();
        });

        $di->set('products', function () {
            return new ProductsService();
        });
    }
}
