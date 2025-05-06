<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "error_holding".
 *
 * @property int $id
 * @property string $asset_class
 * @property string $asset_type
 * @property string $ticker
 * @property int $quantity
 * @property string|null $currency
 * @property float $net_amount
 * @property string $transaction_date
 */
class ErrorHolding extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%error_holding}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['currency'], 'default', 'value' => null],
            [['asset_class', 'asset_type', 'ticker', 'quantity', 'net_amount', 'transaction_date'], 'required'],
            [['quantity'], 'integer'],
            [['net_amount'], 'number'],
            [['transaction_date'], 'safe'],
            [['asset_class', 'asset_type', 'ticker'], 'string', 'max' => 100],
            [['currency'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'asset_class' => 'Asset Class',
            'asset_type' => 'Asset Type',
            'ticker' => 'Ticker',
            'quantity' => 'Quantity',
            'currency' => 'Currency',
            'net_amount' => 'Net Amount',
            'transaction_date' => 'Transaction Date',
        ];
    }

}
