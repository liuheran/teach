<?php
namespace document\controllers;

use Yii;

use yii\web\Controller;

/**
 * Site controller
 */
class ActivityController extends Controller
{

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionAdd()
    {
        return $this->render('add');
    }
    
    public function actionDelete()
    {
        return $this->render('delete');
    }
    
    public function actionList()
    {
        return $this->render('list');
    }
}
