<?php  
namespace api\modules\v1\models;  
  
use Yii;  
use yii\web\IdentityInterface;  
use yii\filters\RateLimitInterface;  
use api\modules\v1\models\User;
/**  
 * User model  
 *  
 * @property integer $id  
 * @property string $username  
 * @property string $password_hash  
 * @property string $password_reset_token  
 * @property string $email  
 * @property string $auth_key  
 * @property integer $status  
 * @property integer $created_at  
 * @property integer $updated_at  
 * @property string $password write-only password  
 */  
class Track extends User implements IdentityInterface ,RateLimitInterface  
{  
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%track}}';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				[['spent_time'], 'number'],
				[['arguments', 'response_msg'], 'string'],
				[['create_date'], 'safe'],
				[['track_name'], 'string', 'max' => 256]
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
				'track_id' => '跟踪编号',
				'track_name' => '跟踪接口名称',
				'spent_time' => '用时',
				'arguments' => '参数',
				'response_msg' => '返回值',
				'create_date' => '生成时间',
		];
	}
	
	/**
	 * 微妙时间计算
	 */
	public static function microtimeFloat()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/**
	 * 添加baby_track记录
	 */
	public static function addTrack($spendTime,$trackName,$arguments,$responseMsg=''){
		$db = \Yii::$app->db;
		$db->createCommand('INSERT INTO '.self::tableName().'(track_name,spent_time,arguments,response_msg,create_date) VALUES (:name,:time,:arg,:msg,:date)', [
				':name' => $trackName,
				':time' => $spendTime,
				':arg' => $arguments,
				':msg' => $responseMsg,
				':date' => date("Y-m-d H:i:s"),
		])->execute();
	}
	
	/**
	 * 删除七天前任务数据
	 */
	public function deleteTrack(){
		$cn = \Yii::$app()->db;
		$sql = "Delete FROM ".$this->tableName()." WHERE DATE(CreateDate) < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
		return $cn->createCommand($sql)->execute();
	}
}