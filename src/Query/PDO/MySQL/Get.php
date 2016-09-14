<?php

namespace Imhonet\Connection\Query\PDO\MySQL;

use Imhonet\Connection\Query\PDO;

/**
 * @todo check last query
 */
class Get extends PDO\Get
{
    const SQL_WRAP_COUNT = '
        SELECT COUNT(*) FROM (
            %s
        ) as temp
    ';

    private $count;
    private $count_total;

    /**
     * @var \PDOException|null
     */
    private $err_count_total;
    /**
     * @var \PDOException|null
     */
    private $err_count;

    /**
     * @todo check offset when limit not specified
     * @todo last page optimization (FOUND_ROWS < LIMIT)
     * @inheritdoc
     */
    public function getCountTotal()
    {
        if (!$this->hasCountTotal()) {
            if (!$this->isLimit()) {
                if ($this->isExecuted()) {
                    $this->count_total = $this->getCountTotalAfterExecute();
                } elseif ($this->hasCount()) {
                    $this->count_total = $this->getCount();
                } else {
                    $this->execute();
                    $this->count_total = !$this->getErrorCode() ? $this->getCountTotalAfterExecute() : 0;
                }
            } else {
                if ($this->isExecuted() && $this->isSCFR()) {
                    $this->count_total = $this->getCountTotalAfterExecute();
                } elseif ($this->hasResponse()) {
                    $this->count_total = $this->isGroupBy()
                        ? $this->getCountTotalAutoSCFR()
                        : $this->getCountTotalWithoutLimit();
                } else {
                    if (!$this->isSCFR()) {
                        $this->addStatementTransformer('SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ');
                    }

                    $this->execute();
                    $this->count_total = !$this->getErrorCode() ? $this->getCountTotalAfterExecute() : 0;
                }
            }
        }

        return $this->count_total;
    }

    private function getCountTotalAfterExecute()
    {
        try {
            $found_rows = $this->getFoundRows();
        } catch (\PDOException $e) {
            $this->err_count_total = $e;
        }

        return isset($found_rows) ? $found_rows : $this->count_total;
    }

    private function getCountTotalAutoSCFR()
    {
        if (!$this->isSCFR()) {
            $this->addStatementTransformer('SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ');
        }

        try {
            $this->getStmt($this->getStatement(), $this->getParams())->execute();
            $found_rows = $this->getFoundRows();
        } catch (\PDOException $e) {
            $this->err_count_total = $e;
        }

        return isset($found_rows) ? $found_rows : $this->count_total;
    }

    private function getCountTotalWithoutLimit()
    {
        try {
            $result = $this->getSelectCountTotal();
        } catch (\PDOException $e) {
            $this->err_count_total = $e;
        }

        return isset($result) ? $result : $this->count_total;
    }

    protected function hasCountTotal()
    {
        return $this->count_total !== null || $this->err_count_total !== null;
    }

    /**
     * @inheritdoc
     */
    public function getCount()
    {
        if (!$this->hasCount()) {
            if ($this->isExecuted() && !$this->isSCFR()) {
                $this->count = $this->getCountAfterExecute();
            } elseif ($this->hasCountTotal()) {
                $this->count = $this->getCountAfterCountTotal();
            } else {
                $this->count = $this->getCountWrapped();
            }
        }

        return $this->count;
    }

    private function getCountAfterExecute()
    {
        try {
            $found_rows = $this->getFoundRows();
        } catch (\PDOException $e) {
            $this->err_count = $e;
        }

        return isset($found_rows) ? $found_rows - $this->getOffsetWithLimit()['offset'] : $this->count;
    }

    private function getCountAfterCountTotal()
    {
        $offset_limit = $this->getOffsetWithLimit();
        $diff = $this->getCountTotal() - $offset_limit['offset'];
        return $diff > $offset_limit['limit'] ? $offset_limit['limit'] : $diff;
    }

    private function getCountWrapped()
    {
        $result = null;

        try {
            $result = $this->getSelectCount();
        } catch (\PDOException $e) {
            $this->err_count = $e;
        }

        return $result;
    }

    private function hasCount()
    {
        return $this->count !== null || $this->err_count !== null;
    }

    /**
     * @todo support unbuffered queries
     * @return string
     * @throws \PDOException
     */
    private function getFoundRows()
    {
        return $this->getResource()->query('SELECT FOUND_ROWS()')->fetchColumn();
    }

    /**
     * @return string
     * @throws \PDOException
     */
    private function getSelectCount()
    {
        $stmt = $this->getStmt($this->getStatementCount(), $this->getParams());
        $stmt->execute();
        $result = $stmt->fetchColumn();

        return $result;
    }

    /**
     * @return string
     * @throws \PDOException
     */
    private function getSelectCountTotal()
    {
        $stmt = $this->getStmt($this->getStatementLimitless(), $this->getParams());
        $stmt->execute();
        $result = $stmt->fetchColumn($stmt->columnCount() - 1);

        return $result;
    }

    private function getOffsetWithLimit()
    {
        $result = array('offset' => null, 'limit' => null);

        $cnt = preg_match(
            '/(LIMIT\s+(?P<limit_or_offset>\d+|\?)\s*?(,\s*?(?P<limit>\d+|\?))?)?\s*?(?(limit)|OFFSET\s+(?P<offset>\d+|\?))/',
            $this->getStatement(),
            $match
        );

        if ($cnt) {
            $params = $this->getParams();
            end($params);

            if (isset($match['offset'])) {
                $result['offset'] = $match['offset'] == '?' ? current($params) : (int) $match['offset'];

                if (isset($match['limit_or_offset'])) {
                    $result['limit'] = $match['limit_or_offset'] == '?' ? prev($params) : (int) $match['limit_or_offset'];
                }
            } else {
                $result['limit'] = $match['limit'] == '?' ? current($params) : (int) $match['limit'];
                $result['offset'] = $match['limit_or_offset'] == '?' ? prev($params) : (int) $match['limit_or_offset'];
            }
        }

        return $result;
    }

    /**
     * @todo performance tests
     * @return string
     */
    private function getStatementCount()
    {
        $replace = array(
            'SQL_CALC_FOUND_ROWS' => '',
        );
        $statement = str_ireplace(array_keys($replace), array_values($replace), rtrim($this->getStatement(), ';'));

        return sprintf(self::SQL_WRAP_COUNT, $statement);
    }

    private function getStatementLimitless()
    {
        $replace = array(
            'FROM ' => ', COUNT(1) FROM ',
            'LIMIT ' => '#LIMIT ',
            'OFFSET ' => '#OFFSET ',
        );

        return str_ireplace(array_keys($replace), array_values($replace), $this->getStatement());
    }

    /**
     * SQL_CALC_FOUND_ROWS check
     * @return bool
     */
    private function isSCFR()
    {
        return stripos($this->getStatement(), 'SQL_CALC_FOUND_ROWS') !== false;
    }

    private function isGroupBy()
    {
        return stripos($this->getStatement(), 'GROUP ');
    }

    private function isLimit()
    {
        return stripos($this->getStatement(), 'LIMIT ');
    }

    /**
     * @return bool
     */
    private function isExecuted()
    {
        return $this->isLastQueryMain();
    }
}
