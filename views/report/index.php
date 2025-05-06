<?php
/** @var $this yii\web\View */
/** @var array[] $report */
/** @var array[] $errors */

use yii\helpers\Html;

// 1) Load DataTables + RowGroup CSS & JS
$this->registerCssFile('https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css');
$this->registerCssFile('https://cdn.datatables.net/rowgroup/1.3.1/css/rowGroup.dataTables.min.css');
$this->registerJsFile('https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', [
    'depends' => [\yii\web\JqueryAsset::class],
]);
$this->registerJsFile('https://cdn.datatables.net/rowgroup/1.3.1/js/dataTables.rowGroup.min.js', [
    'depends' => [\yii\web\JqueryAsset::class],
]);

$this->title = 'Consolidated Holdings Report';
?>
<div class="report-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- The DataTable: flat rows with hidden grouping columns -->
    <table id="holdings-table" class="display" style="width:100%">
        <thead>
        <tr>
            <th>Asset Class</th>   <!-- hidden by DataTables -->
            <th>Asset Type</th>    <!-- hidden by DataTables -->
            <th>Ticker</th>
            <th>Entries</th>
            <th>Quantity</th>
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($report as $row): ?>
            <tr>
                <td><?= Html::encode($row['asset_class']) ?></td>
                <td><?= Html::encode($row['asset_type'])  ?></td>
                <td><?= Html::encode($row['ticker'])      ?></td>
                <td><?= $row['entries']    ?></td>
                <td><?= $row['quantity']   ?></td>
                <td><?= number_format($row['net_amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Errors section (rows missing currency) -->
    <?php if (!empty($errors)): ?>
        <h4>Errors (Missing Currency)</h4>
        <table class="table table-sm table-danger">
            <thead>
            <tr>
                <th>ID</th><th>Asset Class</th><th>Asset Type</th>
                <th>Ticker</th><th>Qty</th><th>Net</th><th>Date</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($errors as $e): ?>
                <tr>
                    <td><?= Html::encode($e['id']) ?></td>
                    <td><?= Html::encode($e['asset_class']) ?></td>
                    <td><?= Html::encode($e['asset_type'])  ?></td>
                    <td><?= Html::encode($e['ticker'])      ?></td>
                    <td><?= Html::encode($e['quantity'])    ?></td>
                    <td><?= number_format($e['net_amount'], 2) ?></td>
                    <td><?= Html::encode($e['transaction_date']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// 2) Initialize DataTables with multi-level grouping
$js = <<<'JS'
$(document).ready(function() {
    $('#holdings-table').DataTable({
        columnDefs: [
            { targets: [0,1], visible: false } // hide Asset Class & Asset Type columns
        ],
        order: [[0, 'asc'], [1, 'asc']],       // group by class then type
        pageLength: 25,
        rowGroup: {
            dataSrc: ['Asset Class','Asset Type'],
            startRender: function (rows, group, level) {
                // sum a given column index across rows
                const sum = idx => rows
                    .data()
                    .pluck(idx)
                    .reduce((a,b) => a + parseFloat(b||0), 0);

                if (level === 0) {
                    // Asset Class header row
                    return $('<tr/>')
                        .append(`<td colspan="2"><strong>Asset Class: ${group}</strong></td>`)
                        .append('<td></td>')
                        .append(`<td><strong>${sum(3)}</strong></td>`)
                        .append(`<td><strong>${sum(4)}</strong></td>`)
                        .append(`<td><strong>${sum(5).toFixed(2)}</strong></td>`);
                }
                if (level === 1) {
                    // Asset Type header row (indented)
                    return $('<tr/>')
                        .append('<td></td>')
                        .append(`<td colspan="1" style="padding-left:20px;"><strong>Asset Type: ${group}</strong></td>`)
                        .append('<td></td>')
                        .append(`<td><strong>${sum(3)}</strong></td>`)
                        .append(`<td><strong>${sum(4)}</strong></td>`)
                        .append(`<td><strong>${sum(5).toFixed(2)}</strong></td>`);
                }
            }
        }
    });
});
JS;
$this->registerJs($js);
?>
