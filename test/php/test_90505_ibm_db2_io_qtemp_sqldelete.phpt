--TEST--
XML i Toolkit: IBM_DB2 inout SQL - qtemp delete rows
--SKIPIF--
<?php require_once('skipifdb2.inc'); ?>
--FILE--
<?php
// see connection.inc param details ...
require_once('connection.inc');

// -------------
// call IBM i
// -------------
if ($i5persistentconnect) $conn = db2_pconnect($database,$user,$password);
else $conn = db2_connect($database,$user,$password);
if (!$conn) die("Bad connect: $database,$user");
$stmt = db2_prepare($conn, "call $procLib.iPLUG512K(?,?,?,?)");
if (!$stmt) die("Bad prepare: ".db2_stmt_errormsg());
$clobIn = getxml();
$clobOut = "";
$ret=db2_bind_param($stmt, 1, "ipc", DB2_PARAM_IN);
$ret=db2_bind_param($stmt, 2, "ctl", DB2_PARAM_IN);
$ret=db2_bind_param($stmt, 3, "clobIn", DB2_PARAM_IN);
$ret=db2_bind_param($stmt, 4, "clobOut", DB2_PARAM_OUT);
$ret=db2_execute($stmt);
if (!$ret) die("Bad execute: ".db2_stmt_errormsg());
// -----------------
// output processing
// -----------------
// dump raw XML (easy test debug)
// var_dump($clobOut);
// xml check via simplexml vs. expected results
$xmlobj = simplexml_load_string($clobOut);
if (!$xmlobj) die("Bad XML returned");
// -----------------
// output row data
// -----------------
$format = "%s %'.15.15s\n";
$row = $xmlobj->xpath('/script/sql/rowcount');
printf ($format, "pre rollback changed",(string)$row[0]);
if ((string)$row[0] != 3) die("pre rollback bad\n");
// good
echo "Success\n";

function getxml() {
$clob = <<<ENDPROC
<?xml version='1.0'?>
<script>
<sql>
<free/>
<options options='noauto' autocommit='off'/>
<connect conn='myconn' options='noauto'/>
<query conn='myconn' stmt='mydelete'>DELETE FROM animalq WHERE weight > 10.0</query>
<rowcount stmt='mydelete' error='off'/>
<commit conn='myconn' action='rollback'/>
<free/>
</sql>
</script>
ENDPROC;
return $clob;
}
?>
--EXPECTF--
%s
Success

