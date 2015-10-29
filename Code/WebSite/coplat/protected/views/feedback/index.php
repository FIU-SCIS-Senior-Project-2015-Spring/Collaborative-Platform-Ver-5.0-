<?php
/* @var $this FeedbackController */
/* @var $dataProvider CActiveDataProvider */
/* @var $model Feedback */

$this->breadcrumbs=array(
	'Feedbacks',
);

if(User::isCurrentUserAnAdmin())
{
	$this->menu=array(
	array('label'=>'Create Feedback', 'url'=>array('create')),
	array('label'=>'Manage Feedback', 'url'=>array('admin')),
	);
}
else {

	$this->menu = array(
		array('label' => 'Create Feedback', 'url' => array('create')),
		//array('label'=>'Manage Feedback', 'url'=>array('admin')),
	);
}
?>


<h1>Feedbacks</h1>

<?php
if(User::isCurrentUserAnAdmin())
{
	$this->widget('bootstrap.widgets.TbGridView', array(
		'id'=>'domain-grid',
		'dataProvider'=>new CArrayDataProvider($data),
		//'filter'=>$model,
		'columns'=>array(
			//'id',
			array(
				'name'=>'Name',
				'value'=>'User::model()->getUser($data->user_id)'
			),
			array(
				'name'=>'Subject',
				'value'=>'$data->subject'
			),
			array(
				'name'=>'Description',
				'value'=>'$data->description'
			),
			array(
				'header'=>'Options',
				'class'=>'bootstrap.widgets.TbButtonColumn',
				'template'=> '{view} {delete}',
				'buttons'=>array(
					'view'=>
						array(
							'url'=>'Yii::app()->createUrl("feedback/view", array("id"=>$data->id))',

						),
				),
			)
		,)
	));
}

else
{
	$this->widget('bootstrap.widgets.TbGridView', array(
		'id'=>'domain-grid',
		'dataProvider'=>new CArrayDataProvider($data),
		//'filter'=>$model,
		'columns'=>array(
			//'id',
			array(
				'name'=>'Name',
				'value'=>'User::model()->getUser($data->user_id)'
			),
			'subject',
			'description',
			array(
				'header'=>'Options',
				'class'=>'bootstrap.widgets.TbButtonColumn',
				'template'=> '{view}',
				'buttons'=>array(
					'view'=>
						array(
							'url'=>'Yii::app()->createUrl("feedback/view", array("id"=>$data->id))',

						),
				),
			)
		,)
	));
}
?>
