<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 23.11.12
 * Time: 16:58
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Core\PhpSettings;

class SettingManager
{
    protected $_customSettingsMap
        = ['locale.all' => ['callback' => 'setlocale', 'args' => [LC_ALL]]];

    /**
     * @param $name
     * @param null $value
     *
     * @return SettingManager
     */
    public function configSet($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $_k => $_v) {
                $this->configSet($_k, $_v);
            }
        } else {
            if ($name == 'mbstring.internal_encoding' && version_compare(PHP_VERSION, '5.6', '>=')) {
                $name = 'internal_encoding';
            } elseif (str_starts_with((string) $name, 'session_')) {
                $name = str_replace('session_', 'session.', $name);
            }
            if (array_key_exists($name, $this->_customSettingsMap)) {
                $callback = $this->_customSettingsMap[$name]['callback'];
                $args = $this->_customSettingsMap[$name]['args'];
                if (is_array($value)) {
                    $args = array_merge($args, $value);
                } else {
                    array_push($args, $value);
                }
                call_user_func_array($callback, $args);
            } else {
                ini_set($name, $value);
            }
        }
        return $this;
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function configGet($name)
    {
        return ini_get($name);
    }
}
