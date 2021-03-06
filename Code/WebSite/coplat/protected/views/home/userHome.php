<?php
if(User::isCurrentUserAdmin())
{
    echo "<script> window.location ='adminHome' </script>";

} else
{
/**
 * Created by PhpStorm.
 * User: lorenzo_mac
 * Date: 4/9/14
 * Time: 2:08 PM
 */
	
	$button = addslashes(json_encode($button));
	
	
Yii::app()->clientScript->registerScript('register', "
	
		window.button = JSON.parse('" . $button . "');
	
		function setButtons(){
			if(window.button === 0) $('#proposalButton').addClass('hidden disabled');
		}
	
		setButtons();
");
?>

<div><h2><?php echo $user->fname; ?> <?php echo $user->lname; ?> Dashboard</h2></div>
    <?php
    if (User::isCurrentUserAway())
    {
    echo "You are away<br>";
        echo CHtml::button('I\'m Back!!', array('submit' => array('/awayMentor/remove/'.User::getCurrentUserId())));

    } ?>
<br>
    <table style="width:auto;">
        <tr>
            <th>Project Mentor</th>
            <th>Domain Mentor</th>
            <th>Personal Mentor</th>
            <th>Mentee</th>

        </tr>
        <tr>
            <?php
            $gray1 = 'style="opacity: 0.4;filter: alpha(opacity=40);" ';
            $gray2 = 'style="opacity: 0.4;filter: alpha(opacity=40);" ';
            $gray3 = 'style="opacity: 0.4;filter: alpha(opacity=40);" ';
            $gray4 = 'style="opacity: 0.4;filter: alpha(opacity=40);" ';

            $linkpjm = '';
            $linkdmm = '';
            $linkperm = '';
            $linkmen = '';

            if ($user->isProMentor())
            {
                $gray1 = '';
                $linkpjm='href="/coplat/index.php/projectMeeting/pMentorViewMeetings"';
            }
            if($user->isDomMentor())
            {
                $gray2 = '';
                $linkdmm ='href="/coplat/index.php/projectMeeting/domainMentorViewMeetings"';

            }
            if($user->isPerMentor())
            {
                $gray3 = '';
                $linkperm = 'href="/coplat/index.php/projectMeeting/personalMentorViewMeetings"';

            }
            if($user->isMentee())
            {
                $gray4 = '';
                $linkmen ='href="/coplat/index.php/projectMeeting/pMenteeViewMeetings"';

            }


            ?>

            <td style="padding:20px;"><a <?php echo $linkpjm; ?>><img  <?php echo $gray1 ?> border="0" src="/coplat/images/roles/project.png" id="pjm" width="150" height="150"></a></td>
            <td style="padding:20px;"><a <?php echo $linkdmm; ?>><img <?php echo $gray2 ?> border="0" src="/coplat/images/roles/domain.png" id="dmm" width="150" height="150"></a></td>
            <td style="padding:20px;"><a <?php echo $linkperm; ?>><img <?php echo $gray3 ?>  border="0" src="/coplat/images/roles/personal.png" id="pm" width="150" height="150"></a></td>
            <td style="padding:20px;"><a <?php echo $linkmen; ?>><img <?php echo $gray4 ?>  border="0" src="/coplat/images/roles/mentee.png" id="men" width="150" height="150"></a></td>

        </tr>
    </table>
<div><h3>My Questions</h3></div>
<br/>
<a id="proposalButton" style="text-decoration:none" href="/coplat/index.php/application/approve">
				<?php $this->widget('bootstrap.widgets.TbButton', array(
	                'buttonType'=>'button',
	                'type'=>'primary',
					'size'=>'large',
	                'label'=>'New Proposal!',
	            )); ?>
            </a>
<!-- <div style="margin-top = 0px; height: 300px; width: 1000px; overflow-y: scroll; border-radius: 5px;"> -->
<div id = "fullcontent"t>
    <?php $model1= Ticket::model();
    $MyTabs = array(
    'tab1'=>array('title'=>"Open",
    'content'=>$this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'My_questions',
        'dataProvider'=>$model1->searchMyQuestions(User::getCurrentUserId()),
                'columns'=>array(
            array('name'=>'subject','value'=>'$data->subject', 'htmlOptions'=>array('width'=>'120px')),
            //array('name'=>'created_date','value'=>'$data->getCreatedDateToString()'),
          //  array('name'=>'Assigned To','value'=>'$data->getCompiledAssignedID()'),
            array('name'=>'Last Activity','value'=>'$data->getLatestActivityDate()', 'htmlOptions'=>array('width'=>'40px')),
            //array('name'=>'Done By','value'=>'$data->getAssignedDateToString()'),
            array(
                'class'=>'CButtonColumn',
                'template'=>'{view}',
                'buttons'=>array(
                    'view'=>array(
                        'url'=>'Yii::app()->createUrl("ticket/view", array("id"=>$data->id))',)
                ),
            ),
        ),
    ), true)),
        'tab2'=>array('title'=>"Closed",
            'content'=>$this->widget('zii.widgets.grid.CGridView', array(
                'id'=>'My_Closed_questions',
                'dataProvider'=>$model1->searchMyClosedQuestions(User::getCurrentUserId()),
                'columns'=>array(
                    array('name'=>'subject','value'=>'$data->subject', 'htmlOptions'=>array('width'=>'120px')),
                    //array('name'=>'created_date','value'=>'$data->getCreatedDateToString()'),
                    //  array('name'=>'Assigned To','value'=>'$data->getCompiledAssignedID()'),
                    array('name'=>'Last Activity','value'=>'$data->getLatestActivityDate()', 'htmlOptions'=>array('width'=>'40px')),
                    //array('name'=>'Done By','value'=>'$data->getAssignedDateToString()'),
                    array(
                        'class'=>'CButtonColumn',
                        'template'=>'{view}',
                        'buttons'=>array(
                            'view'=>array(
                                'url'=>'Yii::app()->createUrl("ticket/view", array("id"=>$data->id))',)
                        ),
                    ),
                ),
            ), true)));
    $this->widget('CTabView', array('tabs'=>$MyTabs, ));
     ?>
    <br/>
    <?php
    if (User::isCurrentUserDomMentor()) { ?>
        <h3>Assigned Questions</h3> <br/>
        <?php
        $model3 = Ticket::model();
        $AssignedTabs = array(
            'tab4'=>array('title'=>"Open",
            'content'=>$this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'Assigned_tickets',
            'dataProvider' => $model3->searchAssigned(User::getCurrentUserId()),
            'columns' => array(
                array('name' => 'created_date', 'value' => '$data->getCreatedDateToString()'),
                'subject',
                array('name' => 'Last Activity', 'value' => '$data->getLatestActivityDate()'),
                array('name'=>'Priority','value'=>'$data->getPriorityString()'),
               // array('name' => 'Created By', 'value' => '$data->getCompiledCreatorID()'),
                //array('name'=>'Assigned To','value'=>'$data->getCompiledAssignedID()'),
                // array('name'=>'assigned_date','value'=>'$data->getAssignedDateToString()'),
                // 'status',

                //array('name'=>'Done By','value'=>'$data->getAssignedDateToString()'),
                array(
                    'class' => 'CButtonColumn',
                    'template' => '{view}',
                    'buttons' => array(
                        'view' => array(
                            'url' => 'Yii::app()->createUrl("ticket/view", array("id"=>$data->id))',)
                    ),
                ),
            ),
        ), true)),
            'tab3'=>array('title'=>"Closed",
                'content'=>$this->widget('zii.widgets.grid.CGridView', array(
                    'id' => 'Assigned_Closed_tickets',
                    'dataProvider' => $model3->searchAssignedClosed(User::getCurrentUserId()),
                    'columns' => array(
                        array('name' => 'created_date', 'value' => '$data->getCreatedDateToString()'),
                        'subject',
                        array('name' => 'Last Activity', 'value' => '$data->getLatestActivityDate()'),
                        array('name'=>'Priority','value'=>'$data->getPriorityString()'),
                        // array('name' => 'Created By', 'value' => '$data->getCompiledCreatorID()'),
                        //array('name'=>'Assigned To','value'=>'$data->getCompiledAssignedID()'),
                        // array('name'=>'assigned_date','value'=>'$data->getAssignedDateToString()'),
                        // 'status',

                        //array('name'=>'Done By','value'=>'$data->getAssignedDateToString()'),
                        array(
                            'class' => 'CButtonColumn',
                            'template' => '{view}',
                            'buttons' => array(
                                'view' => array(
                                    'url' => 'Yii::app()->createUrl("ticket/view", array("id"=>$data->id))',)
                            ),
                        ),
                    ),
                ), true))
            );
        $this->widget('CTabView', array('tabs'=>$AssignedTabs, ));
    }
    ?>
     <br/>
    <h3>Upcoming Meetings</h3>
    <?php $model2= VideoConference::model();
    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'upcomingVc',
        'dataProvider'=>$model2->searchUpcoming(User::getCurrentUserId()),
        'columns'=>array(
            'subject',
            array('name'=>'scheduled_for','value'=>'$data->getDateToString()'),
            array(
                'class'=>'CButtonColumn',
                'template'=>'{view}',
                'buttons'=>array(
                    'view'=>array(
                        'url'=>'Yii::app()->createAbsoluteUrl("videoConference/join/".$data->id, array(), "https")',)
                    //CHtml::link('Join Now', $this->createAbsoluteUrl('videoConference/join/' . $vc->id ,array(),'https'), array('role' => "button", "class" => "btn btn-primary"));
                ),
            ),
        ),
    )); ?>

        <div class="span2" style="margin-left: 30px">
            <!-- Cancel Button -->
            <table>
                <tr>

                </tr>
                <tr>
                    <td>
                        <br>
                    </td>
                </tr>
            </table>


        </div>
    </div>
</div>
<!-- End FullContent -->

<script type="text/javascript">
    $('.triggerTicketClick').on('click', function () {
        window.location = "/coplat/index.php/ticket/view/" + $(this).attr('id');
    });


    //$('.table-fixed-header').fixedHeader();
</script>
<?php }?>