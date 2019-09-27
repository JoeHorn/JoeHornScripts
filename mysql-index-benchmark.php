<?php
/***********************************************************************************
 * Copyright (c) 2010, Joe Horn <joehorn@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgement:
 *    This product includes software developed by the Joe Horn <joehorn@gmail.com>.
 * 4. Neither the name of the Joe Horn <joehorn@gmail.com> nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 ***********************************************************************************/

$db_host = 'localhost';
$db_user = 'TEST_USERNAME';
$db_pass = 'TEST_PASSWORD';
$db_name = 'test';

$table_name     = 'test';
$data_rows      = 1000000;
$test_round     = 1000;
$test_id        = 10;

define( 'CREATE_TABLE'  , TRUE );
define( 'INSERT_DATA'   , TRUE );
define( 'TEST_SELECT'   , TRUE );
define( 'TEST_UPDATE'   , TRUE );

$pdo = new PDO(
                "mysql:host=$db_host;dbname=$db_name" ,
                $db_user , $db_pass ,
                array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" )
              );

function flush_cache() {
        global $pdo;

        $sql = "FLUSH QUERY CACHE";
        $sth = $pdo->prepare( $sql );
        $sth->execute();
}

function test( $sql , $flush_cache ) {
        global $pdo , $test_round;

        flush_cache();

        $total_time = 0;
        for ( $i = 0; $i < $test_round; $i++ ) {
                if ( $flush_cache ) {
                        flush_cache();
                }

                $begin_time = microtime( TRUE );
                $sth = $pdo->prepare( $sql );
                $sth->execute();
                $finish_time = microtime( TRUE );
                $total_time = $total_time + $finish_time - $begin_time;
        }
        return $total_time;
}

//Create table
if ( CREATE_TABLE ) {
        $sql = "CREATE TABLE `$table_name` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                             `username` VARCHAR(32) NOT NULL ,
                                             `password` CHAR(32) NOT NULL ,
                                             `index_col` INT NOT NULL ,
                                             INDEX (`index_col`) ,
                                             UNIQUE (`username`)
                                           ) ENGINE = MYISAM;";
        $sth = $pdo->prepare( $sql );
        $sth->execute();
}

//Insert data
if ( INSERT_DATA ) {
        for ( $i = 1; $i <= $data_rows; $i++ ) {
                $username_length = rand( 1 , 20 );

                $username = '';
                for ($j = 1; $j <= $username_length; $j++) {
                        $username .= chr( rand( 97, 122 ) );
                }
                $username .= $i;
                $password = md5( $username );
                $index_col = $i;
                $sql = "INSERT INTO `$table_name` ( `username`  , `password`  , `index_col` )
                                           VALUES ( '$username' , '$password' , '$index_col' );";

                $sth = $pdo->prepare( $sql );
                $sth->execute();
        }
}

if ( TEST_SELECT || TEST_UPDATE ) {
        // Get test data
        $sql = "SELECT * FROM `$table_name` WHERE `id` = '$test_id' LIMIT 1";
        $sth = $pdo->prepare( $sql );
        $sth->execute();
        $result = $sth->fetch( PDO::FETCH_ASSOC );

        $test_username  = $result['username'];
        $test_password  = $result['password'];
        $test_index_col = $result['index_col'];
}

//Start testing
if ( TEST_SELECT ) {
        $sql = "SELECT * FROM `$table_name` WHERE `id` = '$test_id'";
        $time = test( $sql , FALSE );
        echo "SELECT BY PK_COL WITH_QUERY_CACHE:              $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `id` = '$test_id'";
        $time = test( $sql , TRUE );
        echo "SELECT BY PK_COL WITHOUT_QUERY_CACHE:           $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `id` = '$test_id' LIMIT 1";
        $time = test( $sql , FALSE );
        echo "SELECT BY PK_COL LIMIT WITH_QUERY_CACHE:        $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `id` = '$test_id' LIMIT 1";
        $time = test( $sql , TRUE );
        echo "SELECT BY PK_COL LIMIT WITHOUT_QUERY_CACHE:     $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `username` = '$test_username'";
        $time = test( $sql , FALSE );
        echo "SELECT BY UNIQUE_COL WITH_QUERY_CACHE:          $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `username` = '$test_username'";
        $time = test( $sql , TRUE );
        echo "SELECT BY UNIQUE_COL WITHOUT_QUERY_CACHE:       $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `username` = '$test_username' LIMIT 1";
        $time = test( $sql , FALSE );
        echo "SELECT BY UNIQUE_COL LIMIT WITH_QUERY_CACHE:    $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `username` = '$test_username' LIMIT 1";
        $time = test( $sql , TRUE );
        echo "SELECT BY UNIQUE_COL LIMIT WITHOUT_QUERY_CACHE: $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `index_col` = '$test_index_col'";
        $time = test( $sql , FALSE );
        echo "SELECT BY INDEX_COL WITH_QUERY_CACHE:           $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `index_col` = '$test_index_col'";
        $time = test( $sql , TRUE );
        echo "SELECT BY INDEX_COL WITHOUT_QUERY_CACHE:        $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `index_col` = '$test_index_col' LIMIT 1";
        $time = test( $sql , FALSE );
        echo "SELECT BY INDEX_COL LIMIT WITH_QUERY_CACHE:     $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `index_col` = '$test_index_col' LIMIT 1";
        $time = test( $sql , TRUE );
        echo "SELECT BY INDEX_COL LIMIT WITHOUT_QUERY_CACHE:  $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `password` = '$test_password'";
        $time = test( $sql , FALSE );
        echo "SELECT BY COL WITH_QUERY_CACHE:                 $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `password` = '$test_password'";
        $time = test( $sql , TRUE );
        echo "SELECT BY COL WITHOUT_QUERY_CACHE:              $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `password` = '$test_password' LIMIT 1";
        $time = test( $sql , FALSE );
        echo "SELECT BY COL LIMIT WITH_QUERY_CACHE:           $time\n";

        $sql = "SELECT * FROM `$table_name` WHERE `password` = '$test_password' LIMIT 1";
        $time = test( $sql , TRUE );
        echo "SELECT BY COL LIMIT WITHOUT_QUERY_CACHE:        $time\n";
}

if ( TEST_UPDATE ) {
        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `id` = '$test_id'";
        $time = test( $sql , FALSE );
        echo "UPDATE BY PK_COL WITH_QUERY_CACHE:              $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `id` = '$test_id'";
        $time = test( $sql , TRUE );
        echo "UPDATE BY PK_COL WITHOUT_QUERY_CACHE:           $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `id` = '$test_id' LIMIT 1";
        $time = test( $sql , FALSE );
        echo "UPDATE BY PK_COL LIMIT WITH_QUERY_CACHE:        $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `id` = '$test_id' LIMIT 1";
        $time = test( $sql , TRUE );
        echo "UPDATE BY PK_COL LIMIT WITHOUT_QUERY_CACHE:     $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `username` = '$test_username'";
        $time = test( $sql , FALSE );
        echo "UPDATE BY UNIQUE_COL WITH_QUERY_CACHE:          $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `username` = '$test_username'";
        $time = test( $sql , TRUE );
        echo "UPDATE BY UNIQUE_COL WITHOUT_QUERY_CACHE:       $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `username` = '$test_username' LIMIT 1";
        $time = test( $sql , FALSE );
        echo "UPDATE BY UNIQUE_COL LIMIT WITH_QUERY_CACHE:    $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `username` = '$test_username' LIMIT 1";
        $time = test( $sql , TRUE );
        echo "UPDATE BY UNIQUE_COL LIMIT WITHOUT_QUERY_CACHE: $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `index_col` = '$test_index_col'";
        $time = test( $sql , FALSE );
        echo "UPDATE BY INDEX_COL WITH_QUERY_CACHE:           $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `index_col` = '$test_index_col'";
        $time = test( $sql , TRUE );
        echo "UPDATE BY INDEX_COL WITHOUT_QUERY_CACHE:        $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `index_col` = '$test_index_col' LIMIT 1";
        $time = test( $sql , FALSE );
        echo "UPDATE BY INDEX_COL LIMIT WITH_QUERY_CACHE:     $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `index_col` = '$test_index_col' LIMIT 1";
        $time = test( $sql , TRUE );
        echo "UPDATE BY INDEX_COL LIMIT WITHOUT_QUERY_CACHE:  $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `password` = '$test_password'";
        $time = test( $sql , FALSE );
        echo "UPDATE BY COL WITH_QUERY_CACHE:                 $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `password` = '$test_password'";
        $time = test( $sql , TRUE );
        echo "UPDATE BY COL WITHOUT_QUERY_CACHE:              $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `password` = '$test_password' LIMIT 1";
        $time = test( $sql , FALSE );
        echo "UPDATE BY COL LIMIT WITH_QUERY_CACHE:           $time\n";

        $sql = "UPDATE `$table_name` SET `password` = '$test_password' WHERE `password` = '$test_password' LIMIT 1";
        $time = test( $sql , TRUE );
        echo "UPDATE BY COL LIMIT WITHOUT_QUERY_CACHE:        $time\n";
}
?>
