<?php
declare(strict_types=1);
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 07:04 م
 */

namespace app\common\requestHandler\product;

use Phalcon\Di;
use Phalcon\Utils\Slug;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\controllers\BaseController;
use app\common\exceptions\OperationFailed;
use app\common\requestHandler\RequestHandlerInterface;
use app\common\utils\DigitalUnitsConverterUtil;
use app\common\validators\TypeValidator;
use app\common\validators\UuidValidator;

class UpdateRequestHandler extends BaseController implements RequestHandlerInterface
{
    private $title;
    private $categoryId;
    private $linkSlug;
    private $customPageId;
    private $price;
    private $salePrice;
    private $endSaleTime;
    private $keywords;
    private $isPublished;
    private $brandId;
    private $weight;
    private $packageDimensions;
    private $digitalSize;

    protected $errorMessages;

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @param mixed $categoryId
     */
    public function setCategoryId($categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @param mixed $linkSlug
     */
    public function setLinkSlug($linkSlug): void
    {
        $this->linkSlug = $linkSlug;
    }

    /**
     * @param mixed $customPageId
     */
    public function setCustomPageId($customPageId): void
    {
        $this->customPageId = $customPageId;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * @param mixed $salePrice
     */
    public function setSalePrice($salePrice): void
    {
        $this->salePrice = $salePrice;
    }

    /**
     * @param mixed $endSaleTime
     */
    public function setEndSaleTime($endSaleTime): void
    {
        $this->endSaleTime = $endSaleTime;
    }

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords): void
    {
        $this->keywords = $keywords;
    }

    /**
     * @param mixed $brandId
     */
    public function setBrandId($brandId): void
    {
        $this->brandId = $brandId;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @param mixed $packageDimensions
     */
    public function setPackageDimensions($packageDimensions): void
    {
        $this->packageDimensions = $packageDimensions;
    }

    /**
     * @param mixed $digitalSize
     */
    public function setDigitalSize($digitalSize): void
    {
        $this->digitalSize = $digitalSize;
    }

    /**
     * @param mixed $isPublished
     */
    public function setIsPublished($isPublished): void
    {
        $this->isPublished = $isPublished;
    }

    private function getValidationConfig()
    {
        return Di::getDefault()->getConfig()->application->validation->productTitle;
    }

    private function getDigitalProductValidationConfig()
    {
        return $this->getDI()->getConfig()->application->validation->downloadable;
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        // Validate English input
        $validator->add(
            'name',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    $name = preg_replace('/[\d\s_]/i', '', $data['title']); // clean string
                    if (!empty($name) && preg_match('/[a-z]/i', $name) == false) {
                        return false;
                    }
                    return true;
                },
                'message' => 'English language only supported'
            ])
        );

        $validator->add(
            'title',
            new Validation\Validator\AlphaNumericValidator([
                'whiteSpace' => $this->getValidationConfig()->whiteSpace,
                'underscore' => $this->getValidationConfig()->underscore,
                'min' => $this->getValidationConfig()->min,
                'max' => $this->getValidationConfig()->max,
                'allowEmpty' => true
            ])
        );

        $validator->add(
            ['categoryId', 'customPageId', 'brandId'],
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'price',
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT
            ])
        );

        $validator->add(
            'salePrice',
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT
            ])
        );

        $validator->add(
            'price',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0,
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'salePrice',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0,
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'endSaleTime',
            new Validation\Validator\Date([
                'format' => 'Y-m-d H:i:s',
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'keywords',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    if (!empty($data['keywords'])) {
                        if (!is_array($data['keywords'])) {
                            return false;
                        }
                        foreach ($data['keywords'] as $keyword) {
                            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $keyword)) {
                                return false;
                            }
                        }
                    }
                    return true;
                },
                'message' => 'Invalid keywords'
            ])
        );

        $validator->add(
            'weight',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'packageDimensions',
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT,
                'allowEmpty' => true,
                'message' => 'Invalid dimensions'
            ])
        );

        $validator->add(
            'digitalSize',
            new Validation\Validator\NumericValidator([
                'min' => 1,
                'max' => $this->getDigitalProductValidationConfig()->maxDigitalSize,
                'messageMaximum' => 'Digital size exceeds the max limit ' .
                    DigitalUnitsConverterUtil::bytesToMb(
                        $this->getDigitalProductValidationConfig()->maxDigitalSize
                    ) . ' Mb',
                'messageMinimum' => 'Invalid digital size',
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'isPublished',
            new TypeValidator([
                'type' => TypeValidator::TYPE_BOOLEAN,
                'allowEmpty' => true
            ])
        );

        return $validator->validate([
            'title' => $this->title,
            'categoryId' => $this->categoryId,
            'customPageId' => $this->customPageId,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'endSaleTime' => $this->endSaleTime,
            'keywords' => $this->keywords,
            'brandId' => $this->brandId,
            'weight' => $this->weight,
            'packageDimensions' => $this->packageDimensions,
            'digitalSize' => $this->digitalSize,
            'isPublished' => $this->isPublished
        ]);
    }

    public function isValid(): bool
    {
        $messages = $this->validate();
        if (count($messages)) {
            foreach ($messages as $message) {
                $this->errorMessages[$message->getField()] = $message->getMessage();
            }
            return false;
        }
        return true;
    }

    public function notFound($message = 'Not Found')
    {
        // TODO: Implement notFound() method.
    }

    /**
     * @param null $message
     * @throws OperationFailed
     */
    public function invalidRequest($message = null)
    {
        if (!$message) {
            $message = $this->errorMessages;
        }
        throw new OperationFailed($message, 400);
    }

    public function successRequest($message = null)
    {
        http_response_code(200);
        return $this->response
            ->setJsonContent([
                'status' => 200,
                'message' => $message
            ]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        $result = [];

        if (!empty($this->title)) {
            $result['productTitle'] = $this->title;
            $result['productLinkSlug'] = (new Slug())->generate($this->title);
        }

        if (!empty($this->categoryId)) {
            $result['productCategoryId'] = $this->categoryId;
        }

        if (!empty($this->customPageId)) {
            $result['productCustomPageId'] = $this->customPageId;
        }

        if (!empty($this->price)) {
            $result['productPrice'] = $this->price;
        }

        if (!empty($this->salePrice)) {
            $result['productSalePrice'] = $this->salePrice;
        }

        if (!empty($this->endSaleTime)) {
            $result['productEndSaleTime'] = $this->endSaleTime;
        }

        if (!empty($this->keywords)) {
            $result['productKeywords'] = implode(',', $this->keywords);
        }

        if (!empty($this->brandId)) {
            $result['productBrandId'] = $this->brandId;
        }

        if (!empty($this->weight)) {
            $result['productWeight'] = $this->weight;
        }

        if (!empty($this->packageDimensions)) {
            $result['productPackageDimensions'] = $this->packageDimensions;
        }

        if (!empty($this->digitalSize)) {
            $result['productDigitalSize'] = $this->digitalSize;
        }

        if (in_array($this->isPublished, [true, false], true)) {
            $result['isPublished'] = $this->isPublished;
        }

        if (empty($result)) {
            throw new \Exception('Nothing to be updated', 400);
        }

        return $result;
    }
}
