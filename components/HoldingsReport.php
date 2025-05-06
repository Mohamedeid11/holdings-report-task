<?php
namespace app\components;

use app\models\Holding;
use app\models\ErrorHolding;
use yii\base\Component;

class HoldingsReport extends Component
{
    /**
     * @param array $filters GET params: asset_class, asset_type, ticker, currency, date_from, date_to
     * @return array ['data'=> grouped, 'errors'=> raw error rows]
     */
    public function generate(array $filters): array
    {
        $query = Holding::find();
        // apply filters
        $query->andFilterWhere(['asset_class' => $filters['asset_class'] ?? null]);
        $query->andFilterWhere(['asset_type'  => $filters['asset_type']  ?? null]);
        $query->andFilterWhere(['ticker'      => $filters['ticker']      ?? null]);
        $query->andFilterWhere(['currency'    => $filters['currency']    ?? null]);
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->andWhere([
                'between',
                'transaction_date',
                $filters['date_from'],
                $filters['date_to'],
            ]);
        }

        $rows = $query->asArray()->all();

        // group and sum
        $grouped = [];
        foreach ($rows as $r) {
            $cls  = $r['asset_class'];
            $type = $r['asset_type'];
            $tkr  = $r['ticker'];
            if (!isset($grouped[$cls])) {
                $grouped[$cls] = [];
            }
            if (!isset($grouped[$cls][$type])) {
                $grouped[$cls][$type] = [];
            }
            if (!isset($grouped[$cls][$type][$tkr])) {
                $grouped[$cls][$type][$tkr] = ['quantity'=>0,'net_amount'=>0.0];
            }
            $grouped[$cls][$type][$tkr]['quantity']   += (int)   $r['quantity'];
            $grouped[$cls][$type][$tkr]['net_amount'] += (float) $r['net_amount'];
        }

        // collect errors (missing currency)
        $errQuery = ErrorHolding::find()
            ->andWhere(['or', ['currency' => null], ['currency' => '']]);
        $errors = $errQuery->asArray()->all();

        return [
            'data'   => $grouped,
            'errors' => $errors,
        ];
    }
}
