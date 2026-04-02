<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TestImageController extends Controller
{
    /**
     * Display the specified test question image.
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function show(string $filename)
    {
        // If the filename is an HTTPS URL, redirect to it
        if (str_starts_with($filename, 'https')) {
            return redirect($filename);
        }
        
        $disk = Storage::disk('public');
        
        if (!$disk->exists($filename)) {
            abort(404);
        }

        // Cache the image file contents on server side for 1 hour (3600 seconds)
        $imageData = Cache::remember("question_image_{$filename}", 3600, function () use ($disk, $filename) {
            return [
                'content' => $disk->get($filename),
                'mimeType' => $disk->mimeType($filename)
            ];
        });

        return response($imageData['content'])
            ->header('Content-Type', $imageData['mimeType']);
    }
}
