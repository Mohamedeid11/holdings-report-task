<?php
namespace app\components;

use yii\base\Component;
use app\models\Holding;
use app\models\ErrorHolding;

class HoldingsReport extends Component
{
    /**
     * Builds a 3-level grouped report:
     *  - Asset Class
     *  - Asset Type
     *  - Ticker
     *
     * Each level tracks 'entries', 'quantity' and 'net_amount'.
     *
     * @param array $filters ['asset_class','asset_type','ticker','currency','date_from','date_to']
     * @return array ['data'=> groupedArray, 'errors'=> rowsMissingCurrency]
     */
    public function generate(array $filters): array
    {
        $query = Holding::find()->asArray();
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

        $rows = $query->all();
        $grouped = [];

        foreach ($rows as $r) {
            $cls  = $r['asset_class'];
            $type = $r['asset_type'];
            $tkr  = $r['ticker'];
            $qty  = (int)$r['quantity'];
            $amt  = (float)$r['net_amount'];

            // Asset Class level
            if (!isset($grouped[$cls])) {
                $grouped[$cls] = [
                    'entries'    => 0,
                    'quantity'   => 0,
                    'net_amount' => 0.0,
                    'types'      => [],
                ];
            }
            $grouped[$cls]['entries']    += 1;
            $grouped[$cls]['quantity']   += $qty;
            $grouped[$cls]['net_amount'] += $amt;

            // Asset Type level
            if (!isset($grouped[$cls]['types'][$type])) {
                $grouped[$cls]['types'][$type] = [
                    'entries'    => 0,
                    'quantity'   => 0,
                    'net_amount' => 0.0,
                    'tickers'    => [],
                ];
            }
            $grouped[$cls]['types'][$type]['entries']    += 1;
            $grouped[$cls]['types'][$type]['quantity']   += $qty;
            $grouped[$cls]['types'][$type]['net_amount'] += $amt;

            // Ticker level
            if (!isset($grouped[$cls]['types'][$type]['tickers'][$tkr])) {
                $grouped[$cls]['types'][$type]['tickers'][$tkr] = [
                    'entries'    => 0,
                    'quantity'   => 0,
                    'net_amount' => 0.0,
                ];
            }
            $grouped[$cls]['types'][$type]['tickers'][$tkr]['entries']    += 1;
            $grouped[$cls]['types'][$type]['tickers'][$tkr]['quantity']   += $qty;
            $grouped[$cls]['types'][$type]['tickers'][$tkr]['net_amount'] += $amt;
        }

        // Rows with missing currency go into 'errors'
        $errors = ErrorHolding::find()
            ->andWhere(['or', ['currency' => null], ['currency' => '']])
            ->asArray()
            ->all();

        return [
            'data'   => $grouped,
            'errors' => $errors,
        ];
    }
}
