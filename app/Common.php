<?php

/**
 * Runs a query on a raw mysqli handle and returns false instead of throwing.
 *
 * CodeIgniter turns on mysqli exceptions for the whole request once it opens
 * its own connection, so a missing table would end in a 500 page instead of
 * the JSON the game client expects. The /connect endpoint and the server
 * settings view use plain mysqli, so they call this.
 *
 * @param  mysqli|null $conn
 * @return mysqli_result|false
 */
function db_query($conn, string $sql)
{
    if (! $conn instanceof mysqli) {
        return false;
    }

    try {
        $result = @mysqli_query($conn, $sql);
    } catch (mysqli_sql_exception $e) {
        return false;
    }

    return $result === false ? false : $result;
}
