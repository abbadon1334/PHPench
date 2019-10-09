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

        if ($count < 1) {
            return 0;
        } elseif (1 === $count) {
            return reset($input);
        }

        // cleanup input array
        $input = array_values($input);
        sort($input, SORT_NUMERIC);

        if (0 === $count % 2) {
            $center = (int) floor($count / 2);

            return ($input[$center - 1] + $input[$center]) / 2;
        }

        return $input[(int) ($count / 2)];
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
}
