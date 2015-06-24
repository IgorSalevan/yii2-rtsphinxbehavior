<?php
namespace modules\blog\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\base\InvalidConfigException;

/**
 * Class RtSphinxBehavior
 * @package modules\blog\components
 *  Helpful behavior for handling synchronization with Sphinx realTime index
 */
class RtSphinxBehavior extends Behavior {

    /**
     * @var string provide the name of realtime index from you sphinx.conf file
     */
    public $rtIndex = null;

    /**
     * @var integer the name of document ID from main document fetch query (sphinx.conf)
     */
    public $idAttributeName = null;

    /**
     * @var array the set of rt_field names (sphinx.conf)
     */
    public $rtFieldNames = [];

    /**
     * @var array the set of rt attributes
     */
    public $rtAttributeNames = [];

    /**
     * @var bool turning on | off the behavior
     */
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
        return $this->enabled && $this->replace();
    }

    public function afterUpdate() {
        return $this->enabled && $this->replace();
    }

    public function afterDelete() {
        if (!$this->enabled) {
            return false;
        }

        $params = [];
        $sql = \Yii::$app->sphinx->getQueryBuilder()
            ->delete(
                $this->rtIndex,
                $this->idAttributeName.'='.$this->owner->getAttribute($this->idAttributeName),
                $params
            );

        return \Yii::$app->sphinx->createCommand($sql, $params)->execute();
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
        return \Yii::$app->sphinx->createCommand($sql, $params)->execute();
    }
}