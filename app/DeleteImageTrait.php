<?php

namespace App;
use Illuminate\Support\Facades\File;

trait DeleteImageTrait
{

    public function deleteFile($path)
    {
        // Read the file path from the request
        $filepath = $path;

        // Check if the file exists
        if (File::exists($filepath)) {
            // Delete the file
            File::delete($filepath);

        }

    }
}
