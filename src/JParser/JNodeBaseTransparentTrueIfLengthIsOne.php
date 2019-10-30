<?php

namespace TimWhitlock\JavaScript\JParser;

abstract class JNodeBaseTransparentTrueIfLengthIsOne extends JNodeBase
{
    final function is_transparent(JNodeBase $Parent)
    {
        return $this->length === 1;
    }
}
