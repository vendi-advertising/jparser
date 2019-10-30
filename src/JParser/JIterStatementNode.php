<?php

namespace TimWhitlock\JavaScript\JParser;

class JIterStatementNode extends JNodeBase
{
    function format_lines(&$line, array &$lines)
    {
        foreach ($this->children as $Node) {
            if ($Node->is_symbol(';')) {
                $line = rtrim($line, ' ');
                $line .= '; ';
            } else {
                $Node->format_lines($line, $lines);
            }
        }
    }
}
