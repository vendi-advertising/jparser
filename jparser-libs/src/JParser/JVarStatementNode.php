<?php

namespace TimWhitlock\JavaScript\JParser;

class JVarStatementNode extends JNodeBase
{
    function is_transparent(JNodeBase $Parent)
    {
        return false;
    }

    function __toString()
    {
        return parent::__toString();
    }

    function evaluate()
    {
        return parent::evaluate();
    }
}
