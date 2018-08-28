<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\DatePicker;

$session = Yii::$app->session;

$newinds=$model->getQueueIndics($session['depnum'], $session['accnum']);
$meters = []; 
$unactiveall = [];
for($m=0; $m<count($session['meters']); $m++)
	{
		if($session['meters'][$m]['state'] === "Действующий"){
			$meters[]=array_reverse($session['meters'][$m]);
		}
		else {$unactiveall[] =array_reverse($session['meters'][$m]);}
	}

$unactive=$unactiveall;
$fullwid = '100%';
$smallwid = '55%';
$inds=[];
for($m=0; $m<count($meters); $m++)
	{
		$inds[]=array_reverse($meters[$m]['indications']);
	}
?>
    <h1><?= Html::encode(Yii::t('common','Indications send')) ?></h1>
<div class="site-content">
<?= Html::a('<span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> '. Yii::t('common', 'Go Back'), ['/user/view', 'id' =>$model->id], ['class'=>'btn btn-info']) ?>
 <br>	
	<table name="metersind" class="table table-striped table-bordered" style="width: <?= (count($meters) > 1) ? $fullwid: strval($smallwid)?>; margin-top:10px;">
		<tr>
			<td rowspan='2' align="center"><b><?= \Yii::t('common', 'period') ?></b></td>
			<td colspan="<?= count($meters) ?>" align="center"><b><p><?= \Yii::t('common', 'indications') ?></p></b></td>
 		</tr>
		<tr>
		<?php
			for ($i=0; $i<count($meters); $i++){
			$dm2 = strtotime($meters[$i]['next_check']['date']);?>
			<td style="font-size: 10pt;"><?= $meters[$i]['number'].' - '.$meters[$i]['placement'].' '.$meters[$i]['state'].' '.$meters[$i]['factory_number']?>
			<br> Дата след. проверки - <?= $meters[$i]['next_check']['message'].'<b>'.date("d.m.Y", $dm2)?></b></td>
			<?php } ?>
		</tr>
		<tr>
			<td colspan="<?= count($meters)+1 ?>" align="center" style="color:green"><?= \Yii::t('common', 'predloj') ?></td>
		</tr>
		 <?php $form = ActiveForm::begin(['id' => 'inds-form', 'enableClientValidation' => true,
    'options' => [
        'enableAjaxValidation' => true,
        'class' => 'form'
    ],]); ?>
		<tr>
			<td><?= DatePicker::widget([
											'name' => 'date',
											'type' => DatePicker::TYPE_COMPONENT_APPEND,
											'value' => date("d.m.Y"),
											'pluginOptions' => [
												'autoclose'=>true,
												'format' => 'dd.mm.yyyy'
											]
										]); ?></td>
			<?php for ($i=0; $i<count($meters); $i++){ ?>
				<td><?= $form->field($model, 'indicat[]')->textInput(['type' => 'number'])->label(false)?>
				<?= $form->field($model, 'number[]')->hiddenInput(['value'=> $meters[$i]['number']])->label(false)?>
				</td>
			<?php } ?>
		</tr>
		<tr>
            <td colspan="<?= count($meters)+1 ?>" align="center">
                <?= Html::submitButton(Yii::t('common','Send'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
            </td>
		</tr>
			<?php ActiveForm::end();  ?>
			
                <?php if (Yii::$app->session->hasFlash('success')): ?>
		<tr>
			<td colspan="<?= count($meters)+1 ?>" align="center">
				<div class="alert alert-success">
					<p><?= Html::encode(Yii::t('common', 'Indications thanks'))?></p>
			</td>
		</tr>
		</div><?php endif; ?>
				 <?php if (Yii::$app->session->hasFlash('error')): ?>
		<tr>
			<td colspan="<?= count($meters)+1 ?>" align="center">
				<div class="alert alert-danger">
					<p><?= Html::encode(Yii::t('common', 'Indicat empty'))?></p>
			</td>
		</tr>
				</div><?php endif;?>
				<?php if (Yii::$app->session->hasFlash('error date')): ?>
		<tr>
			<td colspan="<?= count($meters)+1 ?>" align="center">
				<div class="alert alert-danger">
					<p><?= Html::encode(Yii::t('common', 'Indicat date'))?></p>
			</td>
		</tr>
				</div><?php endif;?>
		<?php if(!empty($newinds)): ?><tr><td colspan="<?= count($meters)+1 ?>" align="center" style="background:#CD5C5C; border: 1px solid red; color: whitesmoke; font-weight: 600;">
			<?= Yii::t('common','New indications')?>
			</td>
			</tr>
			<?php for($i=0; $i<count($newinds); $i++) { ?>
			<tr>			
				<td align="center" style="border: 1px solid red;">		
					<?php $inddate=strtotime($newinds[$i]['date']); ?>
					<?= date("d.m.Y",$inddate) ?>&nbsp; 
				</td>
				<td align="center" style="border: 1px solid red;"><?=Yii::t('common', 'meter').' - '.$newinds[$i]['meter'].' - '?><?= $newinds[$i]['indication']?></td>
			</tr>
			<tr><td colspan="<?= count($meters)+1 ?>" align="center" ><?= Html::a('<span class="glyphicon glyphicon-ok"></span> '. Yii::t('common', 'Charged payment'), ['/user/charged', 'date'=>$newinds[$i]['date']], ['class'=>'btn btn-info']) ?> </td></tr>
		<?php } ?>
		
		<?php endif; ?>
			<?php
			switch (count($meters)) {
				
				case 1: for($i=0; $i<count($inds[0]); $i++) { $d1 = strtotime($inds[0][$i]['date']); ?>
				<tr><td><?= date("d.m.Y", $d1)?></td>
				<td><?= $inds[0][$i]['indication'].' '.$inds[0][$i]['flag'] ?></td></tr>
				<?php }break;
				 
				 case 2: 
					 for($i=0; $i<count($inds[0]); $i++) { $d1 = strtotime($inds[0][$i]['date']); ?>
					<tr><td><?= date("d.m.Y", $d1)?></td>
					<td><?= $inds[0][$i]['indication'].' '.$inds[0][$i]['flag'] ?></td>
					<td><?php if(isset($inds[1][$i]['indication'])) : echo $inds[1][$i]['indication'].' '.$inds[1][$i]['flag']?>
					<?php endif; ?></td></tr>
			<?php }  break;
				
				case 3:				
				for ($j=0; $j<count($inds[0]); $j++){ $d1 = strtotime($inds[0][$j]['date']);?>
				<tr><td><?= date("d.m.Y", $d1)?></td>
				<td><?= $inds[0][$j]['indication'].' '.$inds[0][$j]['flag'] ?></td>
				<td><?= $inds[1][$j]['indication'].' '.$inds[1][$j]['flag'] ?></td>
				<td><?= $inds[2][$j]['indication'].' '.$inds[2][$j]['flag'] ?></td>
				</tr>
			<?php }	break;
			
			case 4:
			for ($j=0; $j<count($inds[0]); $j++){ $d1 = strtotime($inds[0][$j]['date']);?>
				<tr><td><?= date("d.m.Y", $d1)?></td>
				<td><?= $inds[0][$j]['indication'].' '.$inds[0][$j]['flag'] ?></td>
				<td><?= $inds[1][$j]['indication'].' '.$inds[1][$j]['flag'] ?></td>
				<td><?= $inds[2][$j]['indication'].' '.$inds[2][$j]['flag'] ?></td>
				<td><?= $inds[3][$j]['indication'].' '.$inds[3][$j]['flag'] ?></td>
				</tr>
			<?php } break; }?>
    </table>
	<?php for ($j=0; $j<count($unactive); $j++){?>
	<div class="panel-group">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<?= '<a data-toggle="collapse" href="#collapse'.$unactive[$j]['number'].'">'.$unactive[$j]['number'].' - '.$unactive[$j]['placement'].' '.$unactive[$j]['state'].' '.$unactive[$j]['factory_number'].'</a>' ?>
			</h4>
			</div>
	<?= '<div id="collapse'.$unactive[$j]['number'].'" class="panel-collapse collapse">' ?>
			<div class="panel-body">
			<table cellpadding="4" cellspacing="0" width="100%" class="table table-striped table-bordered">
			<tr>
			<td align="center"><b><?= \Yii::t('common', 'period') ?></b></td>
			<td align="center"><b><p ><?= \Yii::t('common', 'indications') ?></p></b></td>
 		</tr>
			<?php for ($k=(count($unactive[$j]['indications'])-1); $k>0; $k--){$d1 = strtotime($unactive[$j]['indications'][$k]['date']);?>
				<tr><td><?= date("d.m.Y", $d1)?></td>
				<td><?= $unactive[$j]['indications'][$k]['indication'].' '.$unactive[$j]['indications'][$k]['flag'] ?></td></tr>
			<?php }?>
			</table>  
			</div>
		</div>
	</div>
</div>
<?php }?>
</div>
  