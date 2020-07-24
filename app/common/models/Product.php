<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 04:38 PM
 */

namespace app\common\models;


use app\common\models\factory\PropertiesFactory;
use app\common\models\embedded\{PhysicalProperties, Properties, Variation};
use app\common\validators\{
    SpecialCharactersValidator,
    TypeValidator,
    UuidValidator
};
use Phalcon\Mvc\Model\Transaction\{
    Failed as TxFailed,
    Manager as TxManager
};
use app\common\enums\{
    ProductTypesEnum,
    QuantityOperatorsEnum
};
use Phalcon\Mvc\Model;
use Phalcon\Validation;
use app\common\exceptions\OperationFailed;

/**
 * Product
 * 
 * @package app\common\models
 * @autogenerated by Phalcon Developer Tools
 * @date 2019-01-11, 16:38:52
 * @property-read  Image[] $productImages
 * @property-read  ProductQuestions[] $productQuestions
 * @property-read  ProductRates[] $productRates
 * @property Variation[] $variations
 */
class Product extends BaseModel
{
    const WHITE_LIST = [
        'productId',
        'productCategoryId',
        'productUserId',
        'productStoreId',
        'productTitle',
        'productLinkSlug',
        'productType',
        'productCustomPageId',
        'productAlbumId',
        'productBrandId',
        'productAlbumDeleteHash',
        'productPrice',
        'productSalePrice',
        'productSaleEndTime',
        'productQuantity',
        'isPublished'
    ];

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
     * @Column(column='product_store_id', type='string', length=36)
     */
    public $productstoreId;

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
     * @Column(column='product_album_id', type='string', length=10, nullable=true)
     */
    public $productAlbumId;

    /**
     * @var string
     * @Column(column='product_brand_id', type='string', length=36, nullable=true)
     */
    public $productBrandId;

    /**
     * @var string
     * @Column(column='product_album_delete_hash', type='string', length=20, nullable=true)
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
     * @var int
     * @Column(column='product_quantity', type='integer', nullable=false)
     */
    public $productQuantity = 0;

    /**
     * @var \DateTime
     * @Column(column='created_at', type='datetime')
     */
    public $createdAt;

    /**
     * @var \DateTime
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

    /** @var Properties */
    public $properties;

    private static $attachRelations = false;
    private static $editMode = false;

    /**
     *
     * @var integer
     * @Column(column='is_deleted', type='boolean', nullable=false, default=0)
     */
    public $isDeleted;

    /**
     * @param bool $new
     * @param bool $attachRelations
     * @param bool $editMode
     * @return mixed
     */
    public static function model(bool $new = false, bool $attachRelations = true, bool $editMode = false)
    {
        self::$attachRelations = $attachRelations;
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

        if (self::$attachRelations) {
            $this->hasMany('productId', Image::class, 'productId', [
                'alias' => 'productImages',
                'params' => [
                    'conditions' => 'isDeleted = false AND isVariationImage = false AND isRateImage = false'
                ]
            ]);

            $this->hasMany('productId', ProductQuestions::class, 'productId', [
                'alias' => 'productQuestions',
                'params' => [
                    'conditions' => 'isDeleted = false'
                ]
            ]);

            $this->hasMany('productId', ProductRates::class, 'productId', [
                'alias' => 'productRates',
                'params' => [
                    'conditions' => 'isDeleted = false'
                ]
            ]);
        }

        $this->hasMany(
            'productId',
            Variation::class,
            'productId',
            [
                'alias' => 'variations',
                'params' => [
                    'conditions' => 'isDeleted = false'
                ]
            ]
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'products';
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

    public static function count($parameters = null)
    {
        $operator = '';
        if (!array_key_exists('conditions', $parameters)) {
            $parameters['conditions'] = '';
        }
        if (!empty($parameters['conditions'])) {
            $operator = ' AND ';
        }
        $parameters['conditions'] .= $operator.'isDeleted = false';
        return parent::count($parameters);
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
        // Cast real data types
        $this->productQuantity = (int) $this->productQuantity;
        $this->isDeleted = (bool) $this->isDeleted;
        $this->isPublished = (bool) $this->isPublished;
        $this->productPrice = (float) $this->productPrice;
        $this->productSalePrice = (float) $this->productSalePrice;
        $this->createdAt = new \DateTime($this->createdAt);
        $this->updatedAt = $this->updatedAt ? new \DateTime($this->updatedAt) : null;
        $this->properties = PropertiesFactory::create($this)::findFirst([
            ['productId' => $this->productId]
        ]);
    }

    public function beforeValidationOnCreate()
    {
        $this->productId = $this->getDI()->getSecurity()->getRandom()->uuid();
    }

    public function beforeUpdate()
    {
        $this->createdAt = $this->createdAt->format(self::$dateFormat);
    }

    public function afterSave()
    {
        $this->afterFetch();
    }

    /**
     * @param bool $assoc
     * @return array
     */
    private function getImages(bool $assoc = false): array
    {
        return $this->productImages->filter(function ($image) use ($assoc) {
            return $assoc ? $image->toApiArray() : $image;
        });
    }

    /**
     * @param bool $assoc
     * @return array
     */
    private function getQuestions(bool $assoc = false): array
    {
        return $this->productQuestions->filter(function ($question) use ($assoc) {
            return $assoc ? $question->toApiArray() : $question;
        });
    }

    /**
     * @param bool $assoc
     * @return array
     */
    private function getRates(bool $assoc = false): array
    {
        return $this->productRates->filter(function ($rate) use ($assoc) {
            return $assoc ? $rate->toApiArray() : $rate;
        });
    }

    /**
     * @param bool $assoc
     * @return array
     */
    private function getVariations(bool $assoc = false): array
    {
        return $this->variations->filter(function ($variation) use ($assoc) {
            return $assoc ? $variation->toApiArray() : $variation;
        });
    }

    /**
     * @param int $amount
     * @param string $operation
     * @return $this
     * @throws OperationFailed
     */
    public function updateQuantity(int $amount, string $operation)
    {
        if ($operation == QuantityOperatorsEnum::OPERATOR_INCREMENT) {
            $remaining = $this->productQuantity + $amount;
        } elseif ($operation == QuantityOperatorsEnum::OPERATOR_DECREMENT) {
            $remaining = $this->productQuantity - $amount;
        } else {
            throw new \InvalidArgumentException('unknown operation', 400);
        }

        if ($remaining < 0) {
            throw new \InvalidArgumentException('amount is bigger than quantity', 400);
        }

        $transaction = new TxManager($this->getDI());
        try {
            $this->setTransaction($transaction->getOrCreateTransaction());
            if (!$this->update(['productQuantity' => $remaining])) {
                $transaction->rollback();
                throw new OperationFailed($this->getMessages());
            }
            $transaction->commit();
            return $this;
        } catch (TxFailed $exception) {
            $transaction->rollback();
            throw new OperationFailed($exception->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function hasVariations(): bool
    {
        return count($this->variations) > 0;
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
            'product_store_id' => 'productStoreId',
            'product_title' => 'productTitle',
            'product_link_slug' => 'productLinkSlug',
            'product_type' => 'productType',
            'product_custom_page_id' => 'productCustomPageId',
            'product_brand_id' => 'productBrandId',
            'product_album_id' => 'productAlbumId',
            'product_album_delete_hash' => 'productAlbumDeleteHash',
            'product_price' => 'productPrice',
            'product_sale_price' => 'productSalePrice',
            'product_sale_end_time' => 'productSaleEndTime',
            'product_quantity' => 'productQuantity',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'deleted_at' => 'deletedAt',
            'is_published' => 'isPublished',
            'is_deleted' => 'isDeleted'
        ];
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return array_merge(
            [
                'productId' => $this->productId,
                'productCategoryId' => $this->productCategoryId,
                'productStoreId' => $this->productStoreId,
                'productTitle' => $this->productTitle,
                'productType' => $this->productType,
                'productLinkSlug' => $this->productLinkSlug,
                'productCustomPageId' => $this->productCustomPageId,
                'productBrandId' => $this->productBrandId,
                'productPrice' => (float) $this->productPrice,
                'productSalePrice' => (float) $this->productSalePrice,
                'productSaleEndTime' => $this->productSaleEndTime,
                'productAlbumId' => $this->productAlbumId,
                'productQuantity' => $this->productQuantity
            ],
            ['productVariations' => $this->hasVariations() ? $this->getVariations(true) : []],
            (self::$attachRelations) ? [
                'productImages' => $this->getImages(true),
                'productQuestions' => $this->getQuestions(true),
                'productRates' => $this->getRates(true)
            ] : [],
            $this->properties ? $this->properties->toApiArray() : [],
            ['createdAt' => $this->createdAt->format(self::$dateFormat)],
            ($this->updatedAt) ? ['updatedAt' => $this->updatedAt->format(self::$dateFormat)] : [],
            self::$editMode ? ['isPublished' => (bool) $this->isPublished] : []
        );
    }

    /**
     * @return bool
     */
    public function validation()
    {
        $validation = new Validation();

        $validation->add(
            ['productCategoryId', 'productStoreId', 'productUserId'],
            new UuidValidator()
        );

        $validation->add(
            ['productCustomPageId', 'productBrandId'],
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

        $validation->add(
            'productQuantity',
            new Validation\Validator\NumericValidator([
                'min' => 1,
                'allowFloat' => false,
                'allowSign' => false,
                'allowEmpty' => false
            ])
        );

        $validation->add(
            'productBrandId',
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $this->_errorMessages = $validation->validate([
            'productCategoryId' => $this->productCategoryId,
            'productStoreId' => $this->productStoreId,
            'productUserId' => $this->productUserId,
            'productCustomPageId' => $this->productCustomPageId,
            'productBrandId' => $this->productBrandId,
            'productTitle' => $this->productTitle,
            'productPrice' => $this->productPrice,
            'productSalePrice' => $this->productSalePrice,
            'productSaleEndTime' => $this->productSaleEndTime,
            'productType' => $this->productType,
            'productQuantity' => $this->productQuantity
        ]);

        return !$this->_errorMessages->count();
    }
}
