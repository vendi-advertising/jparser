<?php

namespace TimWhitlock\JavaScript\JParser;

class JVarDeclNode extends JNodeBase
{
    function obfuscate(array &$names)
    {
        $Identifier = $this->reset();
        $Identifier->__obfuscate($names);
        $Initializer = $this->next();
        if ($Initializer) {
            $Initializer->obfuscate($names);
        }
    }
}
