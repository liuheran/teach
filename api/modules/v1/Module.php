<?php

namespace api\modules\v1;

/**
 * v1 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'api\modules\v1\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        \Yii::$app->user->enableSession = false; //关闭session 不是必须， 但是针对无状态的 RESTful APIs 还是建议要有
        \Yii::$app->user->loginUrl = null;
        // custom initialization code goes here
    }
}
