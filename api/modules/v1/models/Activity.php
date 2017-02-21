<?php

namespace api\modules\v1\models;  
  
use Yii;  
use yii\web\IdentityInterface;  
use yii\filters\RateLimitInterface;  

/**
 * This is the model class for table "Protect".
 *
 * @property string $id
 * @property string $userId
 * @property string $protectId
 * @property integer $created
 */
class Activity extends User implements IdentityInterface ,RateLimitInterface  
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'created'], 'integer'],
            [['content'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userId' => '用户ID',
            'content' => '内容',
            'created' => '添加时间',
        ];
    }
    
    /**
     * 插入
     */
    public function add($data){
        $cn = \Yii::$app->db;
        $sql = "INSERT INTO ".$this->tableName()." (userId,content,location,images,created) values ('".$data['userId']."','".$data['content']."','".$data['location']."','".$data['images']."','".time()."')";
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
        $sql = "DELETE FROM ".$this->tableName()." WHERE id = '".$data['id']."' AND userId = '".$data['userId']."'";
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
        $connection= \Yii::$app->db;
        if (!empty($data['userId'])) {
            $sql="SELECT count(1) FROM ".self::tableName()." WHERE userId = '".$data['userId']."'";
        }else {
            $sql="SELECT count(1) FROM ".self::tableName();
        }
        $total = $connection->createCommand ($sql)->queryScalar();
        return $total;
    }
    
    /**
     * 查询列表
     */
    public function lists($start,$pageSize,$data){
        $connection= \Yii::$app->db;
        if (!empty($data['userId'])) {
            $sql="SELECT a.*,u.userName,u.logoUrl FROM ".self::tableName()." AS a LEFT JOIN user AS u ON a.userId=u.id WHERE a.userId=".$data['userId']." ORDER BY a.id DESC LIMIT $start,$pageSize";
        } else {
            $sql="SELECT a.*,u.userName,u.logoUrl FROM ".self::tableName()." AS a LEFT JOIN user AS u ON a.userId=u.id ORDER BY a.id DESC LIMIT $start,$pageSize";
        }
        return $connection->createCommand ($sql)->queryAll();
    }
}
