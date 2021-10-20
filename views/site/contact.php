<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use nurielmeni\sumoSelect\SumoSelectWidget;
use nurielmeni\multiselect\MultiSelectWidget;

$this->title = 'R2M - הגשת מועמדות';
?>
<div class="site-contact">

    <?php if (Yii::$app->session->hasFlash('contactFormSubmitted')) : ?>
        <div class="row-fluid">
            <div class="alert alert-success r2m-title col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
                <h1>תודה על פנייתך,</h1>
                <h1>ניצור קשר בהקדם.</h1>
            </div>
        </div>

        <div class="row-fluid">
            <h1 class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 line r2m-title" style="line-height: 34px;">
                משאבי אנוש R2M
                <?= Html::a('חזור', './', ['class' => 'btn btn-md btn-default pull-left']) ?>
            </h1>
        </div>
    <?php elseif (Yii::$app->session->hasFlash('contactFormSubmitteError')) : ?>
        <div class="row-fluid">
            <div class="alert alert-success r2m-title col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
                <h1>התרחשה שגיאה בשליחת קורות החיים</h1>
            </div>
        </div>

        <div class="row-fluid">
            <h1 class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 r2m-title" style="line-height: 34px;">
                R2M מחלקת הגיוס
                <?= Html::a('חזור', './', ['class' => 'btn btn-lg btn-default pull-left']) ?>
            </h1>
        </div>
    <?php else : ?>
        <div class="row-fluid text-center logo">
            <?= Html::img('@web/images/R2M.jpg', ['alt' => 'R2M Logo']) ?>
        </div>

        <?= $this->render('_customers') ?>

        <div class="row-fluid">
            <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 elbit-title">

                <?php $form = ActiveForm::begin(['id' => 'contact-form', 'options' => ['enctype' => 'multipart/form-data']]); ?>

                <div class="row">
                    <div class="col-xs-12 col-sm-6 inset-label">
                        <?= $form->field($model, 'jobTitle')->widget(MultiSelectWidget::class,[
                            'options' => $model->jobTitles(),
                            'floating' => true,
                            'label' => "",
                            'maxSelectOptions' => 3,
                        ]) ?>
                    </div>
                    <div class="col-xs-12 col-sm-6 inset-label">
                        <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-6 inset-label">
                        <?= $form->field($model, 'phone') ?>
                    </div>
                    <div class="col-xs-12 col-sm-6 inset-label">
                        <?= $form->field($model, 'email') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-6 inset-label">
                        <?= $form->field($model, 'id') ?>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <?= $form->field($model, 'cvfile')->fileInput() ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 inset-label">
                        <?= $form->field($model, 'comment')->textarea(['rows' => 2]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <?= Html::submitButton('שלח', ['class' => 'btn btn-primary col-xs-12', 'name' => 'contact-button']) ?>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>

    <?php endif; ?>
</div>