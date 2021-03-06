<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord implements \yii\web\IdentityInterface
{

    const STATUS_ACTIVE = 10;
    const STATUS_DELETE = 5;

    const ROLE_USER = 10;

    public static function tableName(){
        return 'user';
    }

    /*
     * 验证规则
     */
    public function rules(){
        return [
            [['username', 'email'], 'required'],
            [['role'], 'integer'],
            [['username', 'email', 'password'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['username'], 'match', 'pattern' => '/^[a-z]\w*$/i'],
            [['email'], 'unique'],
            [['email'], 'email'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE,self::STATUS_DELETE]],
            ['role', 'default', 'value' => self::ROLE_USER],
            ['role', 'in', 'range' => [self::ROLE_USER]]
        ];
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => function() {
                    return date('U');
                    // unix timestamp
                    },
                ],
        ];
    }

    /*
     * 标签
     */
    public function attributeLabels(){
        return [
            'id' => Yii::t('app', '主键'),
            'username' => Yii::t('app', '用户名'),
            'password' => Yii::t('app', '密码'),
            'email' => Yii::t('app', '邮箱'),
            'role' => Yii::t('app', '角色'),
            'status' => Yii::t('app', '状态'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
        ];
    }

    /**
     * 获取用户输入密码
     */
    public function setInputPassword($inputPassword){
        $this->password = md5($inputPassword);
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === md5($password);
    }
}
