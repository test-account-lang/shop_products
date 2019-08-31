<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 02:13 م
 */

namespace app\common\requestHandler\product;


use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\enums\ProductTypesEnum;
use app\common\exceptions\OperationFailed;
use app\common\requestHandler\RequestHandlerInterface;
use app\common\utils\DigitalUnitsConverterUtil;

class CreateDownloadableProductRequestHandler extends AbstractCreateRequestHandler implements RequestHandlerInterface
{

    private $digitalSize;

    /**
     * @param mixed $digitalSize
     */
    public function setDigitalSize($digitalSize): void
    {
        $this->digitalSize = $digitalSize;
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return array_merge(parent::fields(), [
            'digitalSize' => $this->digitalSize
        ]);
    }

    private function getMaxDigitalSizeValidationConfig()
    {
        return $this->getDI()->getConfig()->application->validation->downloadable->maxDigitalSize;
    }

    /** Validate request fields using \Phalcon\Validation
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'digitalSize',
            new Validation\Validator\PresenceOf([
                'message' => 'You have to provide digital size'
            ])
        );

        $validator->add(
            'digitalSize',
            new Validation\Validator\NumericValidator([
                'min' => 1,
                'max' => $this->getMaxDigitalSizeValidationConfig(),
                'messageMaximum' => 'Digital size exceeds the max limit ' .
                    DigitalUnitsConverterUtil::bytesToMb($this->getMaxDigitalSizeValidationConfig()).
                    ' Mb',
                'messageMinimum' => 'Invalid digital size'
            ])
        );

        return $validator->validate($this->fields());
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        // TODO: TO BE ENHANCED LATER
        $messages = $this->validate();
        $multiErrorFields = [];
        foreach ($messages as $message) {
            $multiErrorFields[] = $message->getField();
        }
        $multiErrorFields = array_diff_assoc($multiErrorFields, array_unique($multiErrorFields));

        foreach ($messages as $message) {
            if (in_array($message->getField(), $multiErrorFields)) {
                $this->errorMessages[$message->getField()][] = $message->getMessage();
            } else {
                $this->errorMessages[$message->getField()] = $message->getMessage();
            }
        }
        return parent::isValid();
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
        return array_merge(parent::toArray(), [
            'productId' => $this->getUuidUtil()->uuid(),
            'productType' => ProductTypesEnum::TYPE_DOWNLOADABLE,
            'productDigitalSize' => $this->digitalSize
        ]);
    }
}
