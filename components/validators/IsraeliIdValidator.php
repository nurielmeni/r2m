<?php
namespace app\components\validators;

use yii\validators\Validator;

class IsraeliIdValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        //Convert to string, in case numeric input
        $IDnum = strval($model->$attribute);
        if (empty($IDnum)) return;

        //validate correct input 
        if(! ctype_digit($IDnum) || (strlen($IDnum)>9) || (strlen($IDnum)<5)) {
            $this->addError($model, $attribute, 'נא להזין מספר זהות מלא בן 9 ספרות');
        }

        //If the input length less then 9 and bigger then 5 add leading 0 
        while(strlen($IDnum<9)) {
            $IDnum = '0'.$IDnum;
        }

        $mone = 0;
        //Validate the ID number
        for($i=0; $i<9; $i++) {
            $char = mb_substr($IDnum, $i, 1);
            $incNum = intval($char); 
            $incNum*=($i%2)+1;
            if($incNum > 9)
                $incNum-=9;
            $mone+= $incNum; 
        } 

        if($mone%10 !== 0) {
            $this->addError($model, $attribute, 'מספר הזהות שהוזן לא חוקי');
        }
    }
    
    public function clientValidateAttribute($model, $attribute, $view) {
        $message = json_encode('מספר זהות לא חוקי', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return '
            var id = String(value).trim();
            if (id.length === 0 ) return;
            if (id.length > 9 || id.length < 5 || isNaN(id)) messages.push(' . $message . ');
        ';     
    }
}