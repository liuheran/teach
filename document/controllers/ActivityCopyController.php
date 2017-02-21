<?php

namespace document\controllers;
use Yii;

use yii\web\Controller;

class ActivityCopyController extends Controller
{
    public function actionAdd()
    {
        echo 1;exit;
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
