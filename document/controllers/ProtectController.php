<?php

namespace api\modules\v1\controllers;
use Yii;
use api\modules\v1\models\Protect;
use api\modules\v1\models\Track;
class ProtectController extends BaseController  
{
    /**
     * 添加
     */
    public function actionAdd()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $protect = new Protect();
        $params = Yii::$app->request->post(); //接收参数
    
        $userId = isset($params['userId']) ? $params['userId']:0;
        $protectId = isset($params['protectId']) ? $params['protectId']:0;

        if (empty($userId) || ! is_numeric($userId) || empty($protectId) || ! is_numeric($protectId)) {
            $this->failed(601001);
        }else {
            $data = array(
                'userId'=>$userId,
                'protectId'=>$protectId,
            );
    
            $addResult = $protect->add($data);
    
            if ($addResult) {
                $this->success();
            }else{
                $this->failed(601002);//插入失败
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
        $protect = new Protect();
        $params = Yii::$app->request->post(); //接收参数
    
        $userId = isset($params['userId']) ? $params['userId']:0;
        $protectId = isset($params['protectId']) ? $params['protectId']:0;
    
        if (empty($userId) || ! is_numeric($userId) || empty($protectId) || ! is_numeric($protectId)) {
            $this->failed(602001);
        } else {
            $data = array(
                'protectId'=>$protectId,
                'userId'=>$userId,
            );
    
            $deleteResult = $protect->deleteOne($data);
    
            if ($deleteResult) {
                $this->success();
            }else{
                $this->failed(602002);
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
     * 查询保护
     */
    public function actionList()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $protect = new Protect();
        $params = Yii::$app->request->post(); //接收参数
        
        $type = isset($params['type']) ? $params['type']:0;
        $userId = isset($params['userId']) ? $params['userId']:0;
        $pageSize = isset($params['pageSize']) && is_numeric($params['pageSize']) ? $params['pageSize']:5;
        $page = isset($params['page']) && is_numeric($params['page']) ? $params['page']:1;
    
        if (empty($userId) || ! is_numeric($userId)) {
            $this->failed(603001);
        }else {
            $data = array(
                'userId'=>$userId,
                'type' =>$type,
            );
    
            $numResult = $protect->countNum($data);//总条数
    
            if ($numResult > 0) {
                $totalPage = ceil($numResult/$pageSize);//总页数
                $start = ($page-1)*$pageSize;
    
                if($page<=$totalPage)
                {
                    $list = $protect->lists($start,$pageSize,$data);
    
                    if(! empty($list)){
                        $data['list']=$list;
                        $data['totalRecord']=$numResult;
                        $data['totalPage']=$totalPage;
                    }else{
                        $this->failed(603002); //查询失败
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
                $this->failed(603003); //查询失败
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
