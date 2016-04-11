<?php

namespace Imhonet\Connection\DataFormat;

/**
 * @implements IDecorator
 */
trait TDecorator
{
    private $_data;
    private $_is_data_pushed = false;

    /** @type IDataFormat */
    private $formatter;

    /**
     * @inheritdoc
     * @implements IDecorator::setFormatter
     * @see IDecorator::setFormatter
     */
    public function setFormatter(IDataFormat $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @inheritdoc
     * @implements IDataFormat::setData
     * @see IDataFormat::setData
     */
    public function setData($data)
    {
        if ($this->formatter) {
            $this->formatter->setData($data);
            $this->_is_data_pushed = true;
        } else {
            $this->doValidations($data);
            $this->_data = $data;
        }


        return $this;
    }

    private function getData()
    {
        if ($this->formatter && !$this->_is_data_pushed) {
            $this->formatter->setData($this->getDataRaw());
        }

        return $this->formatter
            ? $this->formatter->formatData()
            : $this->getDataRaw();
    }

    /**
     * method for assertions
     * @param mixed $data
     * @return void
     */
    protected function doValidations($data)
    {
    }

    protected function getDataRaw()
    {
        return $this->_data;
    }
}
