<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TempMediaUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Generate a presigned URL for direct S3 upload.
     */
    public function getPresignedUrl(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'file_type' => 'required|string',
            'file_size' => 'required|integer|max:1073741824', // 1GB max
            'type' => 'required|in:image,video,document',
        ]);

        $user = Auth::user();

        // Generate unique file name
        $extension = pathinfo($request->file_name, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;

        // Determine path based on type
        $path = match($request->type) {
            'video' => 'temp/videos/' . $fileName,
            'image' => 'temp/images/' . $fileName,
            'document' => 'temp/documents/' . $fileName,
            default => 'temp/' . $fileName,
        };

        // Get the disk from media config
        $disk = config('media.disk', 's3');

        // Create temp upload record
        $tempUpload = TempMediaUpload::create([
            'user_id' => $user?->id,
            'file_name' => $request->file_name,
            'file_type' => $request->file_type,
            'file_size' => $request->file_size,
            's3_path' => $path,
            'disk' => $disk,
            'type' => $request->type,
            'status' => 'pending',
            'expires_at' => now()->addHours(24), // 24 hour expiry
        ]);

        try {
            // Generate presigned URL for PUT request
            $adapter = Storage::disk($disk)->getAdapter();

            // For Flysystem v3, access the S3 client using reflection
            $reflection = new \ReflectionClass($adapter);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setAccessible(true);
            $s3Client = $clientProperty->getValue($adapter);

            $bucket = config("filesystems.disks.{$disk}.bucket");

            $command = $s3Client->getCommand('PutObject', [
                'Bucket' => $bucket,
                'Key' => $path,
                'ContentType' => $request->file_type,
                'ACL' => 'public-read',
            ]);

            $presignedRequest = $s3Client->createPresignedRequest($command, '+60 minutes');
            $presignedUrl = (string) $presignedRequest->getUri();

            return response()->json([
                'presigned_url' => $presignedUrl,
                'temp_upload_id' => $tempUpload->id,
                's3_path' => $path,
                'expires_in' => 3600, // seconds
            ]);
        } catch (\Exception $e) {
            $tempUpload->markAsFailed($e->getMessage());

            return response()->json([
                'message' => 'Failed to generate presigned URL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm that the upload to S3 was successful.
     */
    public function confirmUpload(Request $request, $id)
    {
        $request->validate([
            's3_path' => 'required|string',
        ]);

        $tempUpload = TempMediaUpload::findOrFail($id);

        // Verify user owns this upload
        if ($tempUpload->user_id && Auth::id() !== $tempUpload->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Verify the file exists on S3
        try {
            $exists = Storage::disk($tempUpload->disk)->exists($request->s3_path);

            if (!$exists) {
                $tempUpload->markAsFailed('File not found on storage');
                return response()->json([
                    'message' => 'File verification failed',
                    'error' => 'File not found on storage',
                ], 400);
            }

            // Mark as confirmed
            $tempUpload->update([
                'status' => 'confirmed',
                's3_path' => $request->s3_path,
            ]);

            return response()->json([
                'message' => 'Upload confirmed successfully',
                'temp_upload' => $tempUpload,
            ]);
        } catch (\Exception $e) {
            $tempUpload->markAsFailed($e->getMessage());

            return response()->json([
                'message' => 'Upload confirmation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a temporary upload.
     */
    public function deleteTempUpload($id)
    {
        $tempUpload = TempMediaUpload::findOrFail($id);

        // Verify user owns this upload
        if ($tempUpload->user_id && Auth::id() !== $tempUpload->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Delete from storage if exists
            if ($tempUpload->s3_path && Storage::disk($tempUpload->disk)->exists($tempUpload->s3_path)) {
                Storage::disk($tempUpload->disk)->delete($tempUpload->s3_path);
            }

            // Delete record
            $tempUpload->delete();

            return response()->json([
                'message' => 'Upload deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete upload',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
