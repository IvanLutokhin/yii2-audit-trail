<?php
/**
 * Created by PhpStorm.
 * User: i_s_lutokhin
 * Date: 20.07.2015
 * Time: 16:06
 */

namespace fwext\AuditTrail;

use Yii;

use yii\db\ActiveRecord;

class AuditTrailBehavior extends \yii\base\Behavior {
    const ACTION_INSERT = 1;

    const ACTION_SET = 2;

    const ACTION_UPDATE = 3;

    const ACTION_DELETE = 4;

    public $userId = -1;

    public $userIp = "0.0.0.0";

    public $object = null;

    public $ignoreFields = [];

    public $logInsert = true;

    public $logUpdate = true;

    public $logDelete = true;

    public $logSkipNull = false;

    public $model = "\\fwext\\AuditTrail\\AuditTrail";

    protected $oldAttributes = [];

    public function getOldAttributes()
    {
        return $this->oldAttributes;
    }

    public function init()
    {
        $this->userId = (!Yii::$app->user->isGuest) ? Yii::$app->user->identity->getId() : null;
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND      => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT    => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE    => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE    => 'afterDelete',
        ];
    }

    public function afterFind($event)
    {
        $this->oldAttributes = $this->owner->getAttributes();
    }

    public function afterInsert($event)
    {
        if(!$this->logInsert) {
            return;
        }

        $this->audit(true);
    }

    public function afterUpdate($event)
    {
        if(!$this->logUpdate) {
            return;
        }

        $this->audit(false);
    }

    public function afterDelete($event)
    {
        if(!$this->logDelete) {
            return;
        }

        $this->save(self::ACTION_DELETE);
    }

    public function audit($insert)
    {
        $oldAttributes = $this->getOldAttributes();

        $newAttributes = $this->owner->getAttributes();

        if(count($this->ignoreFields) > 0) {
            $this->cleanAttributes($newAttributes);

            $this->cleanAttributes($oldAttributes);
        }

        if(count(array_diff_assoc($newAttributes, $oldAttributes)) <= 0) {
            return;
        }

        if($insert) {
            $this->save(self::ACTION_INSERT);
        }

        foreach($newAttributes as $key => $value) {
            $attribute = isset($oldAttributes[$key]) ? $oldAttributes[$key] : '';

            if($this->logSkipNull && empty($attribute) && empty($value)) {
                continue;
            }

            if($attribute !== $value) {
                $this->save($insert ? self::ACTION_SET : self::ACTION_UPDATE, $key, $value, $attribute);
            }
        }
    }

    public function save($actionType, $field = null, $valueNew = null, $valueOld = null)
    {
        $actionAudit = new $this->model;
        $actionAudit->user_id = $this->userId;
        $actionAudit->user_ip = ip2long(Yii::$app->getRequest()->getUserIP());
        $actionAudit->action_type = $actionType;
        $actionAudit->object = $this->owner->className();
        $actionAudit->object_id = $this->owner->getPrimaryKey();
        $actionAudit->field = $field;
        $actionAudit->value_old = $valueOld;
        $actionAudit->value_new = $valueNew;
        $actionAudit->timestamp = time();

        return $actionAudit->save();
    }

    protected function cleanAttributes(&$attributes)
    {
        foreach($attributes as $key => $value) {
            if(array_search($key, $this->ignoreFields)) {
                unset($attributes[$key]);
            }
        }
    }
} 