<?php

namespace TimWhitlock\JavaScript\JParser;

class JNewExprNode extends JNodeBase
{
    function is_transparent(JNodeBase $Parent)
    {
        return true && $this->length === 1;
    }
}
