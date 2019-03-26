<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:54 م
 */

namespace Shop_products\Services;


use Shop_products\Enums\QueueNamesEnum;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Repositories\ProductRepository;
use Shop_products\RequestHandler\Queue\QueueRequestHandler;
use Shop_products\Services\Cache\ProductCache;

class ProductsService
{
    /** @var QueueRequestHandler */
    private $queueRequestHandler;

    /** @var ProductCache */
    private static $productsCacheInstance;

    /** @var ProductRepository */
    private static $productsRepository;

    /**
     * @return ProductCache
     * @throws \RedisException
     */
    private static function getProductsCacheInstance(): ProductCache
    {
        return self::$productsCacheInstance ?? self::$productsCacheInstance = ProductCache::getInstance();
    }

    /**
     * @return ProductRepository
     */
    private static function getProductsRepository(): ProductRepository
    {
        return self::$productsRepository ?? self::$productsRepository = ProductRepository::getInstance();
    }

    /**
     * @return ProductCache|ProductRepository
     * @throws \Exception
     */
    private function getDataSource()
    {
        try {
            return ProductCache::getInstance();
        } catch (\RedisException $exception) {
            return ProductRepository::getInstance();
        } catch (\Throwable $exception) {
            throw new \Exception('No data source available');
        }
    }

    private function getRepository()
    {
        return ProductRepository::getInstance();
    }

    /**
     * @param string $requestType
     * @return QueueRequestHandler
     */
    public function getQueueRequestHandler($requestType = QueueRequestHandler::REQUEST_TYPE_SYNC): QueueRequestHandler
    {
        return $this->queueRequestHandler ??
            $this->queueRequestHandler = new QueueRequestHandler($requestType);
    }

    /**
     * Check if category exists
     *
     * @param string $categoryId
     * @param string $vendorId
     *
     * @throws \ErrorException
     * @throws \Exception
     */
    public function checkCategoryExistence(string $categoryId, string $vendorId)
    {
        $exists = $this->getQueueRequestHandler()
            ->setQueueName(QueueNamesEnum::CATEGORY_SYNC_QUEUE)
            ->setService('category')
            ->setMethod('getCategory')
            ->setData([
                'categoryId' => $categoryId
            ])
            ->setServiceArgs([
                'vendorId' => $vendorId
            ])
            ->sendSync();

        if (empty($exists)) {
            throw new \Exception('Category not found or maybe deleted', 404);
        }
    }

    /**
     * @param array $identifier
     * @return array
     * @throws \Exception
     */
    public function getAll(array $identifier)
    {
        if (!empty($identifier['vendorId']) && empty($identifier['categoryId'])) {
            return $this->getDataSource()->getByVendorId($identifier['vendorId']);
        } elseif (!empty($identifier['vendorId']) && !empty($identifier['categoryId'])) {
            return $this->getDataSource()->getByCategoryId($identifier['categoryId'], $identifier['vendorId']);
        } else {
            throw new \Exception('Unknown modifier');
        }
    }

    /**
     * Get product by id
     *
     * @param string $vendorId
     * @param string $categoryId
     * @param string $productId
     * @return \Phalcon\Mvc\ModelInterface|\Shop_products\Models\Product
     *
     * @throws \Exception
     */
    public function getProduct(string $vendorId, string $categoryId, string $productId)
    {
        return self::getDataSource()->getById($productId, $vendorId, $categoryId);
    }

    /**
     * Create product
     *
     * @param array $data
     * @return array
     * @throws ArrayOfStringsException
     * @throws \Exception
     */
    public function create(array $data)
    {
        $this->checkCategoryExistence($data['productCategoryId'], $data['productVendorId']);
        $product = $this->getRepository()->create($data);
        try {
            ProductCache::getInstance()->setInCache($data['productVendorId'], $data['productCategoryId'], $data);
            ProductCache::indexProduct($product->toApiArray());
        } catch (\RedisException $exception) {
            // do nothing
        }
        return $product->toApiArray();
    }

    /**
     * Update product
     *
     * @param string $productId
     * @param array $data
     * @return array
     * @throws ArrayOfStringsException
     * @throws \Exception
     */
    public function update(string $productId, array $data)
    {
        $product = self::getRepository()->update($productId, $data)->toApiArray();
        try {
            ProductCache::getInstance()->updateCache($product['productVendorId'], $product['productCategoryId'], $productId, $product);
        } catch (\RedisException $exception) {
            // do nothing
        }
        return $product;
    }

    /**
     * Delete product
     *
     * @param string $productId
     * @return bool
     * @throws \Exception
     */
    public function delete(string $productId)
    {
        $deletedProduct = self::getRepository()->delete($productId);
        try {
            ProductCache::getInstance()->invalidateCache($deletedProduct['productVendorId'], $deletedProduct['productCategoryId'], [$productId]);
        } catch (\RedisException $exception) {
            // do nothing
        }
        return true;
    }

    /**
     * @param string $productId
     * @throws \Phalcon\Mvc\Collection\Exception
     * @throws ArrayOfStringsException
     */
    public function deleteExtraInfo(string $productId): void
    {
        /** Delete product related document */
        ProductRepository::getInstance()
            ->getCollection()::findFirst([
                ['product_id' => $productId]
            ])->delete();

        /** Delete product cache index */
        ProductCache::deleteProductIndex($productId);
    }
}