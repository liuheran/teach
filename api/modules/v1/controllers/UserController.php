<?php

namespace api\modules\v1\controllers;
  
use Yii;  
use api\modules\v1\models\User;
use api\modules\v1\models\Track;
use api\modules\v1\models\FileUpload;
class UserController extends BaseController  
{  
     
    public function actionIndex()  
    {  
    	var_dump(Yii::$app->user);exit;
        echo Yii::$app->user->id;
        echo \Yii::$app->user->username;
    }
    
    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        $user = new User();
        $result = $user->logOut();
        if($result != null){
           return $this->success();
        }else{
           return $this->failed(1001101);
        }
    }
    
    /**
     * 处理一起按加朋友请求
     */
    public function actionSendApply()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $params = Yii::$app->request->post(); //接收参数
        $userid = $params['userId'];
        $distance = $params['distance'];
        if (empty($userid) || ! is_numeric($userid) || $userid<=0) {
            return $this->failed(160001);
        }
        if (empty($distance) || ! is_numeric($distance) || $distance<=0) {
            return $this->failed(160002);
        }
        $model=new User();
        //获得用户信息，插入到search表
        $userResult = $model->getUserInfoByUserId($userid);
        if (! $userResult) {
            return $this->failed(160003);
        }
        //获取用户信息
        $dataParams = array(
            'userId' => $userid,
            'distance' => $distance,
            'latitude' => $userResult['latitude'],
            'longitude' => $userResult['longitude'],
        );
        $contentResult = $model->getUserInfo($dataParams);
        if ($contentResult) {
            $data = $contentResult;
        }else{
            $data = null;
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
     * 1、上传图片
     */
    public function actionUploadImage(){
    
        $width  = 750; //默认生成缩略图片宽度为750px
    
        if (isset($_POST) && isset($_FILES)){
            //交流Id
            $discussionId = isset($_POST['discussionId']) && is_numeric($_POST['discussionId']) ? $_POST['discussionId']:0;
    
            $userId = isset($_POST['userId']) && is_numeric($_POST['userId']) ? $_POST['userId']:0;
    
            if ($discussionId <= 0 || $userId <= 0) {
                $data['Status'] = 3101;
                return $data;
            }
    
            $description = isset($_POST['description']) ? $_POST['description']:'';
    
            $type = isset($_POST['type']) ? $_POST['type']:'add';
    
            //如果为modify验证是否设置pageId
            if($type == 'modify'){
                $pageId = isset($_POST['pageId']) ? $_POST['pageId']:0;
                if ($pageId <= 0) {
                    $data['Status'] = 3102;
                    return $data;
                }
            }
    
            $page = new DiscussionPage();
    
            // 验证用户和交流Id是否一致是否为初始化状态---待开发
    
    
            $path = './uploads/discussion/'.$userId.'/'.$discussionId; //图片上传路径
            //上传图片操作获取图片地址
    
            $upload = new FileUpload($path);
            $upload -> set('maxsize', '8000000'); //设置最大8M图片
            if(!$upload -> upload('image')){
                $data['Status'] = 3103;
                return $data;
            }
            $name = $upload->getFileName();
            $thumbName = $upload->thumb($name, $width);
    
            list($width, $height) = getimagesize($path.'/'.$thumbName);
    
            if($type == 'add'){
                //存储
                $discussionPage['discussionId'] = $discussionId;
                $discussionPage['description'] = $description;
                $discussionPage['pageSize'] = $width.'_'.$height;
                $discussionPage['width'] = $width;
                $discussionPage['height'] = $height;
                $discussionPage['pageImg'] = yii::$app->params['imageUrl'].trim($path,'.').'/'.$name;
                $discussionPage['pageImgThumb'] = yii::$app->params['imageUrl'].trim($path,'.').'/'.$thumbName;
                $id = $page->addPage($discussionPage);
                if($id){
                    $data['Status'] =1;
                    return $data;
                }
            }else if($type=='modify'){
                $discussionPage['discussionId'] = $discussionId;
                $discussionPage['pageId'] = $pageId;
                $discussionPage['description'] = $description;
                $discussionPage['pageSize'] = $width.'_'.$height;
                $discussionPage['width'] = $width;
                $discussionPage['height'] = $height;
                $discussionPage['pageImg'] = yii::$app->params['imageUrl'].trim($path,'.').'/'.$name;
                $discussionPage['pageImgThumb'] = yii::$app->params['imageUrl'].trim($path,'.').'/'.$thumbName;
                $id = $page->modifyPage($discussionPage);
                if($id){
                    $data['Status'] =1;
                    return $data;
                }else{
                    $data['Status'] =3104;
                    return $data;
                }
            }elseif ($type=='modify_add'){//编辑页面执行添加操作
    
                //存储
                $discussionPage['discussionId'] = $discussionId;
                $discussionPage['description'] = $description;
                $discussionPage['pageSize'] = $width.'_'.$height;
                $discussionPage['width'] = $width;
                $discussionPage['height'] = $height;
                $discussionPage['pageImg'] = yii::$app->params['imageUrl'].trim($path,'.').'/'.$name;
                $discussionPage['pageImgThumb'] = yii::$app->params['imageUrl'].trim($path,'.').'/'.$thumbName;
                $id = $page->modifyAddPage($discussionPage);
                if($id){
                    $data['Status'] =1;
                    return $data;
                }
    
            }else{
                $data['Status'] = 3102;
                return $data;
            }
        }else{
            $data['Status'] = 3100;
            return $data;
        }
    }
    
    /**
     * 2、修改图片状态
     */
    public function actionModifyPage(){
        $startDate = Track::microtimeFloat(); //开始时间
    
        $param = isset($_REQUEST['Param'])?$_REQUEST['Param']:''; //接收参数(3des加密)
         
        //转换数据并进行安全验证
        if(!empty($param) && strlen($param) > 2)
            $params = parent::checkParam($param);
        else
            return ;
    
        // 交流Id
        $discussionId = isset($params['discussionId']) && is_numeric($params['discussionId']) ? $params['discussionId']:0;
    
        // 获取用户Id
        $userId = isset($params['userId']) && is_numeric($params['userId']) ? $params['userId']:0;
    
        $datas = isset($params['data']) ? json_decode($params['data'],1):''; //修改类型 1 正常 2描述 3 更换图片 4全部删除
    
        if ($discussionId<=0 || $userId<=0 || empty($datas)) {
            $data['Status'] = 3101;
            return $data;
        }
    
        $page = new DiscussionPage();
    
        $isSuccess = $page->modifySourceStatus($datas);
    
        if($isSuccess){
            $data['Status'] = 1;
        }else{
            $data['Status']=3102;
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
         
        //3des加密返回值
        return $data;
    }
    
    /**
     * @author jiangyuying
     * @date 2016年10月13日下午2:27:54
     * 上传或修改头像
     */
    public function actionUploadLogoUrl(){
        $user = new User();
        $width  = 120; //默认生成缩略图片宽度为120px
    
        if (isset($_POST) && isset($_FILES)){

            $userId = isset($_POST['userId']) && is_numeric($_POST['userId']) ? $_POST['userId']:0;
    
            if ($userId <= 0) {
                $data['Status'] = 1330;//参数有误
                return $this->failed(100320);//参数有误
            }
    
            $path = './uploads/user/'.$userId; //图片上传路径
            //上传图片操作获取图片地址
    
            $upload = new FileUpload($path);
            $upload -> set('maxsize', '8000000'); //设置最大8M图片
            if(!$upload -> upload('image')){
                $data['Status'] = 1331;//参数有误
                return $this->failed(100320);//参数有误
            }
            $name = $upload->getFileName();
            $thumbName = $upload->thumb($name, $width,0,'120_');//存数据库 120*120
            $upload->thumb($name, 60,0,'60_');//生成 60*60
    
            list($width, $height) = getimagesize($path.'/'.$thumbName);
    
            //存储
            $logo_url = yii::$app->params['imageUrl'].trim($path,'.').'/'.$thumbName;
            $fields = " logoUrl='{$logo_url}'";
            $id = $user->updateUserInfo($userId,$fields);
            if($id>0){
                $data=array('user_id'=>$userId,'logo_url'=>$logo_url);
                return $this->success($data);//参数有误
            }else {
                $data['Status'] = 1332;//上传失败
                return $this->failed(100320);//参数有误
            }
        }else{
            $data['Status'] = 1333;//上传失败
            return $this->failed(100320);//参数有误
        }
    }
    
    /**
     * @author jiangyuying
     * @date 2016年10月13日下午2:27:54
     * 上传或修改头像
     */
    public function actionUploadCover()
    {
        $user = new User();
        $width  = 200; //默认生成缩略图片宽度为200px
    
        if (isset($_POST) && isset($_FILES)){
    
            $userId = isset($_POST['userId']) && is_numeric($_POST['userId']) ? $_POST['userId']:0;
    
            if ($userId <= 0) {
                $data['Status'] = 1330;//参数有误
                return $this->failed(100320);//参数有误
            }
    
            $path = './uploads/user/'.$userId; //图片上传路径
            //上传图片操作获取图片地址
    
            $upload = new FileUpload($path);
            $upload -> set('maxsize', '8000000'); //设置最大8M图片
            if(!$upload -> upload('image')){
                $data['Status'] = 1331;//参数有误
                return $this->failed(100320);//参数有误
            }
            $name = $upload->getFileName();
            $thumbName = $upload->thumb($name, $width,0,'200_');//存数据库 200*120
            $upload->thumb($name, 120,0,'120_');//生成 60*60
    
            list($width, $height) = getimagesize($path.'/'.$thumbName);
    
            //存储
            $url = yii::$app->params['imageUrl'].trim($path,'.').'/'.$thumbName;
            $fields = " cover='{$url}'";
            $id = $user->updateUserInfo($userId,$fields);
            if($id>0){
                $data=array('user_id'=>$userId,'cover'=>$url);
                return $this->success($data);//参数有误
            }else {
                $data['Status'] = 1332;//上传失败
                return $this->failed(100320);//参数有误
            }
        }else{
            $data['Status'] = 1333;//上传失败
            return $this->failed(100320);//参数有误
        }
    }
    
    /**
     * @author jiangyuying
     * @date 2016年10月13日下午2:27:54
     * 上传动态图片
     */
    public function actionUploadActivityImage(){
    	$user = new User();
    	$width  = 750; //默认生成缩略图片宽度为750px
    
    	if (isset($_POST) && isset($_FILES)){
    
    		$userId = isset($_POST['userId']) && is_numeric($_POST['userId']) ? $_POST['userId']:0;
    
    		if ($userId <= 0) {
    			$data['Status'] = 1330;//参数有误
    			return $this->failed(100320);//参数有误
    		}
    
    		$path = './uploads/user/'.$userId; //图片上传路径
    		//上传图片操作获取图片地址
    
    		$upload = new FileUpload($path);
    		$upload -> set('maxsize', '10000000'); //设置最大10M图片
    		if(!$upload -> upload('image')){
    			$data['Status'] = 1331;//参数有误
    			return $this->failed(100320);//参数有误
    		}
    		$name = $upload->getFileName();
    		$thumbName = $upload->thumb($name, $width,0,'750_');//存数据库 750*750
    		$upload->thumb($name, 120,0,'120_');//生成 120*60
    
    		list($width, $height) = getimagesize($path.'/'.$thumbName);
    
    		//存储
    		$url = yii::$app->params['imageUrl'].trim($path,'.').'/'.$thumbName;
    		if($userId > 0){
    			$data=array('user_id'=>$userId,'url'=>$url);
    			return $this->success($data);//参数有误
    		}else {
    			return $this->failed(100320);//参数有误
    		}
    	}else{
    		return $this->failed(100320);//参数有误
    	}
    }
    
    /**
     * @author jiangyuying
     * @date 2015年10月13日下午2:01:51
     * 修改用户昵称/性别和签名
     */
    public function actionChange()
    {
    	$startDate = Track::microtimeFloat(); //开始时间
    	$user = new user();
    	 
    	$params = Yii::$app->request->post(); //接收参数
    
    	$userId   = isset($params['userId'])?$params['userId']:0;
    	$userName = isset($params['userName'])?$params['userName']:'';
    	$sex      = isset($params['sex']) && is_numeric($params['sex'])?(int) $params['sex'] : 0;
    	$address  = isset($params['address'])?$params['address']:'';
    
    	$data = array();
    	if(! empty($userId) && $userId == Yii::$app->user->id && ! empty($userName) && ! empty($sex) && ! empty($address))
    	{
    		$userName = addslashes($userName);
    		$address  = addslashes($address);
    		
    		$user = User::find()
    		->where(['id' => $userId])
    		->one();
    		$user->username = $userName;
    		$user->address  = $address;
    		$user->sex      = $sex;
    		if($user->save()){ //修改成功
    			$data['status'] = 200;
    			$data['data']   = array("user_id"=>$userId,"username"=>$userName,"sex"=>$sex,'address'=>$address);
    		}else{
    			return $this->failed(100321);
    		}
    
    	}else{
    		return $this->failed(100320);//参数有误
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
    	return $data;
    }
    
    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionGetUser()
    {
    	$startDate = Track::microtimeFloat(); //开始时间
    	$param = Yii::$app->request->post();
    	if (! isset($param['phoneNumber']) || ! is_numeric($param['phoneNumber']) || strlen($param['phoneNumber']) != 11) {
    		return ['status'=>100001,'msg'=>Yii::$app->params['errorCode']['100001']];
    	}
    	$model = new User();
    	$user = $model->findByPhone($param['phoneNumber']);
    	if (! empty($user)) {
    		$data['username'] = $user['username'];
    		$data['logoUrl'] = $user['logoUrl'];
    		$data['cover'] = $user['cover'];
    		$data['userId'] = $user['id'];
    		return $this->success($data);	//参数有误
    	} else {
    		return $this->failed(100401);	//参数有误
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
     * Signs user up.
     *
     * @return mixed
     */
    public function actionGetUserById()
    {
        $startDate = Track::microtimeFloat(); //开始时间
        $param = Yii::$app->request->post();
        if (! isset($param['userId']) || empty($param['userId'])) {
            return ['status'=>100501,'msg'=>Yii::$app->params['errorCode']['100501']];
        }
        $model = new User();
        $user = $model->findOne($param['userId']);
        if (! empty($user)) {
            $data['username'] = $user['username'];
            $data['logoUrl'] = $user['logoUrl'];
            $data['cover'] = $user['cover'];
            $data['userId'] = $user['id'];
            return $this->success($data);	//参数有误
        } else {
            return $this->failed(100401);	//参数有误
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