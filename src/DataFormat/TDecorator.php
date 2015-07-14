<?php

namespace Imhonet\Connection\DataFormat;


trait TDecorator
{
    /** @type IDataFormat */
    private $formatter;

    /**
     * @inheritDoc
     */
    public function setFormatter(IDataFormat $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    private function getData()
    {
        return $this->formatter
            ? $this->formatter->setData($this->getDataRaw())->formatData()
            : $this->getDataRaw();
    }

    abstract protected function getDataRaw();
}
