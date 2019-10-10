<?php declare(strict_types=1);

namespace mre\PHPench\Util;

/**
 * Provides some methods for mathematical operations.
 *
 * @author Markus Poerschke <markus@eluceo.de>
 */
class Math
{
    /**
     * Calculates the median of the given array.
     *
     * @param array $input
     *
     * @return float
     */
    public static function median(array $input)
    {
        $count = count($input);
        if($count === 0) {
            return 0;
        }
        if($count === 1) {
            return $input[0];
        }

        /* sort values*/
        sort($input, SORT_NUMERIC);

        /* get middle index */
        $middle = (int) floor($count / 2);
        $median = $input[$middle];

        return ($middle % 2 === 0)
            ? ($median + $input[$middle-1]) / 2
            : $median
        ;
    }

    public static function standardDeviation(array $dataset)
    {
        $num_of_elements = (float) count($dataset);

        $sum = (float) array_sum($dataset);
        $average = $sum / $num_of_elements;

        $variance = 0.0;
        foreach ($dataset as $i) {
            $variance += (float)pow($i - $average, 2);
        }

        return (float)sqrt($variance / $num_of_elements);
    }

    public static function movingAverage(array $data, int $length)
    {
        $sum = array_sum(array_slice($data, 0, $length));

        $result = array($length - 1 => $sum / $length);

        for ($i = $length, $n = count($data); $i != $n; ++$i) {
            $result[$i] = $result[$i - 1] + ($data[$i] - $data[$i - $length]) / $length;
        }

        return $result;
    }
}
