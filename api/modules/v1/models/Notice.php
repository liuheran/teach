<?php  
namespace api\modules\v1\models;  
  
use Yii;  
use yii\web\IdentityInterface;  
use yii\filters\RateLimitInterface;  
/**  
 * Notice model  
 */  
class Notice extends User implements IdentityInterface ,RateLimitInterface  
{  
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%notice}}';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				[['userId', 'created', 'isLook'], 'integer'],
				[['content'], 'string'],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
				'id' => '通知编号',
				'userId' => '用户编号',
				'content' => '通知内容',
				'created' => '添加时间',
				'isLook' => '0未查看；1查看',
		];
	}
	
	/**
	 * 插入一条通知
	 */
	public function addNotice($data){
		$cn = \Yii::$app->db;
		$sql = "INSERT INTO ".$this->tableName()." (userId,content,created) values ('".$data['userId']."','".$data['content']."','".time()."')";
		$result = $cn->createCommand($sql)->execute();
		if ($result) {
			return 1;
		}else{
			return 0;
		}
	}
	
	/**
	 * 删除一条通知
	 */
	public function deleteOneNotice($data){
		$cn = \Yii::$app->db;
		$sql = "DELETE FROM ".$this->tableName()." WHERE id = '".$data['notice_id']."' AND userId = '".$data['user_id']."'";
		$result = $cn->createCommand($sql)->execute();
		if ($result) {
			return 1;
		}else{
			return 0;
		}
	}
	
	/**
	 * 删除全部通知
	 */
	public function deleteAllNotice($data){
		$cn = \Yii::$app->db;
		$sql = "DELETE FROM ".$this->tableName()."WHERE userId = '".$data['user_id']."'";
		$result = $cn->createCommand($sql)->execute();
		if ($result) {
			return 1;
		}else{
			return 0;
		}
	}
	
	/**
	 * 查询通知总条数
	 */
	public function countNoticeNum($data){
		$connection= \Yii::$app->dbSlave;
		$sql="SELECT count(1) FROM ".self::tableName()." WHERE userId = '".$data['user_id']."'";
		$total = $connection->createCommand ($sql)->queryScalar();
		return $total;
	}
	
	/**
	 * 查询通知
	 */
	public function checkNotice($start,$pageSize,$data){
		$connection= \Yii::$app->dbSlave;
		$sql="SELECT id,content,created,isLook FROM ".self::tableName()." WHERE userId = '".$data['user_id']."' ORDER BY id DESC LIMIT $start,$pageSize";
		return $connection->createCommand ($sql)->queryAll();
	}
	
	/**
	 * 查询通知ID
	 */
	public function checkNoticeId(){
		$connection= \Yii::$app->db;
		$sql="SELECT id FROM ".self::tableName()." ORDER BY created DESC";
		return $connection->createCommand ($sql)->queryOne();
	}
	
	/**
	 * @author jiangyuying
	 * @date 2015年12月3日上午11:28:10
	 * 获取积分后发送通知
	 */
	public static function insertNotice($userId,$number,$message){
		//替换积分
		$message=str_replace('XXX', $number, $message);
		$connection=Yii::$app->db;
		$time = date('Y-m-d H:i:s');
		$sql="INSERT INTO ".self::tableName()." (content,user_id,create_date) VALUES ('$message',$userId,'$time')";
		return $connection->createCommand($sql)->execute();
	}
	
	/*
	 * 订单完成通知用户获取相应积分
	 */
	public static function insertNoticeInOrder($userId,$number,$message,$orderId,$orderSn){
	
		if($number>0){
			//替换积分
			$message=str_replace('XXX', $number, $message);
			$message="订单编号".$orderSn."".$message;
		}
	
		$connection=Yii::$app->db;
		$time = date('Y-m-d H:i:s');
		$sql="INSERT INTO ".self::tableName()." (content,user_id,create_date,is_order,order_sn,order_id) VALUES ('$message',$userId,'$time',1,'$orderSn',$orderId)";
		return $connection->createCommand($sql)->execute();
	}
}