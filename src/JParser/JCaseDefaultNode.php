<?php

namespace TimWhitlock\JavaScript\JParser;

class JCaseDefaultNode extends JNodeBase
{
    function format_lines(&$line, array &$lines)
    {
        foreach ($this->children as $Node) {
            if ($Node->is_symbol(':')) {
                $line = rtrim($line, ' ');
                JNodeBase::format_newline(':', $line, $lines);
            } else {
                $caselines = [];
                $Node->format_lines($line, $caselines);
                foreach ($caselines as $caseline) {
                    $lines[] = "\t" . $caseline;
                }
            }
        }
    }
}
