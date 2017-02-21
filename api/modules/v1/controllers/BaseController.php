<?php

namespace api\modules\v1\controllers;
  
use Yii;  
use yii\web\Controller;
use yii\filters\auth\CompositeAuth;  
use yii\filters\auth\QueryParamAuth;  
use yii\filters\RateLimiter;
use yii\filters\ContentNegotiator;
use yii\web\Response;
class BaseController extends Controller  
{  
     
    public function init(){  
            parent::init();  
    }  
      
    # 行为 添加   
    #   验证 ：authenticator  
    #   速度控制：rateLimiter  
    public function behaviors()  
    {  
        $behaviors = parent::behaviors();  
        $behaviors['authenticator'] = [  
            'class' => CompositeAuth::className(),  
            'authMethods' => [  
                # 下面是三种验证access_token方式  
                //HttpBasicAuth::className(),  
                //HttpBearerAuth::className(),  
                # 这是GET参数验证的方式  
                # http://10.10.10.252:600/user/index/index?access-token=xxxxxxxxxxxxxxxxxxxx  
                QueryParamAuth::className(),  
            ],  
          
        ];  
        $behaviors['contentNegotiator'] =[
        		'class' => ContentNegotiator::className(),
        		'formats' => [
        				'application/json' => Response::FORMAT_JSON,
        				'application/xml' => Response::FORMAT_XML,
        		],
        ];
          
        # rate limit部分，速度的设置是在  
        #   \myapp\code\core\Erp\User\models\User::getRateLimit($request, $action){  
        /*  官方文档：  
            当速率限制被激活，默认情况下每个响应将包含以下HTTP头发送 目前的速率限制信息：  
            X-Rate-Limit-Limit: 同一个时间段所允许的请求的最大数目;  
            X-Rate-Limit-Remaining: 在当前时间段内剩余的请求的数量;  
            X-Rate-Limit-Reset: 为了得到最大请求数所等待的秒数。  
            你可以禁用这些头信息通过配置 yii\filters\RateLimiter::enableRateLimitHeaders 为false, 就像在上面的代码示例所示。  
  
        */  
        $behaviors['rateLimiter'] = [  
            'class' => RateLimiter::className(),  
            'enableRateLimitHeaders' => true,  
        ];  
        return $behaviors;  
    }
    
    public function failed($code)
    {
        return array('status'=>$code,'msg'=>Yii::$app->params['errorCode'][$code]);
    }
    
    public function success($data=[], $msg = '成功')
    {
        return array('status'=>200, 'msg'=>$msg, 'data'=> $data);
    }
}