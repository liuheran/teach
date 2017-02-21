<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "{{%version}}".
 *
 * @property string $type
 * @property string $version
 * @property string $download_url
 * @property string $message
 * @property integer $upgrade_type
 */
class Version extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%version}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'version'], 'required'],
            [['upgrade_type'], 'integer'],
            [['type'], 'string', 'max' => 15],
            [['version'], 'string', 'max' => 10],
            [['download_url'], 'string', 'max' => 200],
            [['message'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => '设备ios android',
            'version' => '版本号',
            'download_url' => '下载地址',
            'message' => '升级提示语',
            'upgrade_type' => '是否强制升级 1是 0否',
        ];
    }
    
    public static function checkVersion($type,$version){
        $cn = \Yii::$app->dbSlave;
        $sql = "SELECT download_url,message FROM ".self::tableName()." WHERE type = '$type' AND version = '$version' ";
        $result =  $cn->createCommand($sql)->queryAll();
        if ($result) {
            //不升级
            return 0;
        }else{
            	
            $sql = "SELECT download_url,version,message,upgrade_type FROM ".self::tableName()." WHERE type = '$type' ";
            $result=$cn->createCommand($sql)->queryOne();
            if($result['version']>$version){ //当前版本高于客户端版本
                return $result;
            }else{
                //不升级
                return 0;
            }
        }
    
    }
}
