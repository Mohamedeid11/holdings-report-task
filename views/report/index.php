<?php
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/** @var $this yii\web\View */
/** @var $report array */
/** @var $errors array */
/** @var $filters array */

$this->title = 'Consolidated Holdings Report';
?>
    <div class="report-index">
        <h1><?= Html::encode($this->title) ?></h1>

        <?php Pjax::begin(['id'=>'report-pjax']); ?>

        <!-- FILTER FORM -->
        <?php $form = ActiveForm::begin([
            'method'  => 'get',
            'options' => ['data-pjax' => true],
        ]); ?>

        <div class="row">
            <div class="col-sm-2">
                <?= Html::dropDownList('asset_class', $filters['asset_class'] ?? null,
                    ArrayHelper::map(\app\models\Holding::find()->select('asset_class')->distinct()->all(), 'asset_class','asset_class'),
                    ['prompt'=>'All Classes','class'=>'form-control']
                ) ?>
            </div>
            <div class="col-sm-2">
                <?= Html::dropDownList('asset_type', $filters['asset_type'] ?? null,
                    ArrayHelper::map(\app\models\Holding::find()->select('asset_type')->distinct()->all(), 'asset_type','asset_type'),
                    ['prompt'=>'All Types','class'=>'form-control']
                ) ?>
            </div>
            <div class="col-sm-2">
                <?= Html::dropDownList('ticker', $filters['ticker'] ?? null,
                    ArrayHelper::map(\app\models\Holding::find()->select('ticker')->distinct()->all(), 'ticker','ticker'),
                    ['prompt'=>'All Tickers','class'=>'form-control']
                ) ?>
            </div>
            <div class="col-sm-2">
                <?= Html::dropDownList('currency', $filters['currency'] ?? null,
                    ArrayHelper::map(\app\models\Holding::find()->select('currency')->distinct()->all(), 'currency','currency'),
                    ['prompt'=>'All Currencies','class'=>'form-control']
                ) ?>
            </div>
            <div class="col-sm-2">
                <?= Html::input('date','date_from',$filters['date_from'] ?? null,['class'=>'form-control']) ?>
            </div>
            <div class="col-sm-2">
                <?= Html::input('date','date_to',$filters['date_to'] ?? null,['class'=>'form-control']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

        <!-- GROUPED TABLE -->
        <table class="table table-bordered table-striped mt-3">
            <?php foreach ($report as $cls => $types): ?>
                <tr class="table-primary">
                    <td colspan="3"><strong><?= Html::encode($cls) ?></strong></td>
                </tr>
                <?php foreach ($types as $type => $tickers): ?>
                    <tr class="table-secondary">
                        <td></td>
                        <td colspan="2"><strong><?= Html::encode($type) ?></strong></td>
                    </tr>
                    <?php foreach ($tickers as $tkr => $vals): ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <?= Html::encode($tkr) ?>:
                                Qty <?= $vals['quantity'] ?> |
                                Net <?= number_format($vals['net_amount'],2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </table>

        <!-- ERRORS -->
        <?php if (!empty($errors)): ?>
            <h4>Errors (Missing Currency)</h4>
            <table class="table table-sm table-danger">
                <tr>
                    <th>ID</th><th>Asset Class</th><th>Asset Type</th>
                    <th>Ticker</th><th>Qty</th><th>Net</th><th>Date</th>
                </tr>
                <?php foreach ($errors as $e): ?>
                    <tr>
                        <td><?= $e['id'] ?></td>
                        <td><?= Html::encode($e['asset_class'])?></td>
                        <td><?= Html::encode($e['asset_type'])?></td>
                        <td><?= Html::encode($e['ticker'])?></td>
                        <td><?= $e['quantity'] ?></td>
                        <td><?= number_format($e['net_amount'],2) ?></td>
                        <td><?= $e['transaction_date'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php Pjax::end(); ?>
    </div>

<?php
// trigger Pjax reload on filter change
$js = <<<JS
$('form[data-pjax] select, form[data-pjax] input').on('change', function(){
    $.pjax.reload({container:'#report-pjax',data:$('form[data-pjax]').serialize()});
});
JS;
$this->registerJs($js);
