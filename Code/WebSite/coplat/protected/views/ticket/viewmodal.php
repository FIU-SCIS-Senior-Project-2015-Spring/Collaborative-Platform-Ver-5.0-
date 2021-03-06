<?php
/* @var $this UserController */
/* @var $model Ticket */

if(User::isCurrentUserAdmin())
{
	$this->breadcrumbs=array(
			'Manage Tickets'=>array('admin'),
			$model->id,
	);
}

Yii::app()->clientScript->registerScript('modal', "
$('.details-button').click(function(){
	$('.details-form').toggle();
	return false;
});

$('.comments-button').click(function(){
	$('.comments-form').toggle();
	return false;
});
");
?>

<!-- DETAILS END -->
<div class='well details-form' style="display:none">
<h3><?php echo CHtml::link('Ticket Number ' . $model->id,'#',array('class'=>'details-button')); ?></h3>
</div>

<div class='well details-form' style="display:">
<h3><?php echo CHtml::link('Ticket Number ' . $model->id,'#',array('class'=>'details-button')); ?></h3>
<hr>
<?php $this->widget('bootstrap.widgets.TbDetailView', array(
		'data'=>$model,
		'attributes'=>array(
			array(
					'label'=>'Creator',
					'type'=>'raw',
					'value'=>CHtml::encode($model->creatorUser->fname) .' '. CHtml::encode($model->creatorUser->lname),
			),
			array(
						'label'=>'Domain',
						'type'=>'raw',
						'value'=>CHtml::encode($model->domain->name),
				),
			array(
						'label'=>'Sub-Domain',
						'type'=>'raw',
						'value'=> CHtml::encode($model->getSubDomainID()),
				),
				array(
						'label'=>'Status',
						'type'=>'raw',
						'value'=> CHtml::encode($model->status),
				),
				array(
						'label'=>'Date Created',
						'type'=>'raw',
						'value'=> CHtml::encode(date("M d, Y g:i a", strtotime($model->created_date))),
				),
				array(
						'label'=>'Description',
						'type'=>'raw',
						'value'=> CHtml::encode($model->description),
				),
				array(
						'label'=>'Assigned To',
						'type'=>'raw',
						'value'=> CHtml::encode($model->getCompiledAssignedID()),
				),
				array(
						'label'=>'Priority',
						'type'=>'raw',
						'value'=> CHtml::encode($model->priority->description),
				),
				array(
						'label'=>'Attachment',
						'type'=>'raw',
						'value'=> CHtml::encode($model->file),
				),
		),
)); 
?>
</div>
<!-- DETAILS START -->

<!-- COMMENTS START -->
<div class='well comments-form' style="display:none">
<h3><?php echo CHtml::link('Comments','#',array('class'=>'comments-button')); ?></h3>
</div>

<div class='well comments-form' style="display:">
<h3><?php echo CHtml::link('Comments','#',array('class'=>'comments-button')); ?></h3>
<hr>
<?php 				
                                        
                    $this->widget('bootstrap.widgets.TbGridView', array(
                    		'type'=>'striped condensed hover',
                    		'id'=>'id',
							'dataProvider'=>  new CArrayDataProvider($model->comments, array('keyField'=>'id')),
                    		'summaryText'=>'',
                    		//'filter'=>$model,
                    		'columns'=>array(
                    				array(
                    					'header'=>'Date Added',
                    					'value'=>'$data->added_date',
                    				),array(
                    					'header'=>'Description',
                    					'value'=>'$data->description',
                    				),array(
                    					'header'=>'Name',
                    					'value'=>'$data->user_added',
                    				),
                    		),
                    ));
?>
</div>
<!-- COMMENTS END -->