<?php

namespace TimWhitlock\JavaScript\JParser;

class JParamListNode extends JNodeBase
{
    function obfuscate(array &$names)
    {
        foreach ($this->get_nodes_by_symbol(J_IDENTIFIER, 1) as $Identifier) {
            $Identifier->__obfuscate($names);
        }
    }
}
