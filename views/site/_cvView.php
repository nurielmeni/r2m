<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'R2M - הגשת מועמדות';
?>

<div dir="rtl">
    <h3>קובץ קורות חיים אוטומטי - R2M משרות</h3>
    <?php foreach ($model->attributes as $name => $value) : ?>
        <p><span style="font-weight: bold;"><?= $model->getAttributeLabel($name) ?>: </span> <?= $value ?></p>
    <?php endforeach; ?>
</div>