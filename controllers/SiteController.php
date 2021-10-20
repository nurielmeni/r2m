<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    public $defaultAction = 'contact';
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
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

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $request = Yii::$app->request;
        
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            //Yii::info('Sid: ' . $model->sid, 'meni');
            //Yii::info('Jobcode: ' . $model->jobcode, 'meni');
            
            $model->sid = $request->get('sid', Yii::$app->params['defaultSid']);
            $model->jobcode = $request->get('jobcode', Yii::$app->params['defaultJobcode']);
            
            //Yii::info('Sid(a): ' . $model->sid, 'meni');
            //Yii::info('Jobcode(a): ' . $model->jobcode, 'meni');
            
            $model->cvfile = UploadedFile::getInstance($model, 'cvfile');
            if ($model->cvfile) $model->upload();
            if ($model->contact(Yii::$app->params['cvWebMail'], $this->renderPartial('_cvView', ['model' => $model]))) {
                Yii::$app->session->setFlash('contactFormSubmitted');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('contactFormSubmitteError');
                return $this->refresh();
            }
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }
}
