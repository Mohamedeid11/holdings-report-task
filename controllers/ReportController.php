<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;

class ReportController extends Controller
{
    public function actionIndex()
    {
        $filters = Yii::$app->request->get();
        $report  = Yii::$app->holdingsReport->generate($filters);

        return $this->render('index', [
            'report'  => $report['data'],
            'errors'  => $report['errors'],
            'filters' => $filters,
        ]);
    }
}