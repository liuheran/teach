<?php

namespace api\modules\v1\controllers;
  
use Yii;  
use api\modules\v1\models\Notice;
use api\modules\v1\models\Track;
use api\modules\v1\models\Version;
class NoticeController extends BaseController  
{
    /**
     * 添加一条通知
     */
    public function actionAdd()
    {
    	$startDate = Track::microtimeFloat(); //开始时间
    	$notice = new Notice();
    	$params = Yii::$app->request->post(); //接收参数
        
    	$user_id = isset($params['userId']) ? $params['userId']:0;
    	$content = isset($params['content']) ? $params['content']:'';
    
    	$arrdata = array();
    
    	if (empty($user_id) || ! is_numeric($user_id) || empty($content)) {
    		$this->failed(801001);//参数有误
    	}else {
    		$data = array(
    				'userId'=>$user_id,
    				'content'=>$content,
    		);
    
    		$addResult = $notice->addNotice($data);
    
    		if ($addResult) {
    			$noticeId = $notice->checkNoticeId();
    			$dataArr = array_merge($noticeId,array("userId"=>$user_id,"content"=>$content));
    			$this->success($dataArr);
    		}else{
    			$this->failed(801002);//插入失败
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
     * 删除一条通知
     */
    public function actionDelete()
    {
    	$startDate = Track::microtimeFloat(); //开始时间
    	$notice = new Notice();
    	$params = Yii::$app->request->post(); //接收参数
    
    	$notice_id = isset($params['id']) ? $params['id']:0;;
    	$user_id = isset($params['userId']) ? $params['userId']:0;
    
    	$arrdata = array();
    
    	if (empty($notice_id) || ! is_numeric($notice_id) || empty($user_id) || ! is_numeric($user_id)) {
    		$this->failed(802001);//参数有误
    	}else {
    		$data = array(
    				'notice_id'=>$notice_id,
    				'user_id'=>$user_id,
    		);
    
    		$deleteResult = $notice->deleteOneNotice($data);
    
    		if ($deleteResult) {
    			$this->success();
    		}else{
    			$this->failed(802002);//删除失败
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
     * 删除全部通知
     */
    public function actionDeleteAll()
    {
    	$startDate = Track::microtimeFloat(); //开始时间
    	$notice = new Notice();
    	$params = Yii::$app->request->post(); //接收参数(3des加密)
    	
    	$user_id = isset($params['userId']) ? $params['userId']:0;
    
    	$arrdata = array();
    
    	if (empty($user_id) || ! is_numeric($user_id)) {
    		$this->failed(803001);//参数有误
    	}else {
    		$data = array(
    				'user_id'=>$user_id,
    		);
    
    		$deleteResult = $notice->deleteAllNotice($data);
    
    		if ($deleteResult) {
    			$this->success();
    		}else{
    		    $this->failed(803002);//删除失败
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
     * 查询通知
     */
    public function actionList()
    {
    	$startDate = Track::microtimeFloat(); //开始时间
    	$notice = new Notice();
    	$params = Yii::$app->request->post(); //接收参数(3des加密)
    
    	$user_id = isset($params['userId']) ? $params['userId']:0;
    	$pageSize = isset($params['pageSize']) && is_numeric($params['pageSize']) ? $params['pageSize']:5;
    	$page = isset($params['page']) && is_numeric($params['page']) ? $params['page']:1;
    	 
    	$arrdata = array();
    
    	if (empty($user_id)) {
    		$this->failed(804001);//参数有误
    	}else {
    		$data = array(
    				'user_id'=>$user_id,
    		);
    
    		$totalRecord = $notice->countNoticeNum($data);//通知总条数
    
    		if ($totalRecord > 0) {
    			$totalPage = ceil($totalRecord/$pageSize);//总页数
    			$start = ($page-1)*$pageSize;
    
    			if($page<=$totalPage)
    			{
    				$checkResult = $notice->checkNotice($start,$pageSize,$data);
    
    				if(!empty($checkResult)){
    					$arrdata['list']=$checkResult;
    					$arrdata['totalRecord']=$totalRecord;
    					$arrdata['totalPage']=$totalPage;
    				}else{
    					$this->failed(804003);//查询失败
    				}
    			}else{
    				$arrdata['list']=[];
    				$arrdata['totalRecord']=$totalRecord;
    				$arrdata['totalPage']=$totalPage;
    			}
    			 
    		}elseif($totalRecord == 0){
    			$arrdata['list']=[];
    			$arrdata['totalRecord']=0;
    			$arrdata['totalPage']=0;
    		}else{
    			$this->failed(804002);//查询失败
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
    	$this->success($arrdata);
    }
    
    /**
     * @author jiangyuying
     * @date 2016年10月20日下午5:07:02
     * 版本升级通知
     */
    public function actionVersionUpgrade()
    {
    	$startDate = Track::microtimeFloat(); //开始时间
    	$params = Yii::$app->request->post(); //接收参数
    	$type = isset($params['type'])?$params['type']:'';
    	$version = isset($params['version'])?$params['version']:'';
    	if(!empty($type) && !empty($version)){
    		if('ios'==strtolower($type) || 'android'==strtolower($type)){
    			$result = Version::checkVersion($type, $version);
    			if(!$result){
    			    $this->failed(151000); //不用升级
    			}else{
    				$data['Data']['message']=$result['message'];
    				$data['Data']['upgrade_type']=$result['upgrade_type'];
    				$data['Data']['download_url']=$result['download_url'];
    				$this->success($data);
    			}
    		}else{
    		    $this->failed(151002); //参数错误
    		}
    	}else{
    	    $this->failed(151001); //参数缺失
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
}