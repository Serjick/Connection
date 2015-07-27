<?php

namespace Imhonet\Connection\Query;

use Imhonet\Connection\Resource\IResource;

abstract class Query implements IQuery
{
    /**
     * @var \Exception|null
     */
    protected $error;

    /**
     * @var IResource
     */
    protected $resource;

    /**
     * @param IResource $resource
     * @return $this
     */
    public function setResource(IResource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getResource()
    {
        try {
            $resource = $this->resource->getHandle();
        } catch (\Exception $e) {
            $this->error = $e;
            throw $e;
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public function getDebugInfo($type = self::INFO_TYPE_QUERY)
    {
        $result = '';

        switch ($type) {
            case self::INFO_TYPE_ERROR:
                $result = $this->error ? $this->error->getMessage() : $result;
                break;
        }

        return $result;
    }
}
