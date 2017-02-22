<?php

namespace api\modules\v1\models;  
  
use Yii;  
use yii\web\IdentityInterface;  
use yii\filters\RateLimitInterface;  
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "Protect".
 *
 * @property string $id
 * @property string $userId
 * @property string $protectId
 * @property integer $created
 */
class Protect extends User implements IdentityInterface ,RateLimitInterface  
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'protect';
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
    
    /**
     * 插入
     */
    public function add($data){
        $cn = \Yii::$app->db;
        $sql = "REPLACE INTO ".$this->tableName()." (userId,protectId,created) values ('".$data['userId']."','".$data['protectId']."','".time()."')";
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
        $sql = "DELETE FROM ".$this->tableName()." WHERE protectId = '".$data['protectId']."' AND userId = '".$data['userId']."'";
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
        if($data['type'] == 0) {
            $sql="SELECT count(1) FROM ".self::tableName()." WHERE userId = '".$data['userId']."'";
        } else {
            $sql="SELECT count(1) FROM ".self::tableName()." WHERE protectId = '".$data['userId']."'";
        }
        $total = $connection->createCommand ($sql)->queryScalar();
        return $total;
    }
    
    /**
     * 查询列表
     */
    public function lists($start,$pageSize,$data){
        $connection= \Yii::$app->db;
        if($data['type'] == 0) {
            $sql="SELECT p.*,u.userName,CONCAT('0') as count,age,sex,logoUrl,grade FROM ".self::tableName()." AS p
            LEFT JOIN user AS u ON p.protectId=u.id
            WHERE p.userId = '".$data['userId']."' ORDER BY p.id DESC LIMIT $start,$pageSize";
        } else {
            $sql="SELECT p.*,u.userName,CONCAT('0') as count,age,sex,logoUrl,grade FROM ".self::tableName()." AS p
            LEFT JOIN user AS u ON p.userId=u.id
            WHERE p.protectId = '".$data['userId']."' ORDER BY p.id DESC LIMIT $start,$pageSize";
        }
        
        $result = $connection->createCommand ($sql)->queryAll();

        if(! empty($result)){
        	
        	if($data['type'] == 0) {
        		$userIds = array_column($result, 'protectId');
        	} else {
        		$userIds = array_column($result, 'userId');
        	}
        	
        	$sql = 'SELECT protectId, count(*) AS count FROM protect WHERE protectId IN('.implode(',', $userIds).') GROUP BY protectId';
//         	echo $sql;
        	$list = $connection->createCommand ($sql)->queryAll();
        	if(! empty($list)) {
        		$list =  ArrayHelper::index($list, 'protectId');
        		if($data['type'] == 0) {
        			$result = ArrayHelper::index($result, 'protectId');
        		} else {
        			$result = ArrayHelper::index($result, 'userId');
        		}
        		foreach ($result as $key=>$value) {
        			if(array_key_exists($key, $list)){
	        			$result[$key]['count'] = $list[$key]['count'];
        			}
        		}
        	}
        }
         return $result;
    }
}
