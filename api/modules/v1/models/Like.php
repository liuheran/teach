<?php  
namespace api\modules\v1\models;  
  
use Yii;  
use yii\web\IdentityInterface;  
use yii\filters\RateLimitInterface;  
/**  
 * Like model  
 */  
class Like extends User implements IdentityInterface ,RateLimitInterface  
{
	/**
	 * @return string the associated database table name
	 */
	public static function tableName()
	{
		return 'likes';
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => '喜欢Id',
			'userId' => '用户Id',
			'activityId' => '动态Id',
			'created' => '喜欢时间',
		);
	}

 /**
     * 插入
     */
    public function add($data){
        $cn = \Yii::$app->db;
        $sql = "REPLACE INTO ".$this->tableName()." (userId,activityId,created) values ('".$data['userId']."','".$data['activityId']."','".time()."')";
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
        $sql = "DELETE FROM ".$this->tableName()." WHERE id = '".$data['likeId']."' AND userId = '".$data['userId']."'";
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
    public function lists($data){
        $connection= \Yii::$app->dbSlave;
        $sql="SELECT l.id AS likeId,u.id AS uId,u.username FROM ".self::tableName()." AS l LEFT JOIN user AS u ON l.userId=u.id WHERE l.activityId IN (".$data['activityId'].")";
        return $connection->createCommand ($sql)->queryAll();
    }
}