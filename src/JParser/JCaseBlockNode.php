<?php

namespace TimWhitlock\JavaScript\JParser;

class JCaseBlockNode extends JNodeBase
{
    function format_lines(&$line, array &$lines)
    {
        foreach ($this->children as $Node) {
            if ($Node->is_symbol('{')) {
                JNodeBase::format_newline('{', $line, $lines);
            } else if ($Node->is_symbol('}')) {
                if ($line) {
                    JNodeBase::format_newline('', $line, $lines);
                }
                JNodeBase::format_newline('}', $line, $lines);
            } else {
                $blocklines = [];
                $Node->format_lines($line, $blocklines);
                foreach ($blocklines as $blockline) {
                    $lines[] = "\t" . $blockline;
                }
            }
        }
    }
}

