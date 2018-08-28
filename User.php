<?php

namespace app\models;
use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $email
 * @property string $passw
 * @property string $datereg
 * @property integer $activation
 *
 * @property Accounts[] $accounts
 */
class User extends \yii\db\ActiveRecord
{
	public $account;
	public $building;
    public $flat;
	public $indicat;
	public $date;
	public $pssw;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account', 'email', 'passw', 'building', 'flat'], 'required'],
            [['datereg'], 'safe'],
            [['activation'], 'integer'],
            [['email'], 'string', 'max' => 50],
			['email', 'email'],
			['pssw','compare', 'compareAttribute'=>'passw','on'=>'insert',],
			['pssw','compare', 'compareAttribute'=>'passw', 'on'=>'update'],
            [['passw'], 'string', 'max' => 255]
			
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
			'account' => Yii::t('common','Account'),
            'email' => Yii::t('common','Email'),
            'passw' => Yii::t('common','Passw'),
			'pssw' => Yii::t('common','Pssw'),
			'datereg' => Yii::t('common','Datereg'),
            'building' => Yii::t('common', 'House'),
            'flat' => Yii::t('common', 'Flat'),
            'datereg' => Yii::t('common','Datereg'),
            'activation' => 'Актв',
			'indicat' => '',
			'date' => '',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccounts()
    {
        return $this->hasMany(Accounts::className(), ['user_id' => 'id']);
    }
	
	public function getAccountStr()
    {
		$accinfo=[];
        foreach($this->accounts as $item)
		{$accinfo[]=$item->department.'-'.$item->account;}
		return implode(',<br> ', $accinfo);
    }
	
	
	public static function findAccount($code, $building, $flat)
	{
		$curl = new Curl();
		
		$response = $curl->setGetParams([
			'code' => $code,
			'building' => $building,
			'flat' => $flat,
		 ])
		 ->post('http://some_site/api.php?method=registerCheck');
			$result=json_decode($response, true);
			return $result;
	}
	
	public static function getAccountInfo($department, $account)
	{
		$curl = new Curl();
		$department = mb_convert_encoding($department,"Windows-1251","UTF-8");
        $response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array(
                    'department' => $department,
					'account' => $account
					
                )
            ))
            ->post('http://some_site/api.php?method=getAccountInfo');
			
				$result=json_decode($response, true);
				return $result;
			
	}
	
	public static function getMeters($department, $account)
	{
		$curl = new Curl();
		$department = mb_convert_encoding($department,"Windows-1251","UTF-8");
        $response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array(
                    'department' => $department,
					'account' => $account
					
                )
            ))
            ->post('http://some_site/api.php?method=getMeters');
			
				$result=json_decode($response, true);
				if($result['result'] == true) : return $result['meters']; else: return $result; endif;
			
	}
	public static function getBuildingMeters($department, $account)
	{
		$curl = new Curl();
		$department = mb_convert_encoding($department,"Windows-1251","UTF-8");
        $response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array(
                    'department' => $department,
					'account' => $account
					
                )
            ))
            ->post('http://some_site/api.php?method=getBuildingMeters');
			
				$result=json_decode($response, true);
				return $result;
			
	}
	
	public static function getChargedPay($date, $department, $account)
	{
		$curl = new Curl();
		$department = mb_convert_encoding($department,"Windows-1251","UTF-8");
        $response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array(
                    'department' => $department,
					'account' => $account,
					'date' => $date
					
                )
            ))
            ->post('http://some_site/api.php?method=getCalcIndics');
			
				$result=json_decode($response, true);
				return $result;
			
	}
	
	public static function getMonthHistory($department, $account, $period)
    {
        //Init curl
        $curl = new Curl();
		$department = mb_convert_encoding($department,"Windows-1251","UTF-8");
        $response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array(
                    'department' => $department,
					'account' => $account,
					'period' =>$period
                )
            ))
            ->post('http://some_site/api.php?method=getMonthHistory');
			$result=json_decode($response, true);
			if($result['result'] == true) : return $result['history']; else: return $result; endif;
    }
	
	public static function getAllHistory($department, $account)
    {
        //Init curl
        $curl = new Curl();
		$department = mb_convert_encoding($department,"Windows-1251","UTF-8");
        $response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array(
                    'department' => $department,
					'account' => $account
                )
            ))
            ->post('http://some_site/api.php?method=getHistory');
			$result=json_decode($response, true);
			return $result;
    }
	
	public static function getPaymenLink($department, $account)
    {
        //Init curl
        $curl = new Curl();
		$department = mb_convert_encoding($department,"Windows-1251","UTF-8");
        $response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array(
                    'department' => $department,
					'account' => $account
                )
            ))
            ->post('http://some_site/api.php?method=getPaymenLink');
			$result=json_decode($response, true);
			if($result['result'] == true) : return $result['link']; else: return $result; endif;
    }
	public static function getQueueIndics($department, $account)
    {
        //Init curl
        $curl = new Curl();
        $department = mb_convert_encoding($department,"Windows-1251","UTF-8");
		$account = mb_convert_encoding($account,"Windows-1251","UTF-8");
        $response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array(
                    'department' => $department,
					'account' => $account
                )
            ))
            ->post('http://some_site/api.php?method=getQueueIndics');
			$result=json_decode($response, true);
			if($result['result'] == true) : return $result['queue']; else: return $result; endif;
    }
	
	public static function enterIndication($department, $account, $meter, $date, $value)
    {
        //Init curl
        $curl = new Curl();
        $department = mb_convert_encoding($department,"Windows-1251","UTF-8");
		$account = mb_convert_encoding($account,"Windows-1251","UTF-8");
        $response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array(
                    'department' => $department,
					'account' => $account,
					'meter' => $meter,
					'date' => $date,
					'value' => $value
                )
            ))
            ->post('http://some_site/api.php?method=enterIndication');
			$result=json_decode($response, true);
			return $result;
    }
	
	public static function mailactivation ($email, $cod){
 
    $absoluteHomeUrl = Url::home(true); //http://ваш сайт
    $serverName = Yii::$app->request->serverName; //ваш сайт без http
    $url = $absoluteHomeUrl.'user/activation/'.$cod;
 
    $msg_html  = "<html><body style='font-family:Arial,sans-serif;'>";
    $msg_html .= "<p style='font-weight:bold;border-bottom:1px dotted #ccc;'>Здравствуйте! Спасибо за регистрацию на нашем сайте <a href='". $absoluteHomeUrl ."'>".Yii::t('common', 'Site title')."</a></p>\r\n";
    $msg_html .= "<p><strong>Вам осталось только подтвердить свой e-mail.</strong></p>\r\n";
    $msg_html .= "<h2><strong>Для этого перейдите по ссылке </strong><a href='". $url."'>$url</a></h2>\r\n";
    $msg_html .= "</body></html>";
 
    Yii::$app->mailer->compose()
        ->setFrom('no-reply@vodokanal-pvk.org.ua') //не надо указывать если указано в common\config\main-local.php
        ->setTo($email) // кому отправляем - реальный адрес куда придёт письмо формата asdf @asdf.com
        ->setSubject('Подтверждение регистрации.') // тема письма
        ->setHtmlBody($msg_html) // текст письма с HTML
        ->send();
	}
	
	public static function mailpasswrestore ($email, $cod){
 
    $absoluteHomeUrl = Url::home(true); //http://ваш сайт
    $serverName = Yii::$app->request->serverName; //ваш сайт без http
    $url = $absoluteHomeUrl.'user/update?id='.$cod;
 
    $msg = "Здравствуйте! Спасибо за регистрацию на нашем сайте $serverName!  Вам осталось только подтвердить свой e-mail.";
 
    $msg_html  = "<html><body style='font-family:Arial,sans-serif;'>";
    $msg_html .= "<p style='font-weight:bold;border-bottom:1px dotted #ccc;'>Здравствуйте! Это письмо было отправленно с сайта - <a href='". $absoluteHomeUrl ."'>".Yii::t('common', 'Site title')."</a> </p>\r\n";
    $msg_html .= "<p><strong>Для изменения пароля</strong></p>\r\n";
    $msg_html .= "<h2><strong>перейдите по ссылке </strong><a href='". $url."'>$url</a></h2>\r\n";
    $msg_html .= "</body></html>";
 
    Yii::$app->mailer->compose()
        ->setFrom('no-reply@vodokanal-pvk.org.ua') //не надо указывать если указано в common\config\main-local.php
        ->setTo($email) // кому отправляем - реальный адрес куда придёт письмо формата asdf @asdf.com
        ->setSubject('Подтверждение изменения пароля.') // тема письма
        ->setHtmlBody($msg_html) // текст письма с HTML
        ->send();
	}
	public static function getUsercount()
    {
        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM `user` WHERE activation = 1')->queryScalar();
		return $count;
    }
	public static function getAccountcount()
    {
        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM `accounts`, `user` WHERE `accounts`.user_id =`user`.id AND `user`.activation=1')->queryScalar();
		return $count;
    }
	
	public static function getLegalinfo($department, $account)
    {
		 $ch = curl_init();
		 $path = 'http://some_site_xml/?'.$department.'&o='.$account;
		curl_setopt($ch, CURLOPT_URL,$path);
		curl_setopt($ch, CURLOPT_FAILONERROR,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$retValue = curl_exec($ch); 
		$data= curl_getinfo ($ch);	
		curl_close($ch);	
		$xml = simplexml_load_string($retValue, "SimpleXMLElement");
		$p = xml_parser_create();
		xml_parse_into_struct($p, $retValue, $vals, $index);
		xml_parser_free($p);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		$session = Yii::$app->session;
		$valinds=[];		
		for($i=0; $i<count($vals); $i++){
			if($vals[$i]['tag']=='INDICATION' && $vals[$i]['type']== 'complete'):
				$valinds[$i]['dateind']=$vals[$i]['attributes']['DATE'];
				$valinds[$i]['operation']=$vals[$i]['attributes']['OPERATION'];
				$valinds[$i]['inds']=$vals[$i]['value'];
			endif;
		}
		$valinds = array_values($valinds);
		if(isset($array['card']['account']['meter']['@attributes'])):
			unset($array['card']['account']['meter']['indications']);
			$array['card']['account']['meter']['indication']=$valinds;
		endif;
		if(isset($array['card']['account']['meter'][0]['@attributes'])):
			switch(count($array['card']['account']['meter'])){
			case 2:
			if(count($array['card']['account']['meter'][0]) == count($array['card']['account']['meter'][1])):
				$countinds=count($array['card']['account']['meter'][0]['indications']['indication']);
				$arrchunk[0]=array_slice($valinds, 0, $countinds);
				$arrchunk[1]=array_slice($valinds, $countinds-1, $countinds);
				
				unset($array['card']['account']['meter'][0]['indications']);
				$array['card']['account']['meter'][0]['indication']=array_reverse($arrchunk[0]);
				unset($array['card']['account']['meter'][1]['indications']);
				$array['card']['account']['meter'][1]['indication']=array_reverse($arrchunk[1]);
				//$session->set('indicts', $arrchunk);
			endif;
			break;
			
			}
		endif;
		//$session->set('vals', $card);
		return $array;
	}
	
	public static function getLegalinds($link)
    {
        $ch = curl_init();
		 $path = 'http://some_site_xml/'.$link;
		curl_setopt($ch, CURLOPT_URL,$path);
		curl_setopt($ch, CURLOPT_FAILONERROR,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$retValue = curl_exec($ch); 
		$data= curl_getinfo ($ch);	
		curl_close($ch);	
		$xml = simplexml_load_string($retValue, "SimpleXMLElement", LIBXML_NOCDATA);
		
		$p = xml_parser_create();
		xml_parse_into_struct($p, $retValue, $vals, $index);
		xml_parser_free($p);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		return $array;
    }
	
	public static function getLegalinvoice($link)
    {
        $ch = curl_init();
		 $path = 'http://some_site_xml/'.$link;
		curl_setopt($ch, CURLOPT_URL,$path);
		curl_setopt($ch, CURLOPT_FAILONERROR,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$retValue = curl_exec($ch); 
		$data= curl_getinfo ($ch);	
		curl_close($ch);	
		$xml = simplexml_load_string($retValue, "SimpleXMLElement", LIBXML_NOCDATA);
		
		$p = xml_parser_create();
		xml_parse_into_struct($p, $retValue, $vals, $index);
		xml_parser_free($p);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		$invoices=[];
		for($i=0; $i<count($vals); $i++){
			if($vals[$i]['tag']=='DOCUMENT'):
				$invoices['invoice'][$i]['docnum']=$vals[$i]['attributes']['NUMBER'];
				$invoices['invoice'][$i]['docperiod']=$vals[$i]['attributes']['PERIOD'];
			endif;
		}
		if (isset($invoices['invoice'])):
			$invoice=array_values($invoices['invoice']);
			$invoices['invoice']=$invoice; 
		endif;
		return $invoices;
    }
}
