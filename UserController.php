<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Legal;
use app\models\UserSearch;
use app\models\Accounts;
use app\models\AccountsSearch;
use app\models\Activation;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use linslin\yii2\curl\Curl;
use yii\db\Command;
use yii\data\SqlDataProvider;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public function behaviors()
    {
        return [
		'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['adminmode', 'indication', 'update', 'view', 'period', 'account', 'select', 'addaccount', 'delete', 'accdelete', 'notactive','userdelete', 'charged', 'updateuser', 'report','updateall', 'report', 'indslegal', 'histlegal', 'invoicelegal', 'addlegal'],
                        'allow' => true,
                        'roles' => ['@'],
					],
					[
						'actions' => ['create', 'activation', 'restore', 'update', 'legal'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /*
     * Все пользователи
     */
    public function actionAdminmode()
    {
		$session = Yii::$app->session;
		if($session['__id'] == 1 || $session['__id'] == 500):
		if($session->has('account') || $session->has('meters') || $session->has('allhistory') || $session->has('account') || $session->has('link') || $session->has('depnum') || $session->has('accnum')|| $session->has('legalacc')){
			$session->remove('account');
			$session->remove('meters');
			$session->remove('legalacc');
			$session->remove('allhistory');
			$session->remove('link');
			$session->remove('depnum');
			$session->remove('accnum');
		}
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		$dataProvider->pagination->pageSize = 10;

        return $this->render('adminmode', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
		else : return $this->redirect(['view', 'id' => $session['__id']]);  endif;
    }

    /*
     * Просмотр отдельного зарегистрированного пользователя
     */
    public function actionView($id, $accid='')
    {
		$session = Yii::$app->session;
		
		$model = $this->findModel($id);
		if($model->id == 500): return $this->redirect(['news/index']); endif;
		if($session->has('legalacc')): return $this->render('viewlegal', ['model' => $model]); endif;
		if($session->has('account')){
			return $this->render('view', ['model' => $model]);
		}
		else{
			if(empty($accid)){
					$accinfo= Accounts::findOne(['user_id' => $model->id]);
					$department = $accinfo->department;
					$account =  $accinfo->account;
				}
				else{
						$department = $model->accounts[$accid]->department;
						$account =  $model->accounts[$accid]->account;
					}
				$session->set('account', $model->getAccountInfo($department, $account));
				$meters = $model->getMeters($department, $account);
				$session->set('meters', $meters);
				$allhistoryArr = $model->getAllHistory($department, $account);
				if($allhistoryArr['result'] == false) : 
					$legalinfo=$model->getLegalinfo($department, $account);
					if(!isset($legalinfo['card']['@attributes']['id'])):
						$session->remove('account');
						$session->remove('meters');
					if(isset($legalinfo['error'])):$session->set('errormess', $legalinfo['error']); endif;
						$session->setFlash('error', Yii::t('common', 'Error acc'));
						return $this->render('error');
					else:
						$session->remove('account');
						$session->remove('meters');	
						$session->remove('error');						
						$session->set('legalacc', $legalinfo);
						return $this->render('viewlegal', ['model' => $model, 'legalacc' => $legalinfo]);
					endif;
				else : 
					$allhistory=$allhistoryArr['history'];
					for($i=0; $i<count($allhistory); $i++){
						$month = $model->getMonthHistory($department, $account, $allhistory[$i]['period']);
						$allhistory[$i]['services'] = $month['charged'];
					}
					$session->set('allhistory', $allhistory);
					$session->set('link', $model->getPaymenLink($department, $account));
					$session->set('depnum', $department);
					$session->set('accnum', $account);
					return $this->render('view', ['model' => $model]);
				endif;				
			}
    }
	
	/*
     * Активация email
     */
	public function actionActivation(){
		$code = Yii::$app->request->get('page');
		$code = Html::encode($code);
		$find=Activation::find()->where(['code'=>$code])->one();
		//ищем код подтверждения
		if($find){
			$user = $this->findModel($find->id);
			$user->activation = 1;
			$user->save(false);
			if ($user->save(false)) {
				$find->delete();
				Yii::$app->session->setFlash('codeconfirm', Yii::t('common', 'Code confirm'));
				return $this->redirect(['site/login']);	
			}		
		}
		Yii::$app->session->setFlash('fail', Yii::t('common', 'Account error'));
		return $this->redirect(['site/login']);
	} 
	
/***/
	public function actionRestore()
	{
		$session=Yii::$app->session;
		$user = new User;
		
		if ($user->load(Yii::$app->request->post())) {
		$model = User::findOne(['email'=>$user->email]);
		  if($model){
			  $model->mailpasswrestore($model->email, $model->id);
				Yii::$app->session->setFlash('Passw change', Yii::t('common', 'Password change'));
				return $this->refresh();	
				}
			}
			else{
				return $this->render('restore', ['model' => $user]);
			}
			return $this->redirect(['site/login']);	
		
	} 
	
	public function actionNotactive()
    {
		Yii::$app->user->logout();
		$session = Yii::$app->session;
		$session->destroy();
       return $this->render('activation', ['provider' =>'']);
    }
	
	/*
     * Регистрация пользователя
     */
    public function actionCreate()
    {
		$session=Yii::$app->session;
		$smail=$session['useremail'];
        $model = new User(['scenario' => 'insert']);
		$account = new Accounts();
		$activation= new Activation();
		if ($model->load(Yii::$app->request->post())) {
			
			$find=User::find()->where(['email'=>$model->email])->one();
			
			if ($find){
				Yii::$app->session->setFlash('mailerror', Yii::t('common', 'Email error'));
					return $this->refresh();		
			}
			
			else{
			$session->set('postarr',$model->load(Yii::$app->request->post('User')));
				$pass = md5($model->passw);
				$model->passw=$pass;
				$model->datereg = date('Y-m-d H:i:s');
				$code = $model->account;
				$code = mb_convert_encoding($code,"Windows-1251","UTF-8");
				$building = $model->building;
				$building = mb_convert_encoding($building,"Windows-1251","UTF-8");
				$flat = $model->flat;
				$flat = mb_convert_encoding($flat,"Windows-1251","UTF-8");
				$curl = new Curl();
				$response = $curl->setOption(
					CURLOPT_POSTFIELDS, 
					http_build_query(array('code' => $code,	'building' => $building, 'flat' => $flat,)))
				->post('http://some_site/api.php?method=registerCheck');
				$result=json_decode($response, true);
				$session->set('res', $result);
				if(!empty($result['variants']))
				{	$model->save(false);
					//$session->set('userid', $model->id);
					$account->id=null;
					$account->user_id = intval($model->id);
					$account->department = $result['variants'][0]['department'];
					$account->account = intval($result['variants'][0]['account']);	
					$account->save(false);
					$dater = (string) time();
					
					$mailcode= md5($model->email.time());
					$activation->id=intval($model->id);
					$activation->code = $mailcode;
					$activation->save(false);
					$model->mailactivation($model->email, $mailcode);
				}
				else {
					Yii::$app->session->setFlash('error', Yii::t('common', 'Account error'));
					return $this->refresh();	
				}	
				
				Yii::$app->session->setFlash('success', Yii::t('common', 'Mail confirm'));
				return $this->redirect(['site/login']);	
			}
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
	
	/*
     * Добавление счета к существующему счету
     */
	public function actionAddaccount()
    {
		$session = Yii::$app->session;
        $user = $this->findModel($session['__id']);
		$model = new Accounts();
		$test=Yii::$app->request->post();
		if (Yii::$app->request->post()) {
			$code = Yii::$app->request->post('account');
			$code = mb_convert_encoding($code,"Windows-1251","UTF-8");
			$building = Yii::$app->request->post('building');
			$building = mb_convert_encoding($building,"Windows-1251","UTF-8");
			$flat = Yii::$app->request->post('flat');
			$flat = mb_convert_encoding($flat,"Windows-1251","UTF-8");
			$curl = new Curl();
			$response = $curl->setOption(
                CURLOPT_POSTFIELDS, 
                http_build_query(array('code' => $code,	'building' => $building, 'flat' => $flat,)))
            ->post('http://some_site/api.php?method=registerCheck');
			$result=json_decode($response, true);
			$session->set('res', $result);
			if(!empty($result['variants']))
			{
				$model->id=null;
				$model->user_id = intval($user->id);
				$model->department = $result['variants'][0]['department'];
				$model->account = intval($result['variants'][0]['account']);		
			}
			else {
				Yii::$app->session->setFlash('error', Yii::t('common', 'Account error'));
				return $this->refresh();
			}	
			$model->save(false);
			return $this->render('select', ['model'=>$user]);
        } else {
            return $this->render('addaccount');
        }
		return $this->render('select', ['model'=>$user]);
    }
	
	/*
     * Обновление всей информации (недоступно)
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
		$model->scenario = 'update';

        if ($model->load(Yii::$app->request->post())) {
			$pass = md5($model->passw);
            $model->passw=$pass;
			$model->save(false);
           Yii::$app->user->isGuest ? $this->redirect(['site/login']) : $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

	/*
     * Просмотр и внесение показаний по счетчику
     */
    public function actionIndication()
    {
		$session = Yii::$app->session;
		$id=$session['__id'];
		$model = $this->findModel($id);
		//$date=date("Y-m-d");
		$indsinfo=[];
		if ($model->load(Yii::$app->request->post())){
			$indsinfo=Yii::$app->request->post('User');
			$datearr=Yii::$app->request->post('date');
			$date = date("Y-m-d", strtotime($datearr));
			if(date("Y-m") < date("Y-m", strtotime($date)) || date("Y-m") > date("Y-m", strtotime($date))):
				Yii::$app->session->setFlash('error date');
			else:
			for ($i=0; $i<count($indsinfo['indicat']); $i++)
			{
				if ($indsinfo['indicat'][$i] == null || $indsinfo['indicat']<0) : 
				Yii::$app->session->setFlash('error');
				else : 
				$answer = $model->enterIndication($session['depnum'], $session['accnum'], $indsinfo['number'][$i], $date, $indsinfo['indicat'][$i]);
				//$session->set('answerinds', $answer);
					Yii::$app->session->setFlash('success');
				endif;
			}
			endif;
		}
        return $this->render('indsend', [
            'model' => $model,
        ]);
    }
	
	/*
     * Удаление пользователя со счетом
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
		$accounts = Accounts::deleteAll(['user_id' => $id]);
		$model->delete();
        return $this->redirect(['userdelete']);
    }
	
    
	/*
     * Удаление счета
     */
	public function actionAccdelete($id)
    {
        Accounts::findOne($id)->delete();
        return $this->redirect(['select']);
    }
	/*
     * Активация пользователей
    */
	public function actionUpdateall()
    {
        Yii::$app->db->createCommand()
			->update('user', ['activation' => 1], 'activation=0')
			->execute();
		$session = Yii::$app->session;
		$id=$session['__id'];
		$model = $this->findModel($id);
		return $this->redirect(['adminmode']);
    }
	
	public function actionUpdateuser()
    {
		$model = User::findOne(4623);
		
		$model->activation=1;
		$model->save(false);
		return $this->redirect(['adminmode']);
    }
	 //Удаление пользователя
   
    public function actionUserdelete()
    {
      
			 $dataProvider = new ActiveDataProvider([
            'query' => User::find()->where(['activation' => 0]),
        ]);
		$count = Yii::$app->db->createCommand('
    SELECT COUNT(*) FROM `user` WHERE id NOT IN (SELECT user_id FROM accounts)')->queryScalar();
       $provider = new SqlDataProvider([
			'sql' => 'SELECT COUNT(*) AS rep, email FROM user GROUP BY email HAVING rep > 1 ',
			'totalCount' => $count,
			'pagination' => [
				'pageSize' => 100,
			]
		]);
		
		$model = $provider->getModels();
        return $this->render('nessesary', [
            'dataProvider' => $dataProvider,
			'provider' => $provider,
        ]);
    }	
	/*
	* Просмотр выбранного периода
	*/
	public function actionPeriod($period)
    {
		$session = Yii::$app->session;
		$id=$session['__id'];
		$model = $this->findModel($id);
		$history = $model->getMonthHistory($session['depnum'], $session['accnum'], $period);
		 return $this->render('hmeters', [
			'history' => $history,
			'model' => $model,
        ]);
	}
	/*
	* Расчет начисления
	*/
	public function actionCharged($date)
    {
		$session = Yii::$app->session;
		$id=$session['__id'];
		$changedpay = User::getChargedPay($date, $session['depnum'], $session['accnum']);
		 return $this->render('changedpay', [
			'changedpay' => $changedpay,
        ]);
	}
	/*
	* Просмотр выбранного зарегистрированного счета через api
	*/
	public function actionAccount()
    {
		$session = Yii::$app->session;
		$id=$session['__id'];
		$model = $this->findModel($id);
		 return $this->render('account', ['model' => $model]);
	}
	/*************/
	public function actionReport()
    {
		 Yii::$app->db->createCommand()
             ->update('accounts', ['account' => '01987402'], 'user_id= 4695')
             ->execute();
        return $this->redirect(['adminmode']);
    }
	/***********/
	/*
	* Просмотр выбор счета из списка при количестве >1
	*/
	public function actionSelect()
    {
		$session = Yii::$app->session;
		$id=$session['__id'];
		$model= $this->findModel($id);
		if($session->has('account') || $session->has('meters') || $session->has('allhistory') || $session->has('account') || $session->has('link') || $session->has('depnum') || $session->has('accnum') || $session->has('legalacc')){
			$session->remove('account');
			$session->remove('meters');
			$session->remove('allhistory');
			$session->remove('link');
			$session->remove('depnum');
			$session->remove('accnum');
			$session->remove('legalacc');
		}
		$model = $this->findModel($id);
		 return $this->render('select', ['model' => $model]);
	}
	
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	/*
     * Регистрация пользователя
     */
    public function actionLegal()
    {
		$session=Yii::$app->session;
		$smail=$session['useremail'];
        $model = new Legal();
		$user = new User(['scenario' => 'insert']);
		$account = new Accounts();
		$activation= new Activation();
		if ($model->load(Yii::$app->request->post())) {
			
			$find=User::find()->where(['email'=>$model->mail])->one();
			
			if ($find){
				Yii::$app->session->setFlash('mailerror', Yii::t('common', 'Email error'));
					return $this->refresh();		
			}
			
			else{
				$user->email=$model->mail;
				$pass = md5($model->passw);
				$user->passw=$pass;
				$user->datereg = date('Y-m-d H:i:s');
				$user->save(false);
				$account->id=null;
				$account->user_id = intval($user->id);
				$account->department = $model->code;
				$account->account = $model->okpo;	
				$account->save(false);
				$dater = (string) time();
				
				$mailcode= md5($user->email.time());
				$activation->id=intval($user->id);
				$activation->code = $mailcode;
				$activation->save(false);
				$user->mailactivation($user->email, $mailcode);
				}
				Yii::$app->session->setFlash('success', Yii::t('common', 'Mail confirm'));
				return $this->redirect(['site/login']);	
				
			}
		else {
            return $this->render('legal', [
                'model' => $model,
            ]);
        }
    }
	
	public function actionIndslegal()
    {
		$session = Yii::$app->session;
		$session->remove('answer');
		$id=$session['__id'];
		$model = $this->findModel($id);
		$inds=[];
		$link='?'.$session['legalacc']['card']['@attributes']['id'].'&o='.$session['legalacc']['card']['@attributes']['okpo'];
		if (Yii::$app->request->post()){
			$inds=Yii::$app->request->post('inds');
			$datearr=Yii::$app->request->post('date');
			$date = date("Y-m-d", strtotime($datearr));
			$link.='&d='.$date;
			
			for($i=0; $i<count($inds); $i++){
				if(isset($inds[$i]['meter']['indication']) && $inds[$i]['meter']['indication'] !=null):
					$link.='&m['.$inds[$i]['meter']['account'].']['.$inds[$i]['meter']['meter'].']='.$inds[$i]['meter']['indication'];
					Yii::$app->session->setFlash('success lagal');
				else:
					for($z=0; $z<count($inds[$i]['meter']); $z++){
						if(isset($inds[$i]['meter'][$z]['indication']) && $inds[$i]['meter'][$z]['indication']!=null): 
							$link.='&m['.$inds[$i]['meter'][$z]['account'].']['.$inds[$i]['meter'][$z]['meter'].']='.$inds[$i]['meter'][$z]['indication'];
							Yii::$app->session->setFlash('success legal');
						else:Yii::$app->session->setFlash('error');
						endif;
					}
				endif;
				if(isset($inds[$i]['meter']['slave'])):
					for($k=0; $k<count($inds[$i]['meter']['slave']); $k++)
					{
						if($inds[$i]['meter']['slave'][$k]['meter']['indication']!=null):
							$link.='&m['.$inds[$i]['meter']['slave'][$k]['meter']['account'].']['.$inds[$i]['meter']['slave'][$k]['meter']['meter'].']='.$inds[$i]['meter']['slave'][$k]['meter']['indication'];
							Yii::$app->session->setFlash('success lagal');
						else:Yii::$app->session->setFlash('error');
						endif;
					}
			 endif;
			}
			$getxml=$model->getLegalinds($link);
				unset($getxml['card']);
				$session->set('answer', $getxml);
				if(isset($getxml['success'])):
					Yii::$app->session->setFlash('success lagal inds');
				return $this->render('viewlegal', ['model' => $model]);
				else:
					Yii::$app->session->setFlash('error');
				endif;
				
		}
        return $this->render('indslegal', [
            'model' => $model,
        ]);
		
    }
	
	public function actionHistlegal()
    {
		return $this->render('legalwork');
    }
	
	public function actionInvoicelegal()
    {
		$session = Yii::$app->session;
		$id=$session['__id'];
		$model = $this->findModel($id);
		$date = Yii::$app->request->post('date');
		$stringHash = '';
		if (!is_null($date)) {
			$period=  date("Y-m", strtotime($date));
			$string='flist?'.$session['legalacc']['card']['@attributes']['id'].'&o='.$session['legalacc']['card']['@attributes']['okpo'].'&p='.$period;
			$stringHash = $model->getLegalinvoice($string);
		}
        return $this->render('invoicelegal', [
            'model' => $model,
			'stringHash' => $stringHash,
        ]);
    }
	
	public function actionAddlegal()
    {
		$session = Yii::$app->session;
        $user = $this->findModel($session['__id']);
		$model = new Accounts();
		$test=Yii::$app->request->post();
		if (Yii::$app->request->post()) {
			$model->user_id=$user->id;
			$model->save(false);
			return $this->render('select', ['model'=>$user]);
        } else {
            return $this->render('addlegal', [
            'model' => $model,
        ]);
        }
		return $this->render('select', ['model'=>$user]);
    }
}
