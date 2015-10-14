<?php

/**
 * This is the model class for table "feedback_replies".
 *
 * The followings are the available columns in table 'feedback_replies':
 * @property string $id
 * @property string $feed_id
 * @property string $reply
 * @property integer $user_id
 */
class feedbackreplies extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return FeedbackReplies the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'feedback_replies';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('feed_id', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('feed_id', 'length', 'max'=>11),
			array('reply', 'length', 'max'=>5000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, feed_id, reply, user_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'feed_id' => array(self::BELONGS_TO, 'Feedback', 'id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'feed_id' => 'Feed',
			'reply' => 'Reply',
			'user_id' => 'User',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('feed_id',$this->feed_id,true);
		$criteria->compare('reply',$this->reply,true);
		$criteria->compare('user_id',$this->user_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}