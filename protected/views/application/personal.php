<?php
/* 
 * @var $this ApplicationController 
 * @var $model ApplicationPersonalMentor
 * @var $user User
 * @var $universities University[]
 * */

$students = addslashes(json_encode($students));

Yii::app()->clientScript->registerScript('register', "
		
	window.students = JSON.parse('" . $students . "');
	window.selectedStudents = [];
	window.studentsGridLastSort = 'name';
	window.selectedGridLastSort = 'name';
	
	var template = $('#rowtemplate').clone();
	
	function generateStudentsGrid(sortByField) {
		var grid = $('#mygrid');
		
		// get the last sort method
		if (!sortByField) {
			sortByField = window.studentsGridLastSort;
		}
		
		// save the current sort method
		window.studentsGridLastSort = sortByField;
			
		// clear the table
		grid.find('.items-body').children().remove();
			
		var studentsCopy = jQuery.extend(true, [], students);
		var sortedStudents = studentsCopy.sort(function(a, b) {
			return a[sortByField].localeCompare(b[sortByField]);
		});
		
		for (var i = 0; i < sortedStudents.length; i++) {
			var student = sortedStudents[i];	
			var row = template.clone();
			
			// fill in all the data here
			row.children('.student-id').text(student.id);
			row.children('.student-avatar').find('img').attr('src', student.avatar);
			row.children('.student-name').text(student.name);
			row.children('.student-university').text(student.university);
			
			row.click(function(){
				moveToSelectedGrid($(this));
			});
			
			row.popover({
				html: true,
				content: '<div class=\"row-fluid\" style=\"width: 500px\"> \
						<div class=\"span3\"><img src=\"' + student.avatar + '\"></div> \
						<div class=\"span8\"> \
							<strong style=\"font-size: 1.5em\">' + student.name + '</strong> \
							<br />' + student.university + '<br /><a href=\"mailto:' + student.email + '\">' + student.email + '</a> \
						</div> \
						</div> \
						<div class=\"row-fluid\" style=\"width: 500px\"> \
							<div class=\"span6\"> \
								<br /><strong style=\"font-size: 1.25em\">Project:</strong> \
								<br />' + student.project + ' \
							</div> \
							<div class=\"span6\"> \
								<br /><strong style=\"font-size: 1.25em\">Personal Mentor:</strong> \
								<br /><img src=\"' + student.mentor.avatar + '\" style=\"width:50px\">  ' + student.mentor.name + ' \
							</div> \
						</div>'
			});
			
			grid.find('.items-body').append(row);
		}
	}
			
	function generateSelectedGrid(sortByField) {
		var grid = $('#selectedgrid');
		
		// get the last sort method
		if (!sortByField) {
			sortByField = window.selectedGridLastSort;
		}
		
		// save the current sort method
		window.selectedGridLastSort = sortByField;
			
		// clear the table
		grid.find('.items-body').children().remove();
			
		var studentsCopy = jQuery.extend(true, [], selectedStudents);
		var sortedStudents = studentsCopy.sort(function(a, b) {
			return a[sortByField].localeCompare(b[sortByField]);
		});
		
		for (var i = 0; i < sortedStudents.length; i++) {
			var student = sortedStudents[i];	
			var row = template.clone();
			
			// fill in all the data here
			row.children('.student-id').text(student.id);
			row.children('.student-avatar').find('img').attr('src', student.avatar);
			row.children('.student-name').text(student.name);
			row.children('.student-university').text(student.university);
			
			row.click(function(){
				moveToStudentsGrid($(this));
			});
			
			row.popover({
				html: true,
				content: '<div class=\"row-fluid\" style=\"width: 500px\"> \
							<div class=\"span3\"><img src=\"' + student.avatar + '\"></div> \
							<div class=\"span8\"> \
								<strong style=\"font-size: 1.5em\">' + student.name + '</strong> \
								<br />' + student.university + '<br /><a href=\"mailto:' + student.email + '\">' + student.email + '</a><br />' + student.project + ' \
							</div> \
						</div> \
						<div class=\"row-fluid\" style=\"width: 500px\"> \
							<div class=\"span6\"> \
								<br /><strong style=\"font-size: 1.25em\">Project:</strong> \
								<br />' + student.project + ' \
								<br />' + student.description + ' \
							</div> \
							<div class=\"span6\"> \
								<br /><strong style=\"font-size: 1.25em\">Personal Mentor:</strong> \
								<br /><img src=\"' + student.mentor.avatar + '\" style=\"width:50px\">  ' + student.mentor.name + ' \
							</div> \
						</div>'
			});
			
			grid.find('.items-body').append(row);
		}
	}
			
	generateStudentsGrid();
	generateSelectedGrid();

	$('#mygrid').find('.items-header .student-name').click(function() {
		generateStudentsGrid('name');
	});

	$('#mygrid').find('.items-header .student-university').click(function() {
		generateStudentsGrid('university');
	});

	$('#selectedgrid').find('.items-header .student-name').click(function() {
		generateSelectedGrid('name');
	});
			
	$('#selectedgrid').find('.items-header .student-university').click(function() {
		generateSelectedGrid('university');
	});
			
	function moveToStudentsGrid(obj) {
		// clear the current click handler
		obj.off('click');
		
		// move the DOM object over to the other table
		var selector = $('#mygrid .items-body');
		if (selector.children('.item').length === 0) {
			selector.html(obj);	
		} else {
			selector.find('.item:last').after(obj);
		}
		
		// reassign the proper click handler
		obj.click(function(){
			moveToSelectedGrid(obj);
		});
		
		var idToRemove = obj.find('.student-id').text();
			
		for (var i = 0; i < window.selectedStudents.length; i++) {
			if (window.selectedStudents[i].id == idToRemove) {
				var studentObj = window.selectedStudents.splice(i, 1);
				window.students.push(studentObj[0]);
			}
		}
			
		generateStudentsGrid();
		generateSelectedGrid();
		
		var currentIds = $('#hiddeninput').val().split(',');
		for(var i = 0; i < currentIds.length; i++){
		 	if(currentIds[i] === idToRemove){
				currentIds.splice(i, 1);
			}
		}
		var result = currentIds.join(',');
		$('#hiddeninput').val(result);
	};
		
	function moveToSelectedGrid(obj) {
		// clear the current click handler
		obj.off('click');
		
		// move the DOM object over to the other table
		var selector = $('#selectedgrid .items-body');
		if (selector.children('.item').length === 0) {
			selector.html(obj);	
		} else {
			selector.find('.item:last').after(obj);
		}
		
		// reassign the proper click handler
		obj.click(function(){
			moveToStudentsGrid(obj);
		});
		
		var newId = obj.find('.student-id').text();
		
		for (var i = 0; i < window.students.length; i++) {
			if (window.students[i].id == newId) {
				var studentObj = window.students.splice(i, 1);
				window.selectedStudents.push(studentObj[0]);
			}
		}
			
		generateStudentsGrid();
		generateSelectedGrid();
		
		var currentIds = $('#hiddeninput').val();
		var separator = (currentIds === '') ? '' : ',';
		$('#hiddeninput').val(currentIds + separator + newId);
	};
	
	(function($) {
	
	    var oldHide = $.fn.popover.Constructor.prototype.hide;
	
	    $.fn.popover.Constructor.prototype.hide = function() {
	        if (this.options.trigger === \"hover\" && this.tip().is(\":hover\")) {
	            var that = this;
	            // try again after what would have been the delay
	            setTimeout(function() {
	                return that.hide.call(that, arguments);
	            }, that.options.delay.hide);
	            return;
	        }
	        oldHide.call(this, arguments);
	    };
	
	})(jQuery);

");
?>

<h1 class="centerTxt">Personal Mentor</h1>
<br>
<p class="centerTxt">From here you may select students to mentor. You can hand pick students from the list in the You Pick column. You can also provide criteria for automatic student selection.</p>
<br>

<div class="form">

<?php $form=$this->beginWidget('booster.widgets.TbActiveForm', array(
	'id'=>'personal-mentor-app',
	'enableAjaxValidation'=>false,
)); ?>

<div class="row">
	<div class="lightMarginL span8 right-border">
		<h3 class="centerTxt">You Pick</h3>
		<p class="centerTxt">Add students you wish to mentor to Your Picks by clicking on them.</p>
		<div class="row">
			<div class="span4 lightMarginL">
				<style>
				html {overflow-y: scroll;}
				.item div {display: inline-block; float: left; padding: 0.3em; font-size: 0.9em; line-height: 1em; cursor: pointer}
				.items-header {background: url("/coplat/assets/f769f9db/gridview/bg.gif") repeat-x scroll left top white; color: white; padding-top: 5px}
				.items-body {height: 400px; overflow-y: scroll; background: #F8F8F8;}
				.header-pad {padding-left: 5px}
				.student-id {display: none !important;}
				.student-avatar {width: 20%; padding: 0; border-left: 1px solid white}
				.student-name {width: 40%; border-left: 1px solid white}
				.student-university {width: 40%;  border-left: 1px solid white}
				</style>
				<div id="mygrid" class="grid-view">
					<div class="items">
						<div class="items-header row-fluid">
							<div class="student-avatar span2"></div>
							<div class="student-name span4 header-pad">Mentee</div>
							<div class="student-university span5 header-pad">University</div>
						</div>
						<div class="items-body row-fluid">
							<div class="item row-fluid" id="rowtemplate" data-trigger="hover" data-delay="500">
								<div class="student-id"></div>
								<div class="student-avatar span2"><img src="/coplat/images/profileimages/avatarsmall.gif" alt=""></div>
								<div class="student-name span4">Ingrid Troche</div>
								<div class="student-university span5">Florida International University</div>
							</div>
						</div>
					</div>
				</div>
				
			</div>
			<div class="span4 lightMarginL">
			
				<div id="selectedgrid" class="grid-view">
					<div class="items">
						<div class="items-header row-fluid">
							<div class="student-avatar span2"></div>
							<div class="student-name span4 header-pad">Mentee</div>
							<div class="student-university span5 header-pad">University</div>
						</div>
						<div class="items-body row-fluid">
							<div class="item row-fluid" id="rowtemplate" data-trigger="hover" data-delay="500">
								<div class="student-id"></div>
								<div class="student-avatar span2"><img src="/coplat/images/profileimages/avatarsmall.gif" alt=""></div>
								<div class="student-name span4">Ingrid Troche</div>
								<div class="student-university span5">Florida International University</div>
							</div>
						</div>
					</div>
				</div>
			
			</div>
		</div>
	</div>
	<div class="span4">
		<h3 class="centerTxt">System Pick Criteria</h3>
			    <p>How many students would you like to have assigned? If you would prefer to work with students from a specific university just add them to your preffered universities</p>
			    <br>
		        <?php echo $form->textFieldGroup($model,'system_pick_amount',array('size'=>2,'maxlength'=>2)); ?>
		        <?php echo $form->error($model,'system_pick_amount'); ?>
		    	<br>
		    	<style>
				#ApplicationPersonalMentor_system_pick_amount {height: 34px;}
				</style>
		        <?php echo $form->dropDownListGroup($model, 'university_id', array('wrapperHtmlOptions'=>array('class'=>'col-sm-5'),
		         																'widgetOptions'=>array(
																		        	'data' => $universities,
																					'htmlOptions' => array(),
		        )));?>
		        <?php echo $form->error($model,'university_id'); ?>
		        
		        <?php echo CHtml::hiddenField('picks', '', array('id'=>'hiddeninput'));?>	
	</div>
</div>
<div class="text-center">
<?php echo CHtml::submitButton('Submit', array("class"=>"btn btn-large btn-primary")/*$model->isNewRecord ? 'Create' : 'Save'*/); ?>
<a style="text-decoration:none" href="/coplat/index.php/application/portal">
	<?php $this->widget('bootstrap.widgets.TbButton', array(
                'buttonType'=>'button',
                'type'=>'danger',
				'size'=>'large',
                'label'=>'Cancel',
            )); ?>
</a>
</div>
<?php $this->endWidget();?>