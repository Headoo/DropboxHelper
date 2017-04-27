<?php

namespace Headoo\DropboxHelper\AbstractClass;

abstract class AbstractExceptionMode
{
    const MODE_STRICT = true;
    const MODE_SILENCE = false;

    protected $exceptionMode = self::MODE_STRICT;

    /**
     * @return $this
     */
    public function setModeStrict()
    {
        $this->exceptionMode = self::MODE_STRICT;

        return $this;
    }

    /**
     * @return $this
     */
    public function setModeSilence()
    {
        $this->exceptionMode = self::MODE_SILENCE;

        return $this;
    }

    /**
     * @return bool
     */
    public function getExceptionMode()
    {
        return $this->exceptionMode;
    }

    /**
     * @param bool $bMode
     * @return $this
     */
    public function setExceptionMode($bMode)
    {
        $this->exceptionMode = (!empty($bMode));

        return $this;
    }

}
