<?php

namespace api\modules\v1\models;  

use Yii;
use yii\web\IdentityInterface;
use yii\filters\RateLimitInterface;

/**
 * This is the model class for table "{{%commentDiscussion}}".
 *
 * @property string $id
 * @property integer $type
 * @property string $activityId
 * @property string $content
 * @property string $userId
 * @property string $parentUserId
 * @property string $parentUserName
 * @property string $logoUrl
 * @property string $userName
 * @property string $parentLogoUrl
 * @property integer $isDelete
 * @property string $created
 */
class Comment extends User implements IdentityInterface ,RateLimitInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'activityId', 'userId', 'weight', 'parentUserId', 'isDelete'], 'integer'],
            [['content'], 'string'],
            [['created'], 'safe'],
            [['parentUserName'], 'string', 'max' => 20],
            [['logoUrl'], 'string', 'max' => 400],
            [['userName'], 'string', 'max' => 255],
            [['parentLogoUrl'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '评论编号',
            'type' => '评论类型 0评论 1 回复评论',
            'activityId' => '关联交流id',
            'content' => '文字评论内容',
            'userId' => '用户编号',
            'weight' => '权重（推荐用）',
            'parentUserId' => '父类用户id',
            'parentUserName' => '父类用户名',
            'logoUrl' => 'Logo Url',
            'userName' => 'User Name',
            'parentLogoUrl' => '父类头像',
            'isDelete' => '回收站 0没有放入回收站 1放入回收站',
            'created' => 'Create Date',
        ];
    }
    
    /**
     *获取交流推荐评论
     */
    public static function getCommentForDiscussion($activityId,$number){
        $connection= \Yii::$app->dbSlave;
        $sql="SELECT id,type,content,userId,userName,logoUrl,parentUserId,parentUserName,parentLogoUrl,created FROM ".self::tableName()." WHERE activityId=$activityId AND isDelete=0 ORDER BY weight DESC,id DESC LIMIT $number";
        return $connection->createCommand ($sql)->queryAll();
    }
    
    /**
     * 增加一条交流评论
     */
    public function addComment($data){
        $cn = \Yii::$app->db;
        $sql = "INSERT INTO ".$this->tableName()." (type,activityId,content,userId,logoUrl,userName,parentUserId,parentUserName,parentLogoUrl,created) values ('".$data['type']."','".$data['activityId']."','".$data['content']."','".$data['userId']."','".$data['logoUrl']."','".$data['userName']."','".$data['parentUserId']."','".$data['parentUserName']."','".$data['parentLogoUrl']."','".time()."')";
        $result = $cn->createCommand($sql)->execute();
        if ($result) {
            return 1;
        }else{
            return 0;
        }
    }
    
    /**
     * 删除某个用户的一条交流评论
     */
    public function deleteOneComment($data){
        $cn = \Yii::$app->db;
        $sql = "DELETE FROM ".$this->tableName()."WHERE id = '".$data['id']."' AND userId = '".$data['userId']."'";
        $result = $cn->createCommand($sql)->execute();
        if ($result) {
            return 1;
        }else{
            return 0;
        }
    }
    
    /**
     * 删除某个用户的全部交流评论
     */
    public function deleteAllComment($data){
        $cn = \Yii::$app->db;
        $sql = "DELETE FROM ".$this->tableName()."WHERE userId = '".$data['userId']."'";
        $result = $cn->createCommand($sql)->execute();
        if ($result) {
            return 1;
        }else{
            return 0;
        }
    }
    
    /**
     * 修改交流评论内容
     */
    public function changeComment($data){
        $cn = \Yii::$app->db;
        $sql = "UPDATE ".$this->tableName()." SET content = '".$data['content']."',created = '".date('Y-m-d H:i:s')."' WHERE id = '".$data['id']."' AND activityId = '".$data['activityId']."' AND userId = '".$data['userId']."'";
        $result = $cn->createCommand($sql)->execute();
        if ($result) {
            return 1;
        }else{
            return 0;
        }
    }
    
    /**
     * 查询交流评论总条数
     */
    public function countCommentNum($data){
        $connection= \Yii::$app->dbSlave;
        $sql="SELECT count(1) FROM ".self::tableName()." WHERE activityId = '".$data['activityId']."'";
        $total = $connection->createCommand ($sql)->queryScalar();
        return $total;
    }
    
    /**
     * 查询交流评论
     */
    public function checkComment($start,$pageSize,$data){
        $connection= \Yii::$app->dbSlave;
        $sql="SELECT id,type,userId,parentUserId,parentUserName,content,logoUrl,userName,parentLogoUrl,created FROM ".self::tableName()." WHERE activityId = '".$data['activityId']."' ORDER BY id DESC LIMIT $start,$pageSize";
        return $connection->createCommand ($sql)->queryAll();
    }
    
    /**
     * 查询评论ID
     */
    public function checkid(){
        $id = \Yii::$app->db->getLastInsertID();
        /* $connection= \Yii::$app->dbSlave;
        $sql="SELECT id,created FROM ".self::tableName()." ORDER BY created DESC";
        return $connection->createCommand ($sql)->queryOne(); */
        return $id;
    }
    /**
    * 查询用户信息
    */
    public static function getUserInfoById($UserId,$fields){
        $cn = \Yii::$app->dbSlave;
        $sql = "SELECT $fields FROM ".self::tableName()." WHERE userId = $UserId";
        $result = $cn->createCommand($sql)->queryOne();
        if ($result) {
            return $result;
        }else{
            return array();
        }
    
    }
    
    /**
     * 查询列表
     */
    public function lists($data){
        $connection= \Yii::$app->dbSlave;
        $sql="SELECT * FROM ".self::tableName()." WHERE activityId IN (".$data['activityId'].")";
        return $connection->createCommand ($sql)->queryAll();
    }
}
