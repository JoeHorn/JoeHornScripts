<?php
if ( $argc < 3 ) {
  echo "Usage: {$argv[0]} TEST_ROUND URL [ Y | N ]" . PHP_EOL;
  die();
}
class Tester {
    private $_curlHandler = null;

    function exec($testRound, $url, $persistent = false) {
        $testRound = intval($testRound);
        if ( 3 > $testRound ) {
            $testRound = 3;
        }
        $time = array(
            'first' => array(
                'total' => array(),
                'namelookup' => array(),
                'connect' => array(),
                'pretransfer' => array(),
                'starttransfer' => array(),
            ),
            'others' => array(
                'total' => array(),
                'namelookup' => array(),
                'connect' => array(),
                'pretransfer' => array(),
                'starttransfer' => array(),
            ),
        );
        for( $i = 0; $i < $testRound; $i++ ) {
            if ( !$persistent || is_null($this->_curlHandler) ) {
                $this->_curlHandler = curl_init();
            }
            curl_setopt_array($this->_curlHandler, array(
                CURLOPT_URL => $url,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_RETURNTRANSFER => true,
            ));
            $result = curl_exec($this->_curlHandler);
            $header = curl_getinfo($this->_curlHandler);

            $col = ( 0 == $i ) ? 'first' : 'others';

            $time[$col]['total'][] = $header['total_time'];
            $time[$col]['namelookup'][] = $header['namelookup_time'];
            $time[$col]['connect'][] = $header['connect_time'];
            $time[$col]['pretransfer'][] = $header['pretransfer_time'];
            $time[$col]['starttransfer'][] = $header['starttransfer_time'];

            if ( !$persistent ) {
                curl_close($this->_curlHandler);
            }
        }
        echo "== 1st. Round ==" . PHP_EOL;
        echo "Name Lookup :\t" . $time['first']['namelookup'][0] . PHP_EOL;
        echo "Connect :\t" . $time['first']['connect'][0] . PHP_EOL;
        echo "Pre-Transfer :\t" . $time['first']['pretransfer'][0] . PHP_EOL;
        echo "Name Lookup :\t" . $time['first']['starttransfer'][0] . PHP_EOL;
        echo "Total :\t\t" . $time['first']['total'][0] . PHP_EOL;

        $i--;
        echo "== Other (Sum for $i rounds.) ==" . PHP_EOL;
        echo "Name Lookup :\t" . array_sum($time['others']['namelookup']) . PHP_EOL;
        echo "Connect :\t" . array_sum($time['others']['connect']) . PHP_EOL;
        echo "Pre-Transfer :\t" . array_sum($time['others']['pretransfer']) . PHP_EOL;
        echo "Name Lookup :\t" . array_sum($time['others']['starttransfer']) . PHP_EOL;
        echo "Total :\t\t" . array_sum($time['others']['total']) . PHP_EOL;

    }
}

$t = new Tester();
$t->exec($argv[1], $argv[2], ('Y' == $argv[3]));
