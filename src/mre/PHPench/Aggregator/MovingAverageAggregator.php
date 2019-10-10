<?php

namespace mre\PHPench\Aggregator;

use mre\PHPench\AggregatorInterface;
use mre\PHPench\Util\Math;

class MovingAverageAggregator implements AggregatorInterface
{
    private $data = [];
    private $moving_average_length;

    public function __construct($moving_average_length = 3)
    {
        $this->moving_average_length = $moving_average_length;
    }

    public function push($i, $index, $value): void
    {
        $this->data[$i][$index] = $value;
    }

    public function getData()
    {
        $data = $this->data;

        $dataByTitle = [];
        foreach ($data as $rowIndex => $rowData) {
            foreach ($rowData as $testIndex => $value) {
                $dataByTitle[$testIndex][] = $value;
            }
        }

        $moving_average_data = [];
        foreach ($dataByTitle as $testIndex => $testData) {

            $length = min(count($testData),$this->moving_average_length);

            $moving_average_data[$testIndex] = Math::movingAverage($testData,$length);
        }

        $ret = [];
        foreach ($data as $rowIndex => $rowData) {
            foreach ($rowData as $testIndex => $value) {
                $ret[$rowIndex][$testIndex] = array_shift($moving_average_data[$testIndex]) ?? $value;
            }
        }

        return $ret;
    }
}
