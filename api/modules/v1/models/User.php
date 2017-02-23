<?php  
namespace api\modules\v1\models;  
  
use Yii;  
use yii\base\NotSupportedException;  
use yii\behaviors\TimestampBehavior;  
use yii\db\ActiveRecord;  
use yii\web\IdentityInterface;  
use yii\filters\RateLimitInterface;  
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
class User extends ActiveRecord implements IdentityInterface ,RateLimitInterface  
{  
    const STATUS_DELETED = 0;  
    const STATUS_ACTIVE = 10;  
  
      
    # 速度控制  6秒内访问3次，注意，数组的第一个不要设置1，设置1会出问题，一定要  
    #大于2，譬如下面  6秒内只能访问三次  
    # 文档标注：返回允许的请求的最大数目及时间，例如，[100, 600] 表示在600秒内最多100次的API调用。  
    public  function getRateLimit($request, $action){  
         return [100, 300];  
    }  
    # 文档标注： 返回剩余的允许的请求和相应的UNIX时间戳数 当最后一次速率限制检查时。  
    public  function loadAllowance($request, $action){  
        //return [1,strtotime(date("Y-m-d H:i:s"))];  
        //echo $this->allowance;exit;  
         return [$this->allowance, $this->allowance_updated_at];  
    }  
    # allowance 对应user 表的allowance字段  int类型  
    # allowance_updated_at 对应user allowance_updated_at  int类型  
    # 文档标注：保存允许剩余的请求数和当前的UNIX时间戳。  
    public  function saveAllowance($request, $action, $allowance, $timestamp){  
        $this->allowance = $allowance;  
        $this->allowance_updated_at = $timestamp;  
        $this->save();  
    }  
      
    /**  
     * @inheritdoc  
     */  
    # 设置table  
    public static function tableName()  
    {  
        return 'user';  
    }  
  
    /**  
     * @inheritdoc  
     */  
    public function behaviors()  
    {  
        return [  
            TimestampBehavior::className(),  
        ];  
    }  
  
    /**  
     * @inheritdoc  
     */  
    # 设置 status  默认  ，以及取值的区间  
    public function rules()  
    {  
        return [  
            ['status', 'default', 'value' => self::STATUS_ACTIVE],  
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],  
        ];  
    }  
  
    /**  
     * @inheritdoc  
     */  
    # 通过id 找到identity  
    public static function findIdentity($id)  
    {  
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);  
    }  
  
    /**  
     * @inheritdoc  
     */  
    # 通过access_token 找到identity  
    public static function findIdentityByAccessToken($token, $type = null)  
    {  
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);  
    }  
    # 生成access_token  
    public function generateAccessToken()  
    {  
        $this->access_token = Yii::$app->security->generateRandomString();  
    }  
  
    /**  
     * Finds user by username  
     *  
     * @param string $username  
     * @return static|null  
     */  
    public static function findByUsername($username)  
    {  
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);  
    }  
    
    /**
     * Finds user by $phone
     *
     * @param string $username
     * @return static|null
     */
    public static function findByPhone($phone)
    {
    	return static::findOne(['phoneNumber' => $phone, 'status' => self::STATUS_ACTIVE]);
    }
  
    /**  
     * Finds user by password reset token  
     *  
     * @param string $token password reset token  
     * @return static|null  
     */  
    # 此处是忘记密码所使用的  
    public static function findByPasswordResetToken($token)  
    {  
        if (!static::isPasswordResetTokenValid($token)) {  
            return null;  
        }  
  
        return static::findOne([  
            'password_reset_token' => $token,  
            'status' => self::STATUS_ACTIVE,  
        ]);  
    }  
  
    /**  
     * Finds out if password reset token is valid  
     *  
     * @param string $token password reset token  
     * @return boolean  
     */  
    public static function isPasswordResetTokenValid($token)  
    {  
        if (empty($token)) {  
            return false;  
        }  
  
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);  
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];  
        return $timestamp + $expire >= time();  
    }  
  
    /**  
     * @inheritdoc  
     */  
    public function getId()  
    {  
        return $this->getPrimaryKey();  
    }  
  
    /**  
     * @inheritdoc  
     */  
    public function getAuthKey()  
    {  
        return $this->auth_key;  
    }
  
    /**  
     * @inheritdoc  
     */  
    public function validateAuthKey($authKey)  
    {  
        return $this->getAuthKey() === $authKey;  
    }
  
    /**  
     * Validates password  
     *  
     * @param string $password password to validate  
     * @return boolean if password provided is valid for current user  
     */  
    public function validatePassword($password)  
    {  
        return Yii::$app->security->validatePassword($password, $this->password_hash);  
    }
  
    /**  
     * Generates password hash from password and sets it to the model  
     *  
     * @param string $password  
     */  
    public function setPassword($password)  
    {  
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);  
    }
  
    /**  
     * Generates "remember me" authentication key  
     */  
    public function generateAuthKey()  
    {  
        $this->auth_key = Yii::$app->security->generateRandomString();  
    }  
  
    /**  
     * Generates new password reset token  
     */  
    public function generatePasswordResetToken()  
    {  
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();  
    }  
  
    /**  
     * Removes password reset token  
     */  
    public function removePasswordResetToken()  
    {  
        $this->password_reset_token = null;  
    }
    
    public function logOut()
    {
        $user = User::findOne([
            'id' => Yii::$app->user->id,
        ]);
        $user->access_token = null;
        return $user->save() ? $user : null;
    }
    
    public static function getUserInfoByUserId($id){
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * 获得一起按的用户
     * $userId,$distance
     */
    public function getUserInfo($data){
    
        $cn = \Yii::$app->db;
        $sql = "SELECT * FROM (SELECT ROUND(6378.138*2*ASIN(SQRT(POW(SIN((latitude*PI()/180-".$data['latitude']."*PI()/180)/2),2)+COS(latitude*PI()/180)*COS(".$data['latitude']."*PI()/180)*POW(SIN((longitude *PI()/180-".$data['longitude']."*PI()/180)/2),2)))*1000) AS distance, id,username,logoUrl FROM ".$this->tableName()." WHERE id <> ".$data['userId']." HAVING distance<=".$data['distance'].") AS u LEFT JOIN (SELECT protectId FROM ".Protect::tableName()." WHERE userId=".$data['userId'].") AS p ON u.id=p.protectId  ORDER BY distance LIMIT 1000";
        $result = $cn->createCommand($sql)->queryAll();
        return $result;
    }
    
    /**
     * @author jiangyuying
     * @date 2017年1月13日下午2:18:50
     * 修改用户信息
     */
    public function updateUserInfo($uid,$fields) {
    	$cn = \Yii::$app->db;
    	$sql = "UPDATE ".self::tableName()." SET ".$fields.",updated_at = '".time()."' WHERE id=$uid";
    	$result = $cn->createCommand($sql)->execute();
    	return $result;
    }
}