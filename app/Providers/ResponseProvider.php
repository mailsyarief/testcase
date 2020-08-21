<?php

namespace App\Providers;


class ResponseProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public static function http($status, $message, $data, $code)
    {
        return response()->json([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ], $code);
    }
}
