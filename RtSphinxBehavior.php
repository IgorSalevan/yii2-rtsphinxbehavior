<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 16.06.2015
 * Time: 9:39
 */
namespace modules\blog\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\base\InvalidConfigException;

class RtSphinxBehavior extends Behavior {

    public $rtIndex = null;

    public $idAttributeName = null;

    public $rtFieldNames = [];

    public $rtAttributeNames = [];

    public $enabled = false;

    public function init() {
        parent::init();

        if ($this->rtIndex === null) {
            throw new InvalidConfigException('The "rtIndex" property must be set.');
        }
        if ($this->idAttributeName === null) {
            throw new InvalidConfigException('The "idAttributeName" property must be set.');
        }
        if (!count($this->rtFieldNames)) {
            throw new InvalidConfigException('The "rtFieldNames" property must be set.');
        }
        if (!count($this->rtAttributeNames)) {
            throw new InvalidConfigException('The "rtAttributeNames" property must be set.');
        }
    }


    public function events() {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
        ];
    }

    public function afterInsert() {
        return $this->replace();
    }

    public function afterUpdate() {
        return $this->replace();
    }

    public function afterDelete() {
        $params = [];
        $sql = \Yii::$app->sphinx->getQueryBuilder()
            ->delete(
                $this->rtIndex,
                $this->idAttributeName.'='.$this->owner->getAttribute($this->idAttributeName),
                $params
            );
        $result = \Yii::$app->sphinx->createCommand($sql, $params)->execute();

        return false;
    }

    protected function getColumns() {
        $columns = [$this->idAttributeName => $this->owner->getAttribute($this->idAttributeName)];
        $columns = $this->addColumns($columns, $this->rtFieldNames);
        $columns = $this->addColumns($columns, $this->rtAttributeNames);
        return $columns;
    }

    protected function addColumns($columns, $fieldNames) {
        foreach($fieldNames as $name) {
            $value = $this->owner->getAttribute($name);
            if (!is_string($value)) {
                $value = strval($value);
            }
            $columns[$name] = $value;
        }
        return $columns;
    }

    protected function replace() {
        $params = [];

        $sql = \Yii::$app->sphinx->getQueryBuilder()
            ->replace(
                $this->rtIndex,
                $this->getColumns(),
                $params
            );
        $result = \Yii::$app->sphinx->createCommand($sql, $params)->execute();

        return false;
    }
}