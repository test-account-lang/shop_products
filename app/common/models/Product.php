<?php

namespace app\common\models;

use app\common\validators\rules\AbstractProductRules;
use app\common\validators\rules\DownloadableProductRules;
use app\common\validators\rules\PhysicalProductRules;
use app\common\validators\SpecialCharactersValidator;
use Phalcon\Mvc\Model;
use Phalcon\Validation;
use app\common\enums\ProductTypesEnum;
use app\common\validators\TypeValidator;
use app\common\validators\UuidValidator;

/**
 * Product
 * 
 * @package app\common\models
 * @autogenerated by Phalcon Developer Tools
 * @date 2019-01-11, 16:38:52
 * @property PhysicalProperties $pp
 * @property DownloadableProperties $dp
 * @property-read  ProductImages[] $pi
 * @property-read  ProductQuestions[] $pq
 * @property-read  ProductRates[] $pr
 * @property-read array $productSegments
 * @property-read array $productKeywords
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
        'packageDimensions',
        'isPublished'
    ];

    const MODEL_ALIAS = 'p';

    /**
     * @var string
     * @Primary
     * @Column(column='product_id', type='string', length=37)
     */
    public $productId;

    /**
     * @var string
     * @Column(column='product_category_id', type='string', length=36)
     */
    public $productCategoryId;

    /**
     * @var string
     * @Column(column='product_user_id', type='string', length=36)
     */
    public $productUserId;

    /**
     * @var string
     * @Column(column='product_vendor_id', type='string', length=36)
     */
    public $productVendorId;

    /**
     * @var string
     * @Column(column='product_title', type='text')
     */
    public $productTitle;

    /**
     * @var string
     * @Column(column='product_type', type='string')
     */
    public $productType;

    /**
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
     * @Column(column='product_price', type='float')
     */
    public $productPrice;

    /**
     * @var float
     * @Column(column='product_sale_price', type='float', nullable=true)
     */
    public $productSalePrice;

    /**
     * @var string
     * @Column(column='product_sale_end_time', type='datetime', nullable=true)
     */
    public $productSaleEndTime;

    /**
     * @var string
     * @Column(column='created_at', type='datetime')
     */
    public $createdAt;

    /**
     * @var string
     * @Column(column='updated_at', type='datetime', nullable=true)
     */
    public $updatedAt;

    /**
     * @var string
     * @Column(column='deleted_at', type='string', nullable=true)
     */
    public $deletedAt;

    /**
     * @var bool
     * @Column(column='is_published', type='boolean', default=0)
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

    /**
     * @var ProductImages[]
     * This value appended from related tables
     */
    public $productImages = [];

    /**
     * @var array
     * This value appended from related tables
     */
    public $productQuestions = [];

    /**
     * @var array
     * This value appended from related tables
     */
    public $productRates = [];

    private static $attachRelations = false;
    private static $attachProperties = true;
    private static $editMode = false;

    /**
     *
     * @var integer
     * @Column(column='is_deleted', type='boolean', nullable=false, default=0)
     */
    public $isDeleted;

    /**
     * @var PhysicalProductRules
     */
    private $physicalProductRules;

    /**
     * @var DownloadableProductRules
     */
    private $downloadableProductRules;

    public static function model(bool $new = false, bool $attachRelations = true, bool $editMode = false, bool $attachProperties = true)
    {
        self::$attachRelations = $attachRelations;
        self::$attachProperties = $attachProperties;
        self::$editMode = $editMode;
        return parent::model($new);
    }

    /**
     * Initialize method for model.
     * @throws \Exception
     */
    public function initialize()
    {
        $this->setSchema("shop_products");
        $this->setSource("product");
        $this->defaultBehavior();

        $this->useDynamicUpdate(true);

        if (self::$attachProperties) {
            $this->hasOne(
                'productId',
                PhysicalProperties::class,
                'productId',
                [
                    'alias' => 'pp'
                ]
            );

            $this->hasOne(
                'productId',
                DownloadableProperties::class,
                'productId',
                [
                    'alias' => 'dp'
                ]
            );
        }

        if (self::$attachRelations) {
            $this->hasMany('productId', ProductImages::class, 'productId', [
                'alias' => 'pi',
                'params' => [
                    'conditions' => 'isDeleted = false'
                ]
            ]);

            $this->hasMany('productId', ProductQuestions::class, 'productId', [
                'alias' => 'pq',
                'params' => [
                    'conditions' => 'isDeleted = false'
                ]
            ]);

            $this->hasMany('productId', ProductRates::class, 'productId', [
                'alias' => 'pr',
                'params' => [
                    'conditions' => 'isDeleted = false'
                ]
            ]);
        }
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
     * @return Model\ResultsetInterface|Product[]
     */
    static public function find($parameters = null)
    {
        $operator = '';
        if (!array_key_exists('conditions', $parameters)) {
            $parameters['conditions'] = '';
        }
        if (!empty($parameters['conditions'])) {
            $operator = ' AND ';
        }
        $parameters['conditions'] .= $operator.'isDeleted = false';
        return parent::find($parameters);
    }

    /**
     * @param null $parameters
     * @return Model|void|Product
     */
    public static function findFirst($parameters = null)
    {
        $query = self::find($parameters);
        return $query->getFirst();
    }

    /**
     * @return array
     */
    public static function getWhiteList()
    {
        return self::WHITE_LIST;
    }

    /**
     * Fetch related data
     * @throws \Exception
     */
    public function afterFetch()
    {
        if (self::$editMode) {
            $this->exposedFields['isPublished'] = (bool) $this->isPublished;
        }

        if (self::$attachProperties) {
            if ($this->productType == ProductTypesEnum::TYPE_PHYSICAL) {
                $this->assign($this->pp->toApiArray(), null, PhysicalProperties::WHITE_LIST);
            } else {
                $this->assign($this->dp->toApiArray(), null, DownloadableProperties::WHITE_LIST);
            }
        }

        if (self::$attachRelations) {
            $images = $questions = $rates = [];

            $this->pi->filter(function ($image) use (&$images) {
                /** @var ProductImages $image */
                $images[] = $image->toApiArray();
            });

            $this->pq->filter(function ($question) use (&$questions) {
                /** @var ProductQuestions $question */
                $questions[] = $question->toApiArray();
            });

            $this->pr->filter(function ($rate) use (&$rates) {
                /** @var ProductRates $rate */
                $rates[] = $rate->toApiArray();
            });

            $this->productImages = $images;
            $this->productQuestions = $questions;
            $this->productRates = $rates;
        }
    }

    public function afterSave()
    {
        $this->afterFetch();
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
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'deleted_at' => 'deletedAt',
            'is_published' => 'isPublished',
            'is_deleted' => 'isDeleted'
        ];
    }

    public function toApiArray()
    {
        return array_merge(
            [
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
                'productSegments' => $this->productSegments ?? null
            ],
            (self::$attachRelations) ? [
                'productImages' => $this->productImages,
                'productQuestions' => $this->productQuestions,
                'productRates' => $this->productRates
            ] : [],
            $this->exposedFields
        );
    }

    /**
     * @return PhysicalProductRules
     */
    private function getPhysicalProductRules(): PhysicalProductRules
    {
        return $this->physicalProductRules ??
            $this->physicalProductRules = new PhysicalProductRules();
    }

    /**
     * @return DownloadableProductRules
     */
    private function getDownloadableProductRules(): DownloadableProductRules
    {
        return $this->downloadableProductRules ??
            $this->downloadableProductRules = new DownloadableProductRules();
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

        /** @noinspection PhpUndefinedFieldInspection */
        $validation->add(
            'productTitle',
            new SpecialCharactersValidator([
                'allowEmpty' => false
            ])
        );

        if ($this->_operationMade == self::OP_CREATE) {
            $validation->add(
                'productType',
                new Validation\Validator\InclusionIn([
                    'domain' => ProductTypesEnum::getValues(),
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
                'allowFloat' => true,
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

        $this->_errorMessages = $validation->validate([
            'productCategoryId' => $this->productCategoryId,
            'productVendorId' => $this->productVendorId,
            'productUserId' => $this->productUserId,
            'productCustomPageId' => $this->productCustomPageId,
            'productTitle' => $this->productTitle,
            'productPrice' => $this->productPrice,
            'productSalePrice' => $this->productSalePrice,
            'productSaleEndTime' => $this->productSaleEndTime,
            'productType' => $this->productType
        ]);

        return !$this->_errorMessages->count();
    }
}
