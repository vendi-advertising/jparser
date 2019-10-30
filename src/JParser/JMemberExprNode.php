<?php

namespace TimWhitlock\JavaScript\JParser;

class JMemberExprNode extends JNodeBaseTransparentTrueIfLengthIsOne
{
    function obfuscate(array &$names)
    {
        $Node = $this->reset();
        if ($Node->is_symbol(J_NEW)) {
            $Node = $this->next();
            $Node->obfuscate($names);
            $Node = $this->next();
            $Node->obfuscate($names);
            return;
        }
        $Node->obfuscate($names);
        while ($Node = $this->next()) {
            if (!$Node->is_symbol('[')) {
                break;
            }
            $Node = $this->next();
            $Node->obfuscate($names);
            $Node = $this->next();
        }
    }

    function format_lines(&$line, array &$lines)
    {
        $Node = $this->reset();
        if ($Node->is_symbol(J_NEW)) {
            return parent::format_lines($line, $lines);
        }
        do {
            $Node->format_lines($line, $lines);
            $line = trim($line);
        } while ($Node = $this->next());
        $line .= ' ';
    }
}
