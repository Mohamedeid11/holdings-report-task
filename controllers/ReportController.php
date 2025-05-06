<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class ReportController extends Controller
{
    /**
     * Renders the holdings report.
     */
    public function actionIndex()
    {
        $filters = Yii::$app->request->get();
        $result  = Yii::$app->holdingsReport->generate($filters);

        // Flatten the nested grouping into a simple list for DataTables
        $report = [];
        foreach ($result['data'] as $assetClass => $clsData) {
            foreach ($clsData['types'] as $assetType => $typeData) {
                foreach ($typeData['tickers'] as $ticker => $tkrData) {
                    $report[] = [
                        'asset_class' => $assetClass,
                        'asset_type'  => $assetType,
                        'ticker'      => $ticker,
                        'entries'     => $tkrData['entries'],
                        'quantity'    => $tkrData['quantity'],
                        'net_amount'  => $tkrData['net_amount'],
                    ];
                }
            }
        }

        return $this->render('index', [
            'report'  => $report,
            'errors'  => $result['errors'],
            'filters' => $filters,
        ]);
    }
}
