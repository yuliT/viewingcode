<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ButtonDropdown;

/* @var $this yii\web\View */
/* @var $model app\models\Users */

$session = Yii::$app->session;
if($session->has('account')): $account = $session['account']; endif;
if($session->has('legalacc')): return $this->render('viewlegal', ['model' => $model]); endif;
$link = $session['link'];
?>
    <h1><?= Yii::t('common', 'Privat') ?></h1>
	
<div class="site-content">
<?= Html::a('<span class="glyphicon glyphicon-dashboard"></span> '. Yii::t('common', 'Indications send'), ['/user/indication'], ['class'=>'btn btn-primary']) ?> 
 <?= Html::a('<span class="glyphicon glyphicon-tasks"></span> '. Yii::t('common', 'params'), ['/user/account'], ['class'=>'btn btn-success']) ?> 
 <?= is_array($link) ? $link['message']: Html::a('<span class="glyphicon glyphicon-usd"></span> '. Yii::t('common', 'Payment'), $link, ['class'=>'btn btn-danger', 'target'=>'_blank']) ?>
 <?= count($model->accounts)>1 ?  Html::a('<span class="glyphicon glyphicon-th-list"></span> '. Yii::t('common', 'Other accounts').' ('.count($model->accounts).')', ['/user/select'], ['class'=>'btn btn-warning', 'data' => ['confirm' => Yii::t('common', 'Are you sure?')]]) : ''?>
 <?= Html::a('<span class="glyphicon glyphicon-plus"></span> '. Yii::t('common', 'Add acount'), ['/user/addaccount'], ['class'=>'btn btn-success']) ?> 
 <?= Html::a('<span class="glyphicon glyphicon-saved"></span> '. Yii::t('common', 'Update password'), ['/user/update', 'id' => $model->id], ['class'=>'btn btn-info']) ?> 
	
	<br><table class="table table-striped table-bordered" style="margin-top:10px;">
		<tbody>
			<tr><th><?=\Yii::t('common', 'Account')?></th>
			<th><?=\Yii::t('common', 'Email')?></th>
			<th><?=\Yii::t('common', 'Full name')?></th>
			<th><?=\Yii::t('common', 'Adress')?></th>
			</tr>			
			<tr>
			
			<td><?= $session['depnum'] ?>-<?= $session['accnum']?></td>
			<td><?= $model->email ?></td>
			<td><?= $account['full_name'] ?></td>
			<td><?= $account['address'] ?></td></tr>
		</tbody>
	</table>
  <?php 
 
  if($session->has('allhistory')) : $allhistory = array_reverse($session['allhistory']); 
  else : $allhistory='not here'; endif;

$colspan=0; //$count=count($account['services']);

$allhis=[];
	for ($year=date("Y"); $year>2000; $year--)
		{
			for ($i=0; $i<count($allhistory); $i++)
			{
				$period=explode('-', $allhistory[$i]['period']);
				if($year == $period[0])
				{
					$allhis[$year][]=$allhistory[$i];
				}
			}
			
		}
foreach($allhis as $key =>$value){	
?>
<div class="panel-group">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<?= '<a data-toggle="collapse" href="#collapse'.$key.'">'.$key.'</a>' ?>
			</h4>
			</div>
	<?= '<div id="collapse'.$key.'" class="panel-collapse collapse">' ?>
		<div class="panel-body">
			<table cellpadding="4" cellspacing="0" width="100%" class="table table-striped table-bordered">
				<tr>
					<th><?= \Yii::t('common', 'period') ?></th>
					<!--th><?= \Yii::t('common', 'roomers') ?></th-->
					<th><?= \Yii::t('common', 'priveledges') ?></th>
					<th><?= \Yii::t('common', 'saldo_in') ?></th>
					<th><?= \Yii::t('common', 'service') ?></th>
					<th><?= \Yii::t('common', 'tarif') ?></th>
					<th><?= \Yii::t('common', 'charged') ?></th>
					<th><?= \Yii::t('common', 'added') ?></th>
					<th><?= \Yii::t('common', 'removed') ?></th>
					<th><?= \Yii::t('common', 'subsidy') ?></th>
					<th><?= \Yii::t('common', 'payment') ?></th>
					<th><?= \Yii::t('common', 'saldo_out') ?></th>
					<!--th><?= \Yii::t('common', 'meter') ?></th-->
				</tr>
				<?php for ($i=0; $i<count($value); $i++)
			{ $d1 = strtotime($value[$i]['period']);
		!empty($value[$i]['services']) ? $colspan=count($value[$i]['services'])+1 : $colspan=count($account['services'])+1;
			?>
				<tr>
					<td rowspan="<?= $colspan ?>" valign='top'><b><?= Html::a(date("m.Y", $d1),['user/period', 'period' => $value[$i]['period']], ['title' => \Yii::t('common', 'history').' ' . date("m.Y", $d1)] )?></b></td>
					<!--td rowspan="<?= $colspan ?>" valign='top' align="right"><?= $account['params']['roomers_count'] ?></td-->
					<td rowspan="<?= $colspan ?>" valign='top'></td>
					<td rowspan="<?= $colspan ?>" valign='top' align="right"><b><?= $value[$i]['saldo_in']?></b></td>
					<td colspan="2"><font color='darkgray'>Всего:</font></td>
					<td align="right"><?php if ($value[$i]['added']!=null): ?><?= $value[$i]['charged']?> + <?= $value[$i]['added']?> = <b><?= $value[$i]['added']+$value[$i]['charged']?></b>
					<?php endif;?>
					<?php if ($value[$i]['removed']!=null): ?><?= $value[$i]['charged']?> <?= $value[$i]['removed']?> = <b><?= $value[$i]['charged']+$value[$i]['removed']?>
					<?php endif;?></b>
					<?php if ($value[$i]['removed']==null && $value[$i]['added']==null): ?><?= $value[$i]['charged']?>
					<?php endif;?></b>
					</td>
					<td align="right" rowspan="<?= $colspan ?>"><b><?= $value[$i]['added'];?></b></td>
					<td align="right" rowspan="<?= $colspan ?>"><b><?= $value[$i]['removed'];?></b></td>
					<td align="right"rowspan="<?= $colspan ?>"><?= $value[$i]['subsidy']?></td>
					<td align="right" rowspan="<?= $colspan ?>"><?= $value[$i]['payment']?></td>
					<td rowspan="<?= $colspan ?>" valign='top' align="right"><b><?= $value[$i]['saldo_out']?></b></td>
				</tr>
				<?php if(!empty($value[$i]['services'])) : $count=count($value[$i]['services']);
				else: $count=count($account['services']); endif;
					for ($j=0; $j<$count; $j++) {?>
				<tr>
					<td><?= empty($value[$i]['services']) ? $account['services'][$j]['title'] : 
					$value[$i]['services'][$j]['service_title']?></td>
					<td align="right"><?= empty($value[$i]['services']) ? '' : $value[$i]['services'][$j]['params']['unit_price'] ?></td>
					<td align="right"><?= empty($value[$i]['services']) ? $value[$i]['charged'] : $value[$i]['services'][$j]['charged_amount'];?></td>
				</tr>
			<?php } }	?>
			</table>  
			</div>
		</div>
	</div>
</div>
<?php }?>
</div>