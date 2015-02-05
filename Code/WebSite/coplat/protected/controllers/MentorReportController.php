<?php

/**
 * MentorReportController short summary.
 *
 * MentorReportController description.
 *
 * @version 1.0
 * @author aalfonso
 */
class MentorReportController extends Controller
{
    
    //////////////////////////////////////////Controll
   public function filters()
    {
        return array('accessControl'); // perform access control for CRUD operations
   	}
        
    //this is for the access rules
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('index'),
                'users'=>array('admin')),
            array('deny',  // deny all users
                'users'=>array('*')),
        );
    }
    
    //////////////////////////////From here begins all actions/////////////////////////////////
    
    //this is the default action
    public function actionIndex()
    {
        $this->layout = '//layouts/column1'; // very simple layout (no layout)
        echo("<script>console.log('actionIndex');</script>");

        //$dataProvider=new CActiveDataProvider('User');
        $this->render('index');  ///,array('dataProvider'=>$dataProvider)
    }   
    
}
