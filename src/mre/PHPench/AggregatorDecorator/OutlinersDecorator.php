<?php declare(strict_types=1);

namespace mre\PHPench\AggregatorDecorator;

use mre\PHPench\AggregatorInterface;
use mre\PHPench\Util\Math;

/**
 * Class OutlinersDecorator.
 *
 * Use the $magnitude to decide the amount of outliners to esclude
 *
 * lower magnitude = less elements
 *
 * @example new mre\PHPench(new OutlinersDecorator(MedianAggregator(), 1.25));
 */
class OutlinersDecorator implements AggregatorInterface
{
    /** @var AggregatorInterface */
    private $aggregator;

    /** @var float */
    private $magnitude;

    /**
     * OutlinersDecorator constructor.
     *
     * @param AggregatorInterface $aggregator
     * @param float               $magnitude  factor of std_dev
     */
    public function __construct(AggregatorInterface $aggregator, float $magnitude = 1.25)
    {
        $this->aggregator = $aggregator;
        $this->magnitude = $magnitude;
    }

    public function push($i, $index, $value): void
    {
        $this->aggregator->push($i, $index, $value);
    }

    public function getData()
    {
        $data = $this->aggregator->getData();

        $dataByTitle = [];
        foreach ($data as $rowIndex => $rowData) {
            foreach ($rowData as $testIndex => $value) {
                $dataByTitle[$testIndex][] = $value;
            }
        }

        $dataWithoutOutliners = [];
        foreach ($dataByTitle as $testIndex => $testData) {
            $dataWithoutOutliners[$testIndex] = $this->removeOutliners($testData);
        }

        $ret = [];
        foreach ($dataWithoutOutliners as $testIndex => $testData) {
            foreach ($testData as $i => $v) {
                $ret[$i][$testIndex] = $v;
            }
        }


        return $ret;
    }

    public function removeOutliners($dataset)
    {
        if (0 === count($dataset)) {
            return [];
        }

        $ret = [];
        $mean = array_sum($dataset) / count($dataset);
        $stddev = Math::standardDeviation($dataset);

        $threshold = $this->magnitude * $stddev;
        $thresholdBottom = $mean - $threshold;
        $thresholdTop = $mean + $threshold;

        foreach ($dataset as $v) {
            switch (true) {
                case (float) $v > $thresholdTop:
                    $v = $thresholdTop;
                    break;
                case (float) $v < $thresholdBottom:
                    $v = $thresholdBottom;
                    break;
            }

            $ret[] = $v;
        }

        return $ret;
    }
}
