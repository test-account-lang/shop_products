<?php
/**
 * User: Wajdi Jurry
 * Date: 31/08/2018
 * Time: 10:21 PM
 */

namespace app\common\models\embedded\physical;


class Weight
{
    /** @var float */
    public $amount;

    /** @var string */
    public $unit;

    /**
     * @param array $data
     */
    public function setAttributes(array $data): void
    {
        $this->amount = $data->amount ?? null;
        $this->unit = $data->unit ?? null;
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'amount' => $this->amount,
            'unit' => $this->unit
        ];
    }
}
