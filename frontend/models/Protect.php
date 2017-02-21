<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Protect".
 *
 * @property string $id
 * @property string $userId
 * @property string $protectId
 * @property integer $created
 */
class Protect extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Protect';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'protectId', 'created'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userId' => 'User ID',
            'protectId' => 'Protect ID',
            'created' => 'Created',
        ];
    }
}
