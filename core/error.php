<?php

class ErrorApp {

    private static $ErrorList = array(
        400 => "Bad Request",
        200 => "OK",
        404 => "Not Found",
        204 => " No Content"
    );

    public static function Err($error = 400, $msg = null) {
        http_response_code($error);
        echo json_encode( array(
                "errors" => array(
                    "code" => $error,
                    "error" => self::$ErrorList[$error],
                    "error_text" => $msg
                )
            ) 
        );
    }
}