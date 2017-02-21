<?php

namespace api\modules\v1\controllers;
use Yii;
use api\modules\v1\models\Activity;
use api\modules\v1\models\Track;
use api\modules\v1\models\Like;
use api\modules\v1\models\Comment;
class ActivityController extends BaseController  
{
    /**
     * 添加
     */
    public function actionAdd()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $activity = new Activity();
        $params = Yii::$app->request->post(); //接收参数
    
        $userId = isset($params['userId']) ? $params['userId']:0;
        $content = isset($params['content']) ? $params['content']:'';
        $location = isset($params['location']) ? $params['location']:'';
        $images = isset($params['images']) ? $params['images']:'';

        if (empty($userId) || ! is_numeric($userId) || (empty($content) && empty($images))) {
        	return $this->failed(301001);
        }else {
            $data = array(
                'userId'=>$userId,
                'content'=>$content,
                'location' => $location,
                'images' => $images
            );
    
            $addResult = $activity->add($data);
    
            if ($addResult) {
               return $this->success();
            }else{
               return $this->failed(301002);//插入失败
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
    }
    
    /**
     * 删除
     */
    public function actionDelete()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $activity = new Activity();
        $params = Yii::$app->request->post(); //接收参数
    
        $id = isset($params['id']) ? $params['id']:0;
        $userId = isset($params['userId']) ? $params['userId']:0;
    
        if (empty($userId) || ! is_numeric($userId) || empty($id) || ! is_numeric($id)) {
            return $this->failed(302001);
        } else {
            $data = array(
                'id'=>$id,
                'userId'=>$userId,
            );
    
            $deleteResult = $activity->deleteOne($data);
            if ($deleteResult) {
                return $this->success();
            }else{
                return $this->failed(302002);
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
    }
    
    /**
     * 查询
     */
    public function actionList()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $activity = new Activity();
        $params = Yii::$app->request->post(); //接收参数
        
        $userId = isset($params['userId']) ? $params['userId']:0;
        $pageSize = isset($params['pageSize']) && is_numeric($params['pageSize']) ? $params['pageSize']:5;
        $page = isset($params['page']) && is_numeric($params['page']) ? $params['page']:1;
    
        if (!empty($userId) && ! is_numeric($userId)) {
            return $this->failed(303001);
        }else {
            $data = array(
                'userId'=>$userId,
            );
    
            $numResult = $activity->countNum($data);//总条数
    
            if ($numResult > 0) {
                $totalPage = ceil($numResult/$pageSize);//总页数
                $start = ($page-1)*$pageSize;
    
                if($page<=$totalPage)
                {
                    $list = $activity->lists($start,$pageSize,$data);
                    if(! empty($list)){
                        foreach ($list as $key=>$value ) {
                            $ids[] = $value['id'];
                        }
                        $id = rtrim(implode(',', $ids) ,',');
                        $like = new Like();
                        $likeList =  $like->lists(['activityId'=>$id]);
                        foreach ($likeList as $key=>$value) {
                            $activitys['like'][$value['activityId']][] = $value;
                        }
                        $comment = new Comment();
                        $commentList =  $comment->lists(['activityId'=>$id]);
                        foreach ($commentList as $key=>$value) {
                            $activitys['comment'][$value['activityId']][] = $value;
                        }
                        foreach ($list as $key=>$value ) {
                            if(isset($activitys['like'][$value['id']])) {
                                $list[$key]['likeList'] = $activitys['like'][$value['id']];
                            }else {
                                $list[$key]['likeList']=[];
                            }
                            if(isset($activitys['comment'][$value['id']])) {
                                $list[$key]['commentList'] = $activitys['comment'][$value['id']];
                            }else {
                                $list[$key]['commentList'] = [];
                            }
                        }
                        $data['list'] = $list;
                        $data['totalRecord'] = $numResult;
                        $data['totalPage'] = $totalPage;
                    }else{
                        $this->failed(303002); //查询失败
                    }
                }else{
                    $data['list']=[];
                    $data['totalRecord'] = $numResult;
                    $data['totalPage'] = $totalPage;
                }
    
            }elseif($numResult == 0){
                $data['list'] = [];
                $data['totalRecord'] = 0;
                $data['totalPage'] = 0;
            }else{
               return $this->failed(303003); //查询失败
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
        return $this->success($data);
    }
    
    /**
     * 查询
     */
    public function actionAll()
    {
    	$startDate = Track::microtimeFloat(); //开始时间
    	$activity = new Activity();
    	$params = Yii::$app->request->post(); //接收参数
    
    	$pageSize = isset($params['pageSize']) && is_numeric($params['pageSize']) ? $params['pageSize']:5;
    	$page = isset($params['page']) && is_numeric($params['page']) ? $params['page']:1;
    
    	if (!empty($userId) && ! is_numeric($userId)) {
    		return $this->failed(303001);
    	}else {
    
    		$numResult = $activity->countNum($data);//总条数
    
    		if ($numResult > 0) {
    			$totalPage = ceil($numResult/$pageSize);//总页数
    			$start = ($page-1)*$pageSize;
    
    			if($page<=$totalPage)
    			{
    				$list = $activity->lists($start,$pageSize,$data);
    				if(! empty($list)){
    					foreach ($list as $key=>$value ) {
    						$ids[] = $value['id'];
    					}
    					$id = rtrim(implode(',', $ids) ,',');
    					$like = new Like();
    					$likeList =  $like->lists(['activityId'=>$id]);
    					foreach ($likeList as $key=>$value) {
    						$activitys['like'][$value['activityId']][] = $value;
    					}
    					$comment = new Comment();
    					$commentList =  $comment->lists(['activityId'=>$id]);
    					foreach ($commentList as $key=>$value) {
    						$activitys['comment'][$value['activityId']][] = $value;
    					}
    					foreach ($list as $key=>$value ) {
    						if(isset($activitys['like'][$value['id']])) {
    							$list[$key]['likeList'] = $activitys['like'][$value['id']];
    						}else {
    							$list[$key]['likeList']=[];
    						}
    						if(isset($activitys['comment'][$value['id']])) {
    							$list[$key]['commentList'] = $activitys['comment'][$value['id']];
    						}else {
    							$list[$key]['commentList'] = [];
    						}
    					}
    					$data['list'] = $list;
    					$data['totalRecord'] = $numResult;
    					$data['totalPage'] = $totalPage;
    				}else{
    					$this->failed(303002); //查询失败
    				}
    			}else{
    				$data['list']=[];
    				$data['totalRecord'] = $numResult;
    				$data['totalPage'] = $totalPage;
    			}
    
    		}elseif($numResult == 0){
    			$data['list'] = [];
    			$data['totalRecord'] = 0;
    			$data['totalPage'] = 0;
    		}else{
    			return $this->failed(303003); //查询失败
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
    	return $this->success($data);
    }
}
