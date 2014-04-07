<?php

class TicketController extends Controller
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array('index', 'view'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('create', 'update','Download'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
       /*Retrieve ticket Details */
       $ticket = Ticket::model()->findByPk($id);

       /*Retrieve the names for each ticket */
       $userCreator = User::model()->findBySql("SELECT * from USER  WHERE id=:id", array(":id"=>$ticket->creator_user_id));
       $userAssign = User::model()->findBySql("SELECT * from USER  WHERE id=:id", array(":id"=>$ticket->assign_user_id));
       $domainName = Domain::model()->findBySql("SELECT * from domain  WHERE id=:id", array(":id"=>$ticket->domain_id));

        $this->render('view', array(
            'model' => $this->loadModel($id), /*Return all the ticket details */
            'userCreator'=>$userCreator, 'userAssign'=>$userAssign, 'domainName'=>$domainName
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model = new Ticket;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Ticket'])) {
            $model->attributes = $_POST['Ticket'];
            $domain_id = $model->domain_id;
            //Populate ticket attributes
            //Get the ID of the user
            $model->creator_user_id = User::getCurrentUserId();
            $model->created_date = new CDbExpression('NOW()');

            /*Assign the ticket to the most appropiate Domain mentor */
            $model->assign_user_id = User::assignTicket($domain_id);

            $model->status = 'Pending';
            /*Attach file */
            $uploadedFile = CUploadedFile::getInstance($model, 'file');
            $fileName = "{$uploadedFile}";
            if($fileName != null) {
                $model->file = 'coplat/uploads/' . $fileName;
                $uploadedFile->saveAs(Yii::getPathOfAlias('webroot') . '/uploads/' . $fileName);
            }else {
                $model->file = '';
            }


            if ($model->save()) {
                /*Save file uploaded in the Uploads folder */

                /*Send Notification the the Domain Mentor who was assigned the ticket */
                User::sendTicketAssignedEmailNotification($model->creator_user_id, $model->assign_user_id, $model->domain_id);
                $this->redirect(array('view', 'id' => $model->id));
            }
        }
        $this->render('create', array('model' => $model,));
        //$this->render('index',array('model'=>$model,));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Ticket'])) {
            $model->attributes = $_POST['Ticket'];
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $dataProvider = new CActiveDataProvider('Ticket');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));

        /* Retrieve info from User Creator and Assign User Name */
        /* Domain Name also */
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model = new Ticket('search');
        $model->unsetAttributes(); // clear any default values
        if (isset($_GET['Ticket']))
            $model->attributes = $_GET['Ticket'];

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Ticket the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Ticket::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Ticket $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'ticket-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }


    public function actionDownload(){


        // place this code inside a php file and call it f.e. "download.php"
        $path = $_SERVER['DOCUMENT_ROOT']."/"; // change the path to fit your websites document structure
        $fullPath = $path.$_GET['download_file'];

        if ($fd = fopen ($fullPath, "r")) {
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            $ext = strtolower($path_parts["extension"]);
            switch ($ext) {
                case "pdf":
                    header("Content-type: application/pdf"); // add here more headers for diff. extensions
                    header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
                    break;
                default;
                    header("Content-type: application/octet-stream");
                    header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
            }
            header("Content-length: $fsize");
            header("Cache-control: private"); //use this to open files directly
            while(!feof($fd)) {
                $buffer = fread($fd, 2048);
                echo $buffer;
            }
        }
        fclose ($fd);
        exit;
    }
}
