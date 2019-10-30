<?php

namespace TimWhitlock\JavaScript\JParser;

class JLabelledStatementNode extends JNodeBase
{
    function obfuscate(array &$names)
    {
        $Identifier = $this->reset();
        $label = $Identifier->__toString();
        $inscope = isset($names[$label]);
        $Identifier->__obfuscate($names);
        $Statement = $this->get_child(2);
        $Statement->obfuscate($names);
        if (!$inscope) {
            unset($names[$label]);
        }
    }

    function format_lines(&$line, array &$lines)
    {
        foreach ($this->children as $Node) {
            if ($Node->is_symbol(':')) {
                $line = rtrim($line, ' ');
                JNodeBase::format_newline(':', $line, $lines);
            } else {
                $Node->format_lines($line, $lines);
            }
        }
    }
}
