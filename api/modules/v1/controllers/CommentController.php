<?php

namespace api\modules\v1\controllers;

use Yii;
use api\modules\v1\models\Comment;
use api\modules\v1\models\User;
use api\modules\v1\models\Track;

class CommentController extends BaseController  
{
    
    /**
     * 添加一条评论
     */
    public function actionAdd()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $Comment = new Comment();
        $user = new User();
        $params = Yii::$app->request->post(); //接收参数
        $type = isset($params['type']) ? $params['type'] : 0;
        $activityId = isset($params['activityId']) ? $params['activityId'] : 0;
        $content = isset($params['content']) ? $params['content'] : '';
        $userId = isset($params['userId']) ? $params['userId'] : 0;
        
        $arrdata = array();
        
        if($type == 0)
        {
            if(empty($activityId) || empty($content) || empty($userId))
            {
              return $this->failed(501001);
            }else{
                    $result = $user::getUserInfoByUserId($userId);
                    $userName = $result['username'];
                    $logoUrl = $result['logoUrl'];
                    
                    $parentUserId = 0;
                    $parentUserName = "";
                    $parentLogoUrl = "";
            
                    $data = array(
                        'type'=>$type,
                        'activityId'=>$activityId,
                        'content'=>$content,
                        'userId'=>$userId,
                        'logoUrl'=>$logoUrl,      
                        'userName'=>$userName,
                        'parentUserId'=>$parentUserId,
                        'parentUserName'=>$parentUserName,
                        'parentLogoUrl'=>$parentLogoUrl,
                    );      
            
                    $addResult = $Comment->addComment($data);
                
                    if ($addResult) {
                        $id = $Comment->checkid();
                        $dataArr = array_merge(array('id'=>$id),array("type"=>$type,"activityId"=>$activityId,"content"=>$content,"userId"=>$userId,"logoUrl"=>$logoUrl,"userName"=>$userName));
                        return $this->success($dataArr);
                    }else{
                          return  $this->failed(501002); //插入失败
                        }                  
            }
        }else{
            $parentUserId = $params['parentUserId'];
            if(empty($activityId) || empty($content) || empty($userId) || empty($parentUserId))
            {
               return $this->failed(501001);
            }else{
                    $result1 = $user::getUserInfoByUserId($userId);
                    $userName = $result1['username'];
                    $logoUrl = $result1['logoUrl'];
                    
                    $result2 = $user::getUserInfoByUserId($parentUserId);
                    $parentUserName = $result2['username'];
                    $parentLogoUrl = $result2['logoUrl'];
            
                    $data = array(
                        'type'=>$type,
                        'activityId'=>$activityId,
                        'content'=>$content,
                        'userId'=>$userId,
                        'logoUrl'=>$logoUrl,      
                        'userName'=>$userName,
                        'parentUserId'=>$parentUserId,
                        'parentUserName'=>$parentUserName,
                        'parentLogoUrl'=>$parentLogoUrl,
                    );
            
                    $addResult = $Comment->addComment($data);
                
                    if ($addResult) {
                        $id = $Comment->checkid();
                        $dataArr = array_merge(array('id'=>$id),array("type"=>$type,"activityId"=>$activityId,"content"=>$content,"userId"=>$userId,"logoUrl"=>$logoUrl,"userName"=>$userName,"parentUserId"=>$parentUserId,"parentUserName"=>$parentUserName,"parentLogoUrl"=>$parentLogoUrl));
                        return $this->success($dataArr);
                    }else{
                         return $this->failed(501002); //插入失败
                        }                  
            }
        }
        
        //接口结束时间
    	if(Yii::$app->params['enableTrack']){
    		//存入showtime_track库
    		$endDate = Track::microtimeFloat();//结束时间
    		$spendTime = $endDate-$startDate; //花费时间
    		$trackName=__CLASS__.'/'.__FUNCTION__; //方法名
    		$arguments = json_encode($params);
    		Track::addTrack($spendTime, $trackName,$arguments); //入库
    	}

        return $arrdata;
    }
    
    /**
     * 删除某个用户的一条交流评论
     */
    public function actionDelete()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $Comment = new Comment();
        $params = Yii::$app->request->post(); //接收参数
        
        $id = isset($params['id']) ? $params['id'] : 0;
        $userId = isset($params['userId']) ? $params['userId'] : 0;
        
        $arrdata = array();
        
        if (empty($id) || empty($userId)) {
           return $this->failed(502001);
        }else {
            $data = array(
                    'id'=>$id,
                    'userId'=>$userId,
            );
            $deleteResult = $Comment->deleteOneComment($data);
            if ($deleteResult) {
                return $this->success();
            }else{
                return $this->failed(502002);
            }
        }
        
        //接口结束时间
        if(Yii::$app->params['enableTrack']){
            //存入showtime_track库
            $endDate = Track::microtimeFloat();//结束时间
            $spendTime = $endDate-$startDate; //花费时间
            $trackName=__CLASS__.'/'.__FUNCTION__; //方法名
            $arguments = json_encode($params);
            Track::addTrack($spendTime, $trackName,$arguments); //入库
        }

        return $arrdata;
    }
    
    /**
     * 修改评论内容
     */
    public function actionUpdate()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $Comment = new Comment();
        $params = Yii::$app->request->post(); //接收参数
        $id = isset($params['id']) ? $params['id'] : 0;
        $activityId = isset($params['activityId']) ? $params['activityId'] : 0;
        $content = isset($params['content']) ? $params['content'] : '';
        $userId = isset($params['userId']) ? $params['userId'] : 0;
        
        $arrdata = array();
        
        if (empty($id) || empty($activityId) || empty($content) || empty($userId)) {
           return $this->failed(503001);
        }else {
            $data = array(
                'id'=>$id,
                'activityId'=>$activityId,
                'content'=>$content,
                'userId'=>$userId,
            );
            
            $changeResult = $Comment->changeComment($data);
            
            if ($changeResult) {
                return $this->success();
            }else{
                   return $this->failed(503002);
                }
        }
        
        //接口结束时间
        if(Yii::$app->params['enableTrack']){
            //存入showtime_track库
            $endDate = Track::microtimeFloat();//结束时间
            $spendTime = $endDate-$startDate; //花费时间
            $trackName=__CLASS__.'/'.__FUNCTION__; //方法名
            $arguments = json_encode($params);
            Track::addTrack($spendTime, $trackName,$arguments); //入库
        }

        return $arrdata;
    }
    
    /**
     * 查询交流评论
     */
    public function actionList()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $Comment = new Comment();
        $params = Yii::$app->request->post(); //接收参数
        $activityId = isset($params['activityId']) ? $params['activityId'] : 0;
        $pageSize = isset($params['pageSize']) && is_numeric($params['pageSize']) ? $params['pageSize']:5;
        $page = isset($params['page']) && is_numeric($params['page']) ? $params['page']:1;
        
        $arrdata = array();
        
        if (empty($activityId)) {
            $this->failed(504001);
        }else {
            $data = array(
                'activityId'=>$activityId,
            );
                
            $numResult = $Comment->countCommentNum($data);//交流评论总条数

            if ($numResult > 0) {
                $totalPage = ceil($numResult/$pageSize);//总页数
                $start = ($page-1)*$pageSize;
                
                $checkResult = $Comment->checkComment($start,$pageSize,$data);
                
                if(!empty($checkResult)){
        		    $arrdata['Status']=1;
        		    $arrdata['Data']['checkResult']=$checkResult;
        		    $arrdata['numResult']=$numResult;
        		    $arrdata['totalPage']=$totalPage;
        		}else{
        		    $arrdata['Status']=8310;//查询失败
        	    }
           
            }elseif($numResult == 0){
                $arrdata['Status']=1;
                $arrdata['Data']['checkResult']="";
                $arrdata['numResult']=0;
		        $arrdata['totalPage']=0;
            }else{
                $arrdata['Status']=8311;//查询失败
            }            
        }
        
         //接口结束时间
        if(Yii::$app->params['enableTrack']){
            //存入showtime_track库
            $endDate = Track::microtimeFloat();//结束时间
            $spendTime = $endDate-$startDate; //花费时间
            $trackName=__CLASS__.'/'.__FUNCTION__; //方法名
            $arguments = json_encode($params);
            Track::addTrack($spendTime, $trackName,$arguments); //入库
        }
        return $this->success($arrdata);
    }
}
