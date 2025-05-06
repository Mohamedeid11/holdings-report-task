<?php
namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use Yii;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ClientDataController extends Controller
{

    public function actionImport($filePath = null)
    {
        if ($filePath === null) {
            $filePath = Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'Client Data.xlsx';
        }
        if (!file_exists($filePath)) {
            $this->stderr("File not found: $filePath\n");
            return ExitCode::DATAERR;
        }

        $reader = new Xlsx();
        try {
            $spreadsheet = $reader->load($filePath);
        } catch (\Exception $e) {
            $this->stderr("Failed to read Excel file: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);
        if (empty($rows)) {
            $this->stderr("The Excel sheet is empty or could not be read.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $header = array_shift($rows);  // first row

        $colIndex = [];
        foreach ($header as $colLetter => $colName) {
            if (is_string($colName) && trim($colName) !== '') {
                $colIndex[trim($colName)] = $colLetter;
            }
        }

        $requiredCols = [
            'Asset Class',
            'Asset Type',
            'Ticker',
            'Quantity',
            'Currency',
            'Amount',
            'Date',
        ];
        foreach ($requiredCols as $colName) {
            if (!isset($colIndex[$colName])) {
                $this->stderr("Required column '{$colName}' is missing in the Excel file.\n");
                return ExitCode::DATAERR;
            }
        }

        $validData = [];
        $errorData = [];
        foreach ($rows as $row) {
            $assetClass      = trim($row[$colIndex['Asset Class']]    ?? '');
            $assetType       = trim($row[$colIndex['Asset Type']]     ?? '');
            $ticker          = trim($row[$colIndex['Ticker']]         ?? '');
            $quantity        = trim($row[$colIndex['Quantity']]       ?? '');
            $currency        = trim($row[$colIndex['Currency']]       ?? '');
            $netAmount       = trim($row[$colIndex['Amount']]         ?? '');
            $transactionDate = trim($row[$colIndex['Date']]           ?? '');

            if ($currency === '') {
                $errorData[] = [
                    $assetClass, $assetType, $ticker,
                    $quantity,   $currency,   $netAmount,
                    $transactionDate,
                ];
            } else {
                $validData[] = [
                    $assetClass, $assetType, $ticker,
                    $quantity,   $currency,   $netAmount,
                    $transactionDate,
                ];
            }
        }

        $db = Yii::$app->db;
        $insertedHolding = 0;
        $insertedError   = 0;
        $cols = [
            'asset_class',
            'asset_type',
            'ticker',
            'quantity',
            'currency',
            'net_amount',
            'transaction_date',
        ];

        if (!empty($validData)) {
            try {
                $db->createCommand()
                    ->batchInsert('holding', $cols, $validData)
                    ->execute();
                $insertedHolding = count($validData);
            } catch (\Exception $e) {
                $this->stderr("Error inserting into holding: " . $e->getMessage() . "\n");
            }
        }

        if (!empty($errorData)) {
            try {
                $db->createCommand()
                    ->batchInsert('error_holding', $cols, $errorData)
                    ->execute();
                $insertedError = count($errorData);
            } catch (\Exception $e) {
                $this->stderr("Error inserting into error_holding: " . $e->getMessage() . "\n");
            }
        }

        $this->stdout("Import complete: {$insertedHolding} records added to holding, {$insertedError} to error_holding.\n");
        return ExitCode::OK;
    }
}
