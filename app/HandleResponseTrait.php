<?php

namespace App;

trait HandleResponseTrait
{
    public function handleResponse($status, $msg, $errors, $data, $notes)
    {
      return response()->json([
        "status" => $status,
        "message" => $msg,
        "errors" => $errors,
        "data" => $data,
        "notes" => $notes
      ]);
    }
}
