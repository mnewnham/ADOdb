<?php

/**
 *  Unserialize session data manually. See PHPLens Issue No: 9821
 *
 * From Kerr Schere, to unserialize session data stored via ADOdb.
 * 1. Pull the session data from the db and loop through it.
 * 2. Inside the loop, you will need to urldecode the data column.
 * 3. After urldecode, run the serialized string through this function:
 */
function adodb_unserialize($serialized_string)
{
    $variables = array( );
    $a = preg_split("/(\w+)\|/", $serialized_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    for ($i = 0; $i < count($a); $i = $i + 2) {
        $variables[$a[$i]] = unserialize($a[$i + 1]);
    }
    return( $variables );
}

/**
 * Regenerate session id
 *
 * Thanks Joe Li. See PHPLens Issue No: 11487&x=1
 *
 * @since 4.61
 */
function adodb_session_regenerate_id()
{
    $conn = ADODB_Session::_conn();
    if (!$conn) {
        return false;
    }

    $old_id = session_id();
    if (function_exists('session_regenerate_id')) {
        session_regenerate_id();
    } else {
        session_id(md5(uniqid(rand(), true)));
        $ck = session_get_cookie_params();
        setcookie(session_name(), session_id(), false, $ck['path'], $ck['domain'], $ck['secure'], $ck['httponly']);
        //@session_start();
    }
    $new_id = session_id();
    $ok = $conn->Execute('UPDATE ' . ADODB_Session::table() . ' SET sesskey=' . $conn->qstr($new_id) . ' WHERE sesskey=' . $conn->qstr($old_id));

    /* it is possible that the update statement fails due to a collision */
    if (!$ok) {
        session_id($old_id);
        if (empty($ck)) {
            $ck = session_get_cookie_params();
        }
        setcookie(session_name(), session_id(), false, $ck['path'], $ck['domain'], $ck['secure'], $ck['httponly']);
        return false;
    }

    return true;
}

/**
 * Generate database table for session data.
 * @see PHPLens Issue No: 12280
 *
 * @return int 0 if failure, 1 if errors, 2 if successful.
 *
 * @author Markus Staab http://www.public-4u.de
 */
function adodb_session_create_table($schemaFile = null, $conn = null)
{
    // set default values
    if ($schemaFile === null) {
        $schemaFile = ADODB_SESSION . '/session_schema2.xml';
    }
    if ($conn === null) {
        $conn = ADODB_Session::_conn();
    }

    if (!$conn) {
        return 0;
    }

    $schema = new adoSchema($conn);
    $schema->ParseSchema($schemaFile);
    return $schema->ExecuteSchema();
}

/**
 * @deprecated for backwards compatibility only
 */
function adodb_sess_open($save_path, $session_name, $persist = true)
{
    return ADODB_Session::open($save_path, $session_name, $persist);
}

/**
 * @deprecated for backwards compatibility only
 */
function adodb_sess_gc($t)
{
    return ADODB_Session::gc($t);
}
