<?php

namespace TimWhitlock\JavaScript\JParser;

class JFuncDeclNode extends JNodeBase
{
    function obfuscate(array &$names)
    {
        $this->reset();
        $Identifier = $this->next();
        if ($Identifier->is_symbol(J_IDENTIFIER)) {
            $Identifier->__obfuscate($names);
            $this->next();
        }
        unset($Identifier);
        $Params = $this->next();
        if ($Params->is_symbol(J_PARAM_LIST)) {
            $Params->obfuscate($names);
            $this->next();
        }
        unset($Params);
        $this->next();
        $Body = $this->next();
        $Body->obfuscate($names);
    }

    function format_lines(&$line, array &$lines)
    {
        $Node = $this->reset();
        do {
            $Node->format_lines($line, $lines);
        } while (($Node = $this->next()) && !$Node->is_symbol(J_FUNC_BODY));
        JNodeBase::format_newline('', $line, $lines);
        $funclines = [];
        $Node->format_lines($line, $funclines);
        foreach ($funclines as $funcline) {
            $lines[] = "\t" . $funcline;
        }
        if ($this->is_symbol(J_FUNC_EXPR)) {
            $line .= '} ';
        } else {
            if ($line) {
                JNodeBase::format_newline('', $line, $lines);
            }
            JNodeBase::format_newline('}', $line, $lines);
        }
    }
}
