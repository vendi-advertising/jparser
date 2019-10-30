<?php

namespace TimWhitlock\JavaScript\JParser;

class JBlockNode extends JNodeBase
{
    function format_lines(&$line, array &$lines)
    {
        $Block = $this->get_child(1);
        if (!$Block->is_symbol(J_STATEMENT_LIST)) {
            JNodeBase::format_newline('{ }', $line, $lines);
            return;
        }
        JNodeBase::format_newline('{', $line, $lines);
        $blocklines = [];
        $Block->format_lines($line, $blocklines);
        foreach ($blocklines as $blockline) {
            $lines[] = "\t" . $blockline;
        }
        if ($line) {
            JNodeBase::format_newline('', $line, $lines);
        }
        JNodeBase::format_newline('}', $line, $lines);
    }
}
