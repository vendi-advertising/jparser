<?php

namespace TimWhitlock\JavaScript\JParser;

class JAssignOpNode extends JNodeBase
{
    function is_transparent(JNodeBase $Parent)
    {
        return true;
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
