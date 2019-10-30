<?php

namespace TimWhitlock\JavaScript\JParser;

class JProgramNode extends JNodeBase
{
    function obfuscate(array &$names)
    {
        $Elements = $this->reset();
        $Elements->obfuscate($names);
    }

    function format()
    {
        $Elements = $this->reset();
        $line = '';
        $lines = [];
        $Elements->format_lines($line, $lines);
        return implode("\n", $lines);
    }
}
