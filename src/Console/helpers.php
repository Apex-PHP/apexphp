<?php

if (!function_exists("getInputType")) {
    function getInputType($sqlType)
    {
        $type = strtolower($sqlType);

        if (strpos($type, "int") !== false) {
            return "number";
        }
        if (
            strpos($type, "decimal") !== false ||
            strpos($type, "float") !== false ||
            strpos($type, "double") !== false
        ) {
            return "number";
        }
        if (strpos($type, "date") !== false) {
            return "date";
        }
        if (strpos($type, "time") !== false) {
            return "time";
        }
        if (strpos($type, "text") !== false) {
            return "textarea";
        }
        if (strpos($type, "email") !== false) {
            return "email";
        }

        return "text";
    }
}