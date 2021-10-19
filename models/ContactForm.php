<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\components\validators\IsraeliIdValidator;
use kartik\mpdf\Pdf;
use yii\helpers\Url;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $jobTitle;
    public $comment;
    public $name;
    public $id;
    public $email;
    public $phone;
    public $cvfile;
    public $sid;
    public $jobcode;
    public $experiance = 'LEAD';
    public $education = 'LEAD';


    private $tmpFiles = [];

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['jobTitle', 'name', 'phone', 'email'], 'required'],
            [['jobTitle', 'name', 'email', 'phone', 'id', 'comment'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['comment'], 'string', 'max' => 256],
            // email has to be a valid email address
            ['email', 'email'],
            ['phone', 'match', 'pattern' => '/^0[0-9]{1,2}[-\s]{0,1}[0-9]{3}[-\s]{0,1}[0-9]{4}/i'],
            ['id', IsraeliIdValidator::class],
            // verifyCode needs to be entered correctly
            ['cvfile', 'file', 'extensions' => ['doc', 'docx', 'pdf', 'rtf']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'jobTitle' => 'בחירת תפקיד',
            'comment' => 'מידע נוסף (לא חובה)',
            'name' => 'שם מלא',
            'id' => 'ת"ז (לא חובה)',
            'email' => 'מייל',
            'phone' => 'טלפון',
            'cvfile' => 'צרף קובץ קורות חיים (לא חובה)',
            'experiance' => 'ניסיון',
            'education' => 'השכלה',
            'sid' => 'מזהה ספק',
            'jobcode' => 'קוד משרה',
        ];
    }

    public function jobTitles()
    {
        return [
            'בר' =>'בר',
            'מטבח' =>'מטבח',
            'בריסטה' =>'בריסטה',
            'מלצרות' =>'מלצרות',
            'אירוח' =>'אירוח',
            'בדליקטסן/בייקרי' =>'בדליקטסן/בייקרי',
            'מכירות HOME ופרחים' =>'מכירות HOME ופרחים',
            'שירות טלפוני' =>'שירות טלפוני',
            'מאפיה וקונדיטוריה' =>'מאפיה וקונדיטוריה',
            'מלונאות' =>'מלונאות',
            'אריזות משלוחים' =>'אריזות משלוחים',
            'עובדים כללים' =>'עובדים כללים',
            'אחר' =>'אחר',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return bool whether email sent successfully
     */
    public function contact($email, $content)
    {
        $subject = 'אתר משרות R2M - בקשה חדשה |' . ' SID: ' . $this->sid . ' JOBCODE: ' . $this->jobcode;
        Yii::info($subject, 'meni');
        if (!$this->cvfile || empty($this->cvfile)) {
            $this->generateCv($content);
        }
        $this->generateNcai();

        $message = Yii::$app->mailer->compose()
            ->setTo($email)
            ->setFrom([$this->email => $this->name])
            ->setSubject($subject)
            ->setHtmlBody($content)
            ->setTextBody(strip_tags($content));

        if (key_exists('bccMail', Yii::$app->params) && !empty(Yii::$app->params['bccMail'])) {
            $message->setBcc(Yii::$app->params['bccMail']);
        }

        foreach ($this->tmpFiles as $tmpFile) {
            $message->attach($tmpFile);
        }

        $res = $message->send();

        $this->removeTmpFiles();
        return true;
    }

    private function generateNcai()
    {
        $ncaiTemplate = file_get_contents(Url::to('@app/templates/ncaiTemplate.txt'));
        $tmpFile = 'uploads/NlsCvAnalysisInfo' . date('s', time()) . '.ncai';
        $notes = '';

        $notes .= $this->getAttributeLabel('name') . ': ' . $this->name . "\r\n";
        $notes .= $this->getAttributeLabel('id') . ': ' . $this->id . "\r\n";
        $notes .= $this->getAttributeLabel('email') . ': ' . $this->email . "\r\n";
        $notes .= $this->getAttributeLabel('phone') . ': ' . $this->phone . "\r\n";
        $notes .= $this->getAttributeLabel('jobTitle') . ': ' . $this->jobTitle . "\r\n";
        $notes .= $this->getAttributeLabel('comment') . ': ' . $this->comment . "\r\n";

        $ncaiTemplate = str_replace('##EMAIL##', $this->email, $ncaiTemplate);
        $ncaiTemplate = str_replace('##NAME##', $this->name, $ncaiTemplate);
        $ncaiTemplate = str_replace('##PHONE##', $this->phone, $ncaiTemplate);
        $ncaiTemplate = str_replace('##FEDERALID##', $this->id, $ncaiTemplate);
        $ncaiTemplate = str_replace('##NOTES##', $notes, $ncaiTemplate);
        $ncaiTemplate = str_replace('##SUPPLIERID##', $this->sid, $ncaiTemplate);


        if (file_put_contents($tmpFile, $ncaiTemplate)) {
            $this->tmpFiles[] = $tmpFile;
            return true;
        }
        return false;
    }

    private function removeTmpFiles()
    {
        foreach ($this->tmpFiles as $tmpFile) {
            unlink($tmpFile);
        }
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return bool whether email sent successfully
     */
    public function followUpMail($content)
    {
        $subject = 'אתר משרות r2n - בקשתך התקבלה';
        return Yii::$app->mailer->compose()
            ->setTo($this->email)
            ->setFrom([Yii::$app->params['cvWebMail'] => Yii::$app->params['fromName']])
            ->setSubject($subject)
            ->setHtmlBody($content)
            ->setTextBody(strip_tags($content))
            ->send();
    }

    public function generateCv($content)
    {
        // setup kartik\mpdf\Pdf component
        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_UTF8,
            // A4 paper format
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_FILE,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting 
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
            // set mPDF properties on the fly
            'options' => ['title' => 'R2M - קובץ קורות חיים אוטומטי'],
            // call mPDF methods on the fly
            'methods' => [
                'SetHeader' => ['R2M - קורות חיים למועמד'],
                'SetFooter' => ['{PAGENO}'],
            ]
        ]);

        $tmpfile = Yii::getAlias('@webroot') . '/uploads/' . $this->sanitizeFileName('auto_generated_' . date('s', time()) , 'pdf');
        $pdf->output($content, $tmpfile, Pdf::DEST_FILE);
        $this->tmpFiles[] = $tmpfile;
        return true;
    }

    private function sanitizeFileName($file, $ext = null)
    {
        // Remove anything which isn't a word, whitespace, number
        // or any of the following caracters -_~,;[]().
        // If you don't need to handle multi-byte characters
        // you can use preg_replace rather than mb_ereg_replace
        // Thanks @Łukasz Rysiak!
        $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
        // Remove any runs of periods (thanks falstro!)
        $file = mb_ereg_replace("([\.]{2,})", '', $file);
        return $ext ? ($file . '.' . $ext) : $file;
    }

    public function upload()
    {
        $tmpFile = 'uploads/' . $this->sanitizeFileName($this->cvfile->baseName, $this->cvfile->extension);
        if ($this->cvfile->saveAs($tmpFile)) {
            $this->tmpFiles[] = $tmpFile;
        }
        return true;
    }

    public function beforeValidate()
    {
        parent::beforeValidate();
        return true;
    }
}
