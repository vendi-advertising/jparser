<?php

namespace TimWhitlock\JavaScript\JParser;

class JEqExprNode extends JNodeBase
{
    function is_transparent(JNodeBase $Parent)
    {
        return true && $this->length === 1;
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
