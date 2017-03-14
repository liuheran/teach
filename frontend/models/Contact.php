<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Contact".
 * test one
 * @property string $id
 * @property string $userId
 * @property string $contactId
 * @property string $created
 */
class Contact extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Contact';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'contactId', 'created'], 'integer'],
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
            'contactId' => 'Contact ID',
            'created' => 'Created',
        ];
    }
}
