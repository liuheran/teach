<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use api\models\LoginForm;
use api\models\SignupForm;
/**
 * Site controller
 */
class SiteController extends Controller
{
	
    /**
     * @inheritdoc
     */
	public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
        		'class' => VerbFilter::className(),
        		'actions' => [
        				'login' => ['post'],
        				'logup' => ['post'],
        		],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
    	echo 1;exit;
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $param = Yii::$app->request->post();
        if (! isset($param['phoneNumber']) || ! is_numeric($param['phoneNumber']) || strlen($param['phoneNumber']) != 11) {
            return ['status'=>100001,'msg'=>Yii::$app->params['errorCode']['100001']];
        }
        if (! isset($param['passWord']) || strlen($param['passWord']) < 6) {
            return ['status'=>100002,'msg'=>Yii::$app->params['errorCode']['100002']];
        }
    	$model = new LoginForm();
    	if ($model->load($param,'') && $model->login()) {
    	    if($user = $model->token(Yii::$app->user->id)) {
                return array('status'=>200,'token'=>$user->access_token, 'uid'=>Yii::$app->user->id);
    	    }else {
    	    	return ['status'=>100204,'msg'=>Yii::$app->params['errorCode']['100204']];
    	    }
        } else {
        	return ['status'=>100203,'msg'=>Yii::$app->params['errorCode']['100203']];
        }
    }
    
    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $param = Yii::$app->request->post();
        if (! isset($param['phoneNumber']) || ! is_numeric($param['phoneNumber']) || strlen($param['phoneNumber']) != 11) {
            return ['status'=>100001,'msg'=>Yii::$app->params['errorCode']['100001']];
        }
        if (! isset($param['passWord']) || strlen($param['passWord']) < 6) {
            return ['status'=>100002,'msg'=>Yii::$app->params['errorCode']['100002']];
        }
        $model = new SignupForm();
        $loginForm = new LoginForm();
        if ($model->load(Yii::$app->request->post(), '')) {
            if($loginForm->load(Yii::$app->request->post(), '')) {
                if (! empty($loginForm->getUser())) {
                    return ['status'=>100003,'msg'=>Yii::$app->params['errorCode']['100003']];
                }
            }else {
                return ['status'=>100004,'msg'=>Yii::$app->params['errorCode']['100004']];
            }
            if ($user = $model->signup()) {
                    return array('status'=>200,'token'=>$user->access_token);
            }else{
                return ['status'=>100005,'msg'=>Yii::$app->params['errorCode']['100005']];
            }
        }else {
            return ['status'=>100006,'msg'=>Yii::$app->params['errorCode']['100006']];
        }
    
        return false;
    }
}
