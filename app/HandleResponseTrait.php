<?php

namespace App;

trait HandleResponseTrait
{
    public function handleResponse($status, $msg, $errors, $data, $notes, $statusCode = 200)
    {
      return response()->json([
        "status" => $status,
        "message" => $msg,
        "errors" => $errors,
        "data" => $data,
        "notes" => $notes
      ], $statusCode);
    }
}
