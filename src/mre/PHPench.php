<?php declare(strict_types=1);

namespace mre;

use LucidFrame\Console\ConsoleTable;
use mre\PHPench\Aggregator\SimpleAggregator;
use mre\PHPench\AggregatorInterface;
use mre\PHPench\BenchmarkInterface;
use mre\PHPench\Output\OutputInterface;
use mre\PHPench\Util\Math;
use SebastianBergmann\Timer\Timer;

/**
 * PHPench.
 *
 * This class provides the core functionality for the PHPench package.
 *
 * @link   http://github.com/mre/PHPench
 *
 * @author Matthias Endler <matthias-endler@gmx.net>
 * @author Markus Poerschke <markus@eluceo.de>
 */
class PHPench
{
    private $tests = [];

    /**
     * @var OutputInterface
     */
    private $output = null;

    /**
     * Contains an array with the run numbers.
     *
     * @var array
     */
    private $input = [];

    /**
     * @var AggregatorInterface
     */
    private $aggregator;

    /**
     * The number of times the bench should be executed.
     *
     * This can increase the precise.
     *
     * @var int
     */
    private $repetitions = 3;
    /**
     * @var bool
     */
    private $show_table_final = false;
    /**
     * @var bool
     */
    private $show_table_pass = false;

    public function __construct(?AggregatorInterface $aggregator = null)
    {
        if (null === $aggregator) {
            $aggregator = new SimpleAggregator();
        }

        $this->aggregator = $aggregator;
    }

    /**
     * sets output interface.
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Add a function to the benchmark.
     *
     * @param callable|BenchmarkInterface $test
     * @param string                      $title
     */
    public function addBenchmark($test, $title): void
    {
        if (!$test instanceof \Closure && !$test instanceof BenchmarkInterface) {
            throw new \InvalidArgumentException('Test must be closure or implement TestInterface');
        }

        $this->tests[] = $test;
        $this->output->addTest($title);
    }

    /**
     * Plots the graph for all added tests.
     *
     * @param bool $keepAlive
     */
    public function run($keepAlive = false): void
    {
        for ($r = 1; $r <= $this->repetitions; ++$r) {
            foreach ($this->input as $i) {
                foreach ($this->tests as $index => $test) {
                    $this->bench($test, $i, $index);
                }

                $this->output->update($this->aggregator, $i);
            }

            if ($this->show_table_pass) {
                $this->showTableData($this->output->getTestsTitles(), $this->aggregator->getData(), $r, false);
            }
        }

        $this->output->finalize($this->aggregator, $i);

        if ($this->show_table_final) {
            $this->showTableData($this->output->getTestsTitles(), $this->aggregator->getData(), 0, true);
        }

        if ($keepAlive) {
            // Wait for user input to close
            echo 'Press enter to quit.';
            fgets(STDIN);
        }
    }

    /**
     * @param array $input
     */
    public function setInput(array $input): void
    {
        $this->input = $input;
    }

    /**
     * @param $repetitions
     */
    public function setRepetitions($repetitions): void
    {
        $this->repetitions = $repetitions;
    }

    private function bench($benchFunction, $i, $index): void
    {
        if ($benchFunction instanceof BenchmarkInterface) {
            $benchFunction->setUp($i);
            Timer::start();
            $benchFunction->execute();
            $time = Timer::stop();
        } else {
            Timer::start();
            $benchFunction($i);
            $time = Timer::stop();
        }

        $this->aggregator->push($i, $index, $time);
    }

    public function setShowRankingTable(bool $on_final = true, bool $every_pass = false): void
    {
        $this->show_table_final = true;
        $this->show_table_pass = $every_pass;
    }

    private function showTableData($titles, $data, $passage, $final = true): void
    {
        if (false === $final) {
            echo "\e[31mPartial Rank #" . $passage . "\e[0m" . PHP_EOL;
        } else {
            echo "\e[33mFinal Rank \e[0m" . PHP_EOL;
        }

        $table = new ConsoleTable();
        $table->addHeader('Test');
        $table->addHeader('Avg');
        $table->addHeader('Min');
        $table->addHeader('Max');
        $table->addHeader('Std');
        $table->addHeader('Range');


        $ranked = [];
        foreach ($titles as $titleIndex => $title) {
            $ranked[$title] = [];
            foreach ($data as $rowIndex => $rowData) {
                $ranked[$title][$rowIndex] = $rowData[$titleIndex];
            }

            $avg = array_sum($ranked[$title]) / count($ranked[$title]);

            $min = min($ranked[$title]);
            $max = max($ranked[$title]);

            $std = Math::standardDeviation($ranked[$title]);
            $range = $max - $min;
            $ranked[$title] = [
                $title,
                number_format($avg, 10),
                number_format($min, 10),
                number_format($max, 10),
                number_format($std, 10),
                number_format($range, 10),
            ];
        }

        uasort($ranked, function ($a, $b) {
            if ($a[1] === $b[1]) {
                return 0;
            }

            return ($a[1] < $b[1]) ? -1 : 1;
        });

        foreach ($ranked as $row) {
            $table->addRow($row);
        }

        $table->display();
    }
}
