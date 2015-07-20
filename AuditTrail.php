<?php
/**
 * Created by PhpStorm.
 * User: i_s_lutokhin
 * Date: 20.07.2015
 * Time: 16:47
 */
namespace fwext\AuditTrail;

use Yii;

/**
 * This is the model class for table "audit_trail".
 *
 * @property integer $user_id
 * @property integer $user_ip
 * @property integer $action_type
 * @property string $model
 * @property string $model_id
 * @property string $field
 * @property string $value_old
 * @property string $value_new
 * @property integer $timestamp
 */
class AuditTrail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'audit_trail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_ip', 'action_type', 'timestamp'], 'integer'],
            [['action_type', 'object', 'object_id', 'timestamp'], 'required'],
            [['value_old', 'value_new'], 'string'],
            [['object'], 'string', 'max' => 255],
            [['object_id', 'field'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'user_ip' => 'User Ip',
            'action_type' => 'Action Type',
            'object' => 'Model',
            'object_id' => 'Model ID',
            'field' => 'Field',
            'value_old' => 'Value Old',
            'value_new' => 'Value New',
            'timestamp' => 'Timestamp',
        ];
    }
}