<?php declare(strict_types=1);

namespace mre\PHPench\Aggregator;

use mre\PHPench\AggregatorInterface;
use mre\PHPench\Util\Math;

/**
 * The average of the data will be calculated.
 *
 * @author Markus Poerschke <markus@eluceo.de>
 */
class MedianAggregator implements AggregatorInterface
{
    private $data = [];

    public function push($i, $index, $value): void
    {
        $this->data[$i][$index] = $value;
    }

    public function getData()
    {
        if(count($this->data) <=1) {
            return $this->data;
        }

        $dataByTitle = [];
        foreach ($this->data as $rowIndex => $rowData) {
            foreach ($rowData as $testIndex => $value) {
                $dataByTitle[$testIndex][$rowIndex] = $value;
            }
        }

        $ret = [];
        foreach ($dataByTitle as $testIndex => $testData) {
            foreach ($testData as $i => $v) {
                $ret[$i][$testIndex] = Math::median($testData);
            }
        }

        return $ret;
    }
}
