<?php

namespace TimWhitlock\JavaScript\JParser;

class JIdentifierNode extends JNodeBase
{
    private static $obfMap;
    private static $obfInc = 0;
    private $obfuscated;

    function __obfuscate(array &$names)
    {
        if (isset($this->obfuscated)) {
            return $this->value;
        }
        if (!isset($names[$this->value])) {
            $names[$this->value] = self::obf_name($this->value);
        }
        $this->obfuscated = $this->value;
        return $this->value = $names[$this->value];
    }

    static function obf_name($name)
    {
        if (!isset(self::$obfMap[$name])) {
            self::$obfMap[$name] = sprintf('$%x', ++self::$obfInc);
        }
        return self::$obfMap[$name];
    }

    function obfuscate(array &$names)
    {
        if (isset($names[$this->value])) {
            $this->value = $names[$this->value];
        }
    }
}
