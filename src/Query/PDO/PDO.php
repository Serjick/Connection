<?php

namespace Imhonet\Connection\Query\PDO;

use Imhonet\Connection\Query\Query;

abstract class PDO extends Query
{
    private $last_query;
    private $statements = array();
    private $params = array();
    private $placeholders = array();

    /**
     * @var \PDOStatement|null
     */
    private $response;
    /**
     * @var bool|null
     */
    private $success;

    /**
     * @param string $statement
     * @return $this
     */
    public function addStatement($statement)
    {
        $this->statements[] = $statement;
        $this->placeholders[] = array();

        return $this;
    }

    /**
     * @param array|float|int|null|string ...$param [optional]
     * @return $this
     */
    public function addParams()
    {
        $params = array();

        foreach (func_get_args() as $param) {
            if (is_array($param)) {
                $params = array_merge($params, $param);
                $this->addPlaceholder(sizeof($param));
            } else {
                $params[] = $param;
                $this->addPlaceholder(1);
            }
        }

        $this->params[ $this->getStatementId() ] = $params;

        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    private function addPlaceholder($count)
    {
        $placeholders = & $this->placeholders[ $this->getStatementId() ];
        $placeholders[] = $count > 0 ? str_repeat('?,', $count - 1) . '?' : null;

        return $this;
    }

    private function getStatementId()
    {
        return $this->statements ? count($this->statements) - 1 : 0;
    }

    /**
     * @return \PDOStatement|bool
     */
    public function execute()
    {
        return $this->disable ? false : $this->getResponse();
    }

    protected function getResponse()
    {
        if (!$this->hasResponse()) {
            try {
                $stmt = $this->getStmt($this->getStatement(), $this->getParams());
            } catch (\Exception $e) {
                $this->error = $e;
                $this->success = false;
                $this->response = false;
            }

            if (isset($stmt)) {
                $this->response = $stmt;

                try {
                    $this->success = $stmt->execute();
                } catch (\PDOException $e) {
                    $this->error = $e;
                    $this->success = false;
                }

                $this->regLastQueryMain();
            }
        }

        return $this->response;
    }

    protected function getStmt($statement, array $params = array())
    {
        try {
            $stmt = $this->getResource()->prepare($statement);
        } catch (\PDOException $e) {
            throw $e;
        }

        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param, is_numeric($param) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }

        return $stmt;
    }

    protected function getStatement()
    {
        $statement_id = key($this->statements);

        return vsprintf($this->statements[$statement_id], $this->placeholders[$statement_id]);
    }

    protected function changeStatement($from, $to)
    {
        $statement_id = key($this->statements);
        $this->statements[$statement_id] = str_ireplace($from, $to, $this->statements[$statement_id], $count);

        return (bool) $count;
    }

    protected function getParams()
    {
        $statement_id = key($this->statements);

        return isset($this->params[$statement_id]) ? $this->params[$statement_id] : array();
    }

    protected function hasResponse()
    {
        return $this->success !== null;
    }

    /**
     * @inheritdoc
     * @return \PDO
     */
    protected function getResource()
    {
        $this->resetLastQuery();

        return parent::getResource();
    }

    private function isError()
    {
        return $this->getResponse() === null || $this->success === false;
    }

    protected function isLastQueryMain()
    {
        return $this->last_query === true;
    }

    private function regLastQueryMain()
    {
        $this->last_query = true;
    }

    private function resetLastQuery()
    {
        $this->last_query = null;
    }

    /**
     * @inheritdoc
     */
    public function getErrorCode()
    {
        return (int) $this->isError();
    }

    public function getDebugInfo($type = self::INFO_TYPE_QUERY)
    {
        switch ($type) {
            case self::INFO_TYPE_QUERY:
                $stmt = str_replace('%', '%%', $this->getStatement());
                $stmt = str_replace('?', ' "%s"', $stmt, $count);
                $result = vsprintf($stmt, $this->getParams());
                break;
            default:
                $result = parent::getDebugInfo($type);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getCacheKey()
    {
        return md5($this->getStatement() . implode('_', $this->getParams()));
    }
}
