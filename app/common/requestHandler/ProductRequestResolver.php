<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 12:24 م
 */

namespace app\common\requestHandler;


use app\common\enums\ProductTypesEnum;
use app\common\requestHandler\product\CreateDownloadableProductRequestHandler;
use app\common\requestHandler\product\CreatePhysicalProductRequestHandler;
use Phalcon\Mvc\Controller;

class ProductRequestResolver
{

    const PHYSICAL_PRODUCT = CreatePhysicalProductRequestHandler::class;
    const DOWNLOADABLE_PRODUCT = CreateDownloadableProductRequestHandler::class;

    public $type;
    private $controller;

    /**
     * ProductRequestResolver constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function resolve()
    {
        $validTypes = [
            ProductTypesEnum::TYPE_PHYSICAL => self::PHYSICAL_PRODUCT,
            ProductTypesEnum::TYPE_DOWNLOADABLE => self::DOWNLOADABLE_PRODUCT
        ];

        if (!array_key_exists($this->type, $validTypes)) {
            throw new \Exception('Invalid product type', 400);
        }

        return new $validTypes[$this->type]($this->controller);
    }
}
