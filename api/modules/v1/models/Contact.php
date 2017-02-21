<?php

namespace api\modules\v1\models;  
  
use Yii;  
use yii\web\IdentityInterface;  
use yii\filters\RateLimitInterface;  
/**
 * This is the model class for table "Contact".
 *
 * @property string $id
 * @property string $userId
 * @property string $contactId
 * @property string $created
 */
class Contact extends User implements IdentityInterface ,RateLimitInterface
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
    
    /**
     * 插入
     */
    public function add($data){
        $cn = \Yii::$app->db;
        $sql = "REPLACE INTO ".$this->tableName()." (userId,contactId,created) values ('".$data['userId']."','".$data['contactId']."','".time()."')";
        $result = $cn->createCommand($sql)->execute();
        if ($result) {
            return $cn->getLastInsertID();
        }else{
            return false;
        }
    }
    
    /**
     * 删除
     */
    public function deleteOne($data){
        $cn = \Yii::$app->db;
        $sql = "DELETE FROM ".$this->tableName()." WHERE id = '".$data['contactId']."' AND userId = '".$data['userId']."'";
        $result = $cn->createCommand($sql)->execute();
        if ($result) {
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 查询总条数
     */
    public function countNum($data){
        $connection= \Yii::$app->dbSlave;
        $sql="SELECT count(1) FROM ".self::tableName()." WHERE userId = '".$data['userId']."'";
        $total = $connection->createCommand ($sql)->queryScalar();
        return $total;
    }
    
    /**
     * 查询列表
     */
    public function lists($start,$pageSize,$data){
        $connection= \Yii::$app->dbSlave;
        $sql="SELECT c.*,u.userName FROM ".self::tableName()." AS c
            LEFT JOIN user AS u ON c.userId=u.id 
            WHERE c.userId = '".$data['userId']."' ORDER BY c.id DESC LIMIT $start,$pageSize";
        return $connection->createCommand ($sql)->queryAll();
    }
}
