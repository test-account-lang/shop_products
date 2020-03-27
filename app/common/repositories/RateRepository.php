<?php
/**
 * User: Wajdi Jurry
 * Date: 27/07/19
 * Time: 05:50 م
 */

namespace app\common\repositories;


use app\common\models\ProductRates;
use app\common\models\RateImages;

class RateRepository extends BaseRepository
{
    /**
     * @return ProductRates
     */
    public function getModel(): ProductRates
    {
        return new ProductRates();
    }

    /**
     * @param string $productId
     * @return bool
     */
    public function deleteProductRates(string $productId)
    {
        $allDeleted = false;
        $allProductRates = $this->getModel()::find([
            'conditions' => 'productId = :productId:',
            'bind' => [
                'productId' => $productId
            ]
        ]);

        if (!count($allProductRates)) {
            return false;
        }

        if ($allProductRates) {
            foreach ($allProductRates as $productRate) {
                $allDeleted = $productRate->delete();
            }
        }

        $ratesIds = array_column($allProductRates->toArray(), 'rateId');
        $rateImagesModel = new RateImages();
        $allRatesImages = $rateImagesModel::find([
            'conditions' => 'rateId IN ({ratesIds:array})',
            'bind' => [
                'ratesIds' => $ratesIds
            ]
        ]);

        foreach ($allRatesImages as $rateImages) {
            $allDeleted = $rateImages->delete();
        }

        return $allDeleted;
    }
}
