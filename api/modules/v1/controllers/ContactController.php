<?php

namespace api\modules\v1\controllers;
use Yii;
use api\modules\v1\models\Contact;
use api\modules\v1\models\Track;

class ContactController extends BaseController  
{
    /**
     * 添加
     */
    public function actionAdd()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $contact = new Contact();
        $params = Yii::$app->request->post(); //接收参数
    
        $userId = isset($params['userId']) ? $params['userId']:0;
        $contactId = isset($params['contactId']) ? $params['contactId']:0;

        if (empty($userId) || ! is_numeric($userId) || empty($contactId) || ! is_numeric($contactId)) {
            $this->failed(701001);
        }else {
            $data = array(
                'userId'=>$userId,
                'contactId'=>$contactId,
            );
    
            $addResult = $contact->add($data);
    
            if ($addResult) {
                $this->success();
            }else{
                $this->failed(701002);//插入失败
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
     * 删除一条联系人
     */
    public function actionDelete()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $contact = new Contact();
        $params = Yii::$app->request->post(); //接收参数
    
        $userId = isset($params['userId']) ? $params['userId']:0;
        $contactId = isset($params['contactId']) ? $params['contactId']:0;
    
        if (empty($userId) || ! is_numeric($userId) || empty($contactId) || ! is_numeric($contactId)) {
            $this->failed(702001);
        } else {
            $data = array(
                'contactId'=>$contactId,
                'userId'=>$userId,
            );
    
            $deleteResult = $contact->deleteOne($data);
    
            if ($deleteResult) {
                $this->success();
            }else{
                $this->failed(702002);
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
     * 查询联系人
     */
    public function actionGetlist()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $contact = new Contact();
        $params = Yii::$app->request->post(); //接收参数
    
        $userId = isset($params['userId']) ? $params['userId']:0;
        $pageSize = isset($params['pageSize']) && is_numeric($params['pageSize']) ? $params['pageSize']:5;
        $page = isset($params['page']) && is_numeric($params['page']) ? $params['page']:1;
    
        if (empty($userId) || ! is_numeric($userId)) {
            $this->failed(703001);
        }else {
            $data = array(
                'userId'=>$userId,
            );
    
            $numResult = $contact->countNum($data);//总条数
    
            if ($numResult > 0) {
                $totalPage = ceil($numResult/$pageSize);//总页数
                $start = ($page-1)*$pageSize;
    
                if($page<=$totalPage)
                {
                    $list = $contact->lists($start,$pageSize,$data);
    
                    if(! empty($list)){
                        $data['list']=$list;
                        $data['totalRecord']=$numResult;
                        $data['totalPage']=$totalPage;
                    }else{
                        $this->failed(703002); //查询失败
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
                $this->failed(703003); //查询失败
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
