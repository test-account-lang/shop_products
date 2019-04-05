<?php

namespace Shop_products\Models;

use Exception;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Validation;
use Shop_products\Enums\ProductTypesEnums;
use Shop_products\Validators\UuidValidator;

/**
 * Product
 * 
 * @package Shop_products\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2019-01-11, 16:38:52
 */
class Product extends Base
{
    const WHITE_LIST = [
        'productId',
        'productCategoryId',
        'productUserId',
        'productVendorId',
        'productTitle',
        'productLinkSlug',
        'productType',
        'productCustomPageId',
        'productPrice',
        'productSalePrice',
        'productSaleEndTime',
        'productKeywords',
        'productSegments',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'isPublished',
        'isDeleted'
    ];

    /**
     * @var string
     */
    public $productId;

    /**
     * @var string
     */
    public $productCategoryId;

    /**
     * @var string
     */
    public $productUserId;

    /**
     * @var string
     */
    public $productVendorId;

    /**
     * @var string
     */
    public $productTitle;

    /**
     * @var string
     */
    public $productType;

    /**
     *
     * @var string
     */
    public $productLinkSlug;

    /**
     * @var string
     */
    public $productCustomPageId;

    /**
     * @var float
     */
    public $productPrice;

    /**
     * @var float
     */
    public $productSalePrice;

    /**
     * @var string
     */
    public $productSaleEndTime;

    /**
     * @var string
     */
    public $createdAt;

    /**
     * @var string
     */
    public $updatedAt;

    /**
     * @var string
     */
    public $deletedAt;

    /**
     * @var bool
     */
    public $isPublished;

    /**
     * @var array
     * This value appended from Mongo Collection
     */
    private $productKeywords;

    /**
     * @var array
     * This value appended from Mongo Collection
     */
    private $productSegments;

    /**
     *
     * @var integer
     */
    public $isDeleted;

    public function onConstruct()
    {
        self::$instance = $this;
    }

    /**
     * @param array $data
     * @param null $dataColumnMap
     * @param null $whiteList
     * @return $this|\Phalcon\Mvc\Model
     */
    public function assign(array $data, $dataColumnMap = null, $whiteList = null)
    {
        foreach ($data as $attribute => $value) {
            if (!in_array($attribute, $whiteList)) {
                continue;
            }
            $this->writeAttribute($attribute, $value);
        }
        return $this;
    }

    /**
     * Initialize method for model.
     * @throws Exception
     */
    public function initialize()
    {
        $this->setSchema("shop_products");
        $this->setSource("product");
        $this->defaultBehavior();

        $this->useDynamicUpdate(true);

        $this->hasMany('productId', 'Shop_products\Models\ProductImages', 'productId', ['alias' => 'ProductImages']);
        $this->hasMany('productId', 'Shop_products\Models\ProductQuestions', 'questionProductId', ['alias' => 'ProductQuestions']);
        $this->hasMany('productId', 'Shop_products\Models\ProductRate', 'rateProductId', ['alias' => 'ProductRate']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'product';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Product[]|Product|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        $operator = '';
        if (!empty($parameters['conditions'])) {
            $operator = ' AND ';
        }
        $parameters['conditions'] .= $operator.'isDeleted = false';
        $parameters['hydration'] = Resultset::HYDRATE_RECORDS;
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ModelInterface
     */
    public static function findFirst($parameters = null)
    {
        $query = self::find($parameters);
        return $query->getFirst();
    }

    /**
     * @param string $type
     * @return Base|DownloadableProduct|PhysicalProduct|Product
     */
    public function detectModelType(string $type)
    {
        switch ($type) {
            case ProductTypesEnums::TYPE_PHYSICAL:
                $model = PhysicalProduct::model(true);
                break;
            case ProductTypesEnums::TYPE_DOWNLOADABLE:
                $model = DownloadableProduct::model(true);
                break;
            default:
                $model = Product::model();
        }
        return $model;
    }

    /**
     * Map Resultset to appropriate model
     * @param Product $product
     */
    public function mapResultSet(Product &$product)
    {
        $model = $this->detectModelType($product->productType);
        $product = parent::cloneResult($model, $product->toArray());
    }

    public static function getWhiteList()
    {
        return self::WHITE_LIST;
    }

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return [
            'product_id' => 'productId',
            'product_category_id' => 'productCategoryId',
            'product_user_id' => 'productUserId',
            'product_vendor_id' => 'productVendorId',
            'product_title' => 'productTitle',
            'product_link_slug' => 'productLinkSlug',
            'product_type' => 'productType',
            'product_custom_page_id' => 'productCustomPageId',
            'product_price' => 'productPrice',
            'product_sale_price' => 'productSalePrice',
            'product_sale_end_time' => 'productSaleEndTime',
            'product_weight' => 'productWeight',
            'product_brand_id' => 'productBrandId',
            'product_digital_size' => 'productDigitalSize',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'deleted_at' => 'deletedAt',
            'is_published' => 'isPublished',
            'is_deleted' => 'isDeleted'
        ];
    }

    public function toApiArray()
    {
        return [
            'productId' => $this->productId,
            'productCategoryId' => $this->productCategoryId,
            'productVendorId' => $this->productVendorId,
            'productTitle' => $this->productTitle,
            'productType' => $this->productType,
            'productLinkSlug' => $this->productLinkSlug,
            'productCustomPageId' => $this->productCustomPageId,
            'productPrice' => $this->productPrice,
            'productSalePrice' => $this->productSalePrice,
            'productSaleEndTime' => $this->productSaleEndTime,
            'productKeywords' => $this->productKeywords ?? null,
            'productSegments' => $this->productSegments ?? null
        ];
    }

    /**
     * @return \Phalcon\Config
     */
    private function getTitleValidationConfig()
    {
        return $this->getDI()->getConfig()->application->validation->productTitle;
    }

    /**
     * @return bool
     */
    public function validation()
    {
        $validation = new Validation();

        $validation->add(
            ['productCategoryId', 'productVendorId', 'productUserId'],
            new UuidValidator()
        );

        $validation->add(
            ['productCustomPageId'],
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        // Validate English input
        $validation->add(
            'productTitle',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    $name = preg_replace('/[\d\s_]/i', '', $data['productTitle']); // clean string
                    if (preg_match('/[a-z]/i', $name) == false) {
                        return false;
                    }
                    return true;
                },
                'message' => 'English language only supported'
            ])
        );

        $validation->add(
            'productTitle',
            new Validation\Validator\AlphaNumericValidator([
                'whiteSpace' => $this->getTitleValidationConfig()->whiteSpace,
                'underscore' => $this->getTitleValidationConfig()->underscore,
                'min' => $this->getTitleValidationConfig()->min,
                'max' => $this->getTitleValidationConfig()->max,
                'message' => 'Product title should contain only letters'
            ])
        );

        if ($this->_operationMade == self::OP_CREATE) {
            $validation->add(
                'productType',
                new Validation\Validator\InclusionIn([
                    'domain' => ProductTypesEnums::getValues(),
                    'allowEmpty' => true,
                    'message' => 'Product type should be physical or downloadable'
                ])
            );
        }

        $validation->add(
            'productPrice',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0
            ])
        );

        $validation->add(
            'productSalePrice',
            new Validation\Validator\NumericValidator([
                'allowFlout' => true,
                'min' => 0,
                'allowEmpty' => true
            ])
        );

        $validation->add(
            'productSaleEndTime',
            new Validation\Validator\Date([
                'format' => 'Y-m-d H:i:s',
                'allowEmpty' => true
            ])
        );

        $message = $validation->validate([
            'productCategoryId' => $this->productCategoryId,
            'productVendorId' => $this->productVendorId,
            'productUserId' => $this->productUserId,
            'productCustomPageId' => $this->productCustomPageId,
            'productTitle' => $this->productTitle,
            'productPrice' => $this->productPrice,
            'productSalePrice' => $this->productSalePrice,
            'productSaleEndTime' => $this->productSaleEndTime
        ]);

        $this->_errorMessages = $message;

        return !$message->count();
    }
}
