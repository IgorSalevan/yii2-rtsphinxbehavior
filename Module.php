<?php

namespace modules\blog\components;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Base module.
 */
class Module extends \yii\base\Module {
    /**
     * @var boolean Whether module is used for backend or not
     */
    public $isBackend = false;

    /**
     * @var string|null Module name
     */
    public static $name;

    /**
     * @inheritdoc
     */
    public function init() {
        if (static::$name === null) {
            throw new InvalidConfigException('The "name" property must be set.');
        }

        // if ($this->isBackend === true) {
            // $this->setViewPath('@modules/' . static::$name . '/views/backend');
            // if ($this->controllerNamespace === null) {
                // $this->controllerNamespace = 'modules\\' . static::$name . '\controllers\backend';
            // }
        // } else {
            // $this->setViewPath('@modules/' . static::$name . '/views/frontend');
            // if ($this->controllerNamespace === null) {
                // $this->controllerNamespace = 'modules\\' . static::$name . '\controllers\frontend';
            // }
        // }

        parent::init();
    }

    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\yii\i18n\I18N::translate()]]
     *
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     *
     * @return string the translated message.
     */
    public static function t($category, $message, $params = [], $language = null) {
        //return print_r([$category, $message, print_r($params,1), $language], 1);
        return Yii::t('modules/' . $category, $message, $params, $language);
    }
}
