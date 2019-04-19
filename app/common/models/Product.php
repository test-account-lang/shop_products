<?php
declare(strict_types= 1);

namespace Shop_products\Models;

use Exception;
use Phalcon\Config;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultSetInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Validation;
use Shop_products\Enums\ProductTypesEnums;
use Shop_products\Validators\TypeValidator;
use Shop_products\Validators\UuidValidator;

/**
 * Product
 * 
 * @package Shop_products\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2019-01-11, 16:38:52
 * @property-read ProductImages $productImages
 * @property-read ProductQuestions $productQuestions
 * @property-read ProductRates $productRates
 */
class Product extends BaseModel
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
        'productAlbumId',
        'productAlbumDeleteHash',
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
     * @Primary
     * @Column(column='product_id', type='string', length=36, nullable=false)
     */
    public $productId;

    /**
     * @var string
     * @Column(column='product_category_id', type='string', length=36, nullable=false)
     */
    public $productCategoryId;

    /**
     * @var string
     * @Column(column='product_user_id', type='string', length=36, nullable=false)
     */
    public $productUserId;

    /**
     * @var string
     * @Column(column='product_vendor_id', type='string', length=36, nullable=false)
     */
    public $productVendorId;

    /**
     * @var string
     * @Column(column='product_title', type='text', nullable=false)
     */
    public $productTitle;

    /**
     * @var string
     * @Column(column='product_type', type='string', nullable=false)
     */
    public $productType;

    /**
     *
     * @var string
     * @Column(column='product_link_slug', type='text')
     */
    public $productLinkSlug;

    /**
     * @var string
     * @Column(column='product_custom_page_id', type='string', length=36, nullable=true)
     */
    public $productCustomPageId;

    /**
     * @var string
     * @Column(column='product_album_id', type='string', length=10)
     */
    public $productAlbumId;

    /**
     * @var string
     * @Column(column='product_album_delete_hash', type='string', length=20)
     */
    public $productAlbumDeleteHash;

    /**
     * @var float
     * @Column(column='product_price', type='float', nullable=false)
     */
    public $productPrice;

    /**
     * @var float
     * @Column(column='product_sale_price', type='float')
     */
    public $productSalePrice;

    /**
     * @var string
     * @Column(column='product_sale_end_time', type='datetime')
     */
    public $productSaleEndTime;

    /**
     * @var string
     * @Column(column='created_at', type='datetime', nullable=false)
     */
    public $createdAt;

    /**
     * @var string
     * @Column(column='updated_at', type='datetime')
     */
    public $updatedAt;

    /**
     * @var string
     * @Column(column='deleted_at', type='string', nullable=true)
     */
    public $deletedAt;

    /**
     * @var bool
     * @Column(column='is_published', type='boolean', nullable=false, default=0)
     */
    public $isPublished = false;

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

    private $images;
    private $questions;
    private $rates;

    /**
     *
     * @var integer
     * @Column(column='is_deleted', type='boolean', nullable=false, default=0)
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
     * @return $this|Model
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

        $this->hasMany('productId', ProductImages::class, 'productId', ['alias' => 'productImages']);
        $this->hasMany('productId', ProductQuestions::class, 'productId', ['alias' => 'productQuestions']);
        $this->hasMany('productId', ProductRates::class, 'productId', ['alias' => 'productRates']);
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
     * @return Product[]|Product|ResultSetInterface
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
     * @return BaseModel|DownloadableProduct|PhysicalProduct|Product
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

    public function afterFetch()
    {
        $images = $questions = $rates = [];

        if (!empty($this->productImages)) {
            $this->productImages->filter(function ($image) use (&$images) {
                $images[] = $image->toApiArray();
            });
        }

        if (!empty($this->productQuestions)) {
            $this->productQuestions->filter(function ($question) use(&$questions) {
                $questions[] = $question->toApiArray();
            });
        }

        if (!empty($this->productRates)) {
            $this->productRates->filter(function ($rate) use(&$rates) {
                $rates[] = $rate->toApiArray();
            });
        }

        $this->images = $images;
        $this->questions = $questions;
        $this->rates = $rates;
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
            'product_album_id' => 'productAlbumId',
            'product_album_delete_hash' => 'productAlbumDeleteHash',
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
            'productPrice' => (float) $this->productPrice,
            'productSalePrice' => (float) $this->productSalePrice,
            'productSaleEndTime' => $this->productSaleEndTime,
            'productAlbumId' => $this->productAlbumId,
            'productKeywords' => $this->productKeywords ?? null,
            'productSegments' => $this->productSegments ?? null,
            'productImages' => $this->images,
            'productQuestions' => $this->questions,
            'productRates' => $this->rates,
            'isPublished' => (bool) $this->isPublished
        ];
    }

    /**
     * @return Config
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
            'productCustomPageId',
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
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT,
                'allowEmpty' => $this->_operationMade == self::OP_CREATE ? false : true
            ])
        );

        $validation->add(
            'productSalePrice',
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT
            ])
        );

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
