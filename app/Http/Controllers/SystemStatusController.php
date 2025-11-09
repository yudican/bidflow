<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SystemStatusController extends Controller
{
    public function getCommitStatus()
    {
        $filePath = '/home/commit.txt';

        try {
            $commitId = file_get_contents($filePath);
            return response()->json(['commit_id' => trim($commitId)]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to read commit status'], 500);
        }
    }

    public function deleteUpdateFile()
    {
        $filePath = '/tmp/update.txt';

        try {
            if (File::exists($filePath)) {
                File::delete($filePath);
                return response()->json(['message' => 'File deleted successfully']);
            } else {
                return response()->json(['message' => 'File not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete file'], 500);
        }
    }
}
