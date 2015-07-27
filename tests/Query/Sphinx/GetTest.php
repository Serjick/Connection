<?php

namespace Imhonet\Connection\Test\Query\Sphinx;

use Imhonet\Connection\Query\Sphinx\Get;
use Imhonet\Connection\Resource\IResource;

/**
 * Class GetTest
 * @covers \Imhonet\Connection\Query\Sphinx\Get::
 * @coversDefaultClass \Imhonet\Connection\Query\Sphinx\Get
 * @package Imhonet\Connection\Test\Query
 */
class GetTest extends \PHPUnit_Framework_TestCase
{
    private $index = 'index_test';
    private $sort = 'sort_field';
    private $sort_expr = '@sort_field * 3';
    private $field_weights = [
        'title' => 100
    ];
    private $filter = ['field' => 'filter_content_id', 'values' => [3]];
    private $filter_and = ['field' => 'mva_ids', 'values' => [70, 14]];
    private $filter_not = ['field' => 'filter_content_id', 'values' => [54, 2, 1]];
    private $limit = 3;
    private $offset = 6;
    private $response = '[{
        "error":"",
        "warning":"",
        "status":0,
        "fields":["title", "description"],
        "attrs":{"attr_id":1, "id":1, "filter_content_id":1, "sort_field":1, "title":1},
        "matches":{
            "1148685":{
                "weight":"1",
                "attrs":{"attr_id":1148685, "id":1148685, "filter_content_id":3, "sort_field":3, "title":"test title"}
            },
            "9764681":{
                "weight":"1",
                "attrs":{"attr_id":9764681, "id":9764681, "filter_content_id":3, "sort_field":50, "title":"test title2"}
            },
            "9782242":{
                "weight":"1",
                "attrs":{"attr_id":9782242, "id":9782242, "filter_content_id":3, "sort_field":51, "title":"test title3"}
            }
        },
        "total":"1000",
        "total_found":"79082",
        "time":"0.015"
    }]';

    /**
     * @var Get
     */
    private $query;

    protected function setUp()
    {
        $this->query = new Get();
        $this->query->addIndex($this->index);
    }

    /**
     * @covers ::__construct
     */
    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\\Connection\\Query\\Query', $this->query);
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $this->query->setResource($this->getResource());
        $this->assertEquals(json_decode($this->response, true), $this->query->execute());
    }

    /**
     * @covers ::setLimit
     */
    public function testExecuteLimit()
    {
        $this->query
            ->setResource($this->getResource(true))
            ->setLimit($this->limit, $this->offset);
        $this->query->execute();
    }

    /**
     * @covers ::setOrder
     */
    public function testExecuteSort()
    {
        $this->query
            ->setResource($this->getResource(true, true))
            ->setLimit($this->limit, $this->offset)
            ->setOrder($this->sort);
        $this->query->execute();
    }

    /**
     * @covers ::setOrderExpr
     */
    public function testExecuteSortExpr()
    {
        $this->query
            ->setResource($this->getResource(true, false, true))
            ->setLimit($this->limit, $this->offset)
            ->setOrderExpr($this->sort_expr);
        $this->query->execute();
    }

    /**
     * @covers ::setFieldWeight
     */
    public function testExecuteFieldWeight()
    {
        $this->query
            ->setResource($this->getResource(false, false, false, true))
            ->setFieldWeight('title', 100);

        $this->query->execute();
    }

    /**
     * @covers ::addFilter
     */
    public function testExecuteFilter()
    {
        $this->query
            ->setResource($this->getResource(false, false, false, false, true))
            ->addFilter($this->filter['field'], $this->filter['values'])
            ->addFilter($this->filter_and['field'], $this->filter_and['values'], Get::FILTER_IMPLODE_AND)
            ->addFilter($this->filter_not['field'], $this->filter_not['values'], Get::FILTER_EXCLUDE)
        ;

        $this->query->execute();
    }

    /**
     * @covers ::setMatchMode
     */
    public function testExecuteMatch()
    {
        $this->query
            ->setResource($this->getResource(false, false, false, false, false, true))
            ->setMatchMode(\SPH_MATCH_EXTENDED2);

        $this->query->execute();
    }

    /**
     * @covers ::getErrorCode
     */
    public function testErrorCode()
    {
        $this->query->setResource($this->getResource());
        $this->assertEquals(0, $this->query->getErrorCode());
    }

    /**
     * @covers ::getCount
     * @covers ::getCountTotal
     */
    public function testCount()
    {
        $this->query->setResource($this->getResource());
        $this->assertEquals($this->limit, $this->query->getCount());
        $this->assertEquals(1000, $this->query->getCountTotal());
    }

    public function testFailure()
    {
        $this->query->setResource($this->getResourceFailed());
        $this->assertEquals(false, $this->query->execute());
    }

    public function testFailureErrorCode()
    {
        $this->query->setResource($this->getResourceFailed());
        $this->assertNotEquals(0, $this->query->getErrorCode());
    }

    /**
     * @param bool $use_limit
     * @param bool $use_sort
     * @param bool $use_sort_expr
     * @param bool $use_field_weights
     * @param bool $use_filter
     * @param bool $use_match
     * @return IResource
     */
    private function getResource(
        $use_limit = false,
        $use_sort = false,
        $use_sort_expr = false,
        $use_field_weights = false,
        $use_filter = false,
        $use_match = false
    ) {
        $sph = $this->getMock(
            '\\SphinxClient',
            array('runQueries', 'addQuery', 'SetSelect', 'SetSortMode', 'SetLimits', 'SetFieldWeights', 'SetFilter', 'SetMatchMode')
        );

        if ($use_limit) {
            $sph->expects($this->once())
                ->method('SetLimits')
                ->with($this->offset, $this->limit);
        }

        if ($use_sort) {
            $sph->expects($this->once())
                ->method('SetSortMode')
                ->with(\SPH_SORT_ATTR_ASC, $this->sort);
        }

        if ($use_sort_expr) {
            $sph->expects($this->once())
                ->method('SetSortMode')
                ->with(\SPH_SORT_EXPR, $this->sort_expr);
        }

        if ($use_field_weights) {
            $sph->expects($this->once())
                ->method('SetFieldWeights')
                ->with($this->field_weights);
        }

        if ($use_filter) {
            $sph->expects($this->once())
                ->method('SetSelect')
                ->with('*, (IN (filter_content_id, 3)) AND (IN (mva_ids, 70) AND IN (mva_ids, 14)) AND  NOT (IN (filter_content_id, 54, 2, 1)) AS _filter');
            $sph->expects($this->once())
                ->method('SetFilter')
                ->with('_filter', array(1));
        }

        if ($use_match) {
            $sph->expects($this->once())
                ->method('SetMatchMode')
                ->with(\SPH_MATCH_EXTENDED2);
        }

        $sph
            ->expects($this->at(0 + $use_limit + $use_sort + $use_sort_expr + $use_field_weights + $use_filter*2 + $use_match))
            ->method('addQuery')
            ->with('', $this->index);
        $sph
            ->expects($this->at(1 + $use_limit + $use_sort))
            ->method('runQueries')
            ->will($this->returnValue(json_decode($this->response, true)));

        $resource = $this->getMock('Imhonet\\Connection\\Resource\\Sphinx', array('getHandle'));
        $resource
            ->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($sph));

        return $resource;
    }

    /**
     * @return IResource
     */
    private function getResourceFailed()
    {
        $sph = $this->getMock('\\SphinxClient', array('runQueries'));

        $sph
            ->expects($this->once())
            ->method('runQueries')
            ->will($this->returnValue(false));

        $resource = $this->getMock('Imhonet\\Connection\\Resource\\Sphinx', array('getHandle'));
        $resource
            ->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($sph));

        return $resource;
    }

    protected function tearDown()
    {
        $this->query = null;
    }
}
