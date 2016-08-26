<?php


function lm_getGlobalTimestamp() {
    if (!$GLOBALS['_beginTime']) {
        $GLOBALS['_beginTime'] = time();
    }
    return $GLOBALS['_beginTime'];
}

function lm_getRecordDateFormat($timestamp = null) {
    if ($timestamp == null) {
        $timestamp = wmd_getGlobalTimestamp();
    }
    return date('Y-m-d H:i:s', $timestamp);
}