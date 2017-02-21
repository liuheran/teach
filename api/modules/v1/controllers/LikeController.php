<?php
namespace api\modules\v1\controllers;
use Yii;
use api\modules\v1\models\Like;
use api\modules\v1\models\Track;
class LikeController extends BaseController  
{
	
    /**
     * 添加喜欢
     */
    public function actionAdd()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $Like = new Like();
        $params = Yii::$app->request->post(); //接收参数
    
        $userId = isset($params['userId']) ? $params['userId']:0;
        $activityId = isset($params['activityId']) ? $params['activityId']:0;

        if (empty($userId) || ! is_numeric($userId) || empty($activityId) || ! is_numeric($activityId)) {
            return $this->failed(401001);
        }else {
            $data = array(
                'userId'=>$userId,
                'activityId'=>$activityId,
            );
    
            $addResult = $Like->add($data);
    
            if ($addResult) {
                return $this->success();
            }else{
                return $this->failed(401002);//插入失败
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
     * 取消喜欢
     */
    public function actionDelete()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $Like = new Like();
        $params = Yii::$app->request->post(); //接收参数
    
        $userId = isset($params['userId']) ? $params['userId']:0;
        $likeId = isset($params['likeId']) ? $params['likeId']:0;
    
        if (empty($userId) || ! is_numeric($userId) || empty($likeId) || ! is_numeric($likeId)) {
            return $this->failed(402001);
        } else {
            $data = array(
                'likeId'=>$likeId,
                'userId'=>$userId,
            );
    
            $deleteResult = $Like->deleteOne($data);
    
            if ($deleteResult) {
                return $this->success();
            }else{
                return $this->failed(402002);
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
     * 查询喜欢的列表
     */
    public function actionGetlist()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $Like = new Like();
        $params = Yii::$app->request->post(); //接收参数
    
        $userId = isset($params['userId']) ? $params['userId']:0;
        $pageSize = isset($params['pageSize']) && is_numeric($params['pageSize']) ? $params['pageSize']:5;
        $page = isset($params['page']) && is_numeric($params['page']) ? $params['page']:1;
    
        if (empty($userId) || ! is_numeric($userId)) {
            return $this->failed(403001);
        }else {
            $data = array(
                'userId'=>$userId,
            );
    
            $numResult = $Like->countNum($data);//总条数
    
            if ($numResult > 0) {
                $totalPage = ceil($numResult/$pageSize);//总页数
                $start = ($page-1)*$pageSize;
    
                if($page<=$totalPage)
                {
                    $list = $Like->lists($start,$pageSize,$data);
    
                    if(! empty($list)){
                        $data['list']=$list;
                        $data['totalRecord']=$numResult;
                        $data['totalPage']=$totalPage;
                    }else{
                       return $this->failed(403002); //查询失败
                    }
                }else{
                    $data['list']=[];
                    $data['totalRecord']=$numResult;
                    $data['totalPage']=$totalPage;
                }
    
            }elseif($numResult == 0){
                $data['list']=[];
                $data['totalRecord']=0;
                $data['totalPage']=0;
            }else{
                return $this->failed(403003); //查询失败
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
        $this->success($data);
    }
}