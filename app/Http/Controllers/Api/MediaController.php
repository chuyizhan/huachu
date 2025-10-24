<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Generate presigned URL for direct S3 upload
     */
    public function getPresignedUrl(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string',
            'file_type' => 'required|string',
            'file_size' => 'required|integer|max:1073741824', // 1GB in bytes
            'type' => 'required|in:video,image',
        ]);

        $user = Auth::user();

        // Validate file type
        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'];
        $allowedImageMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        if ($request->type === 'video' && !in_array($request->file_type, $allowedVideoMimes)) {
            return response()->json(['error' => '不支持的视频格式'], 422);
        }

        if ($request->type === 'image' && !in_array($request->file_type, $allowedImageMimes)) {
            return response()->json(['error' => '不支持的图片格式'], 422);
        }

        // Generate unique file path
        $extension = pathinfo($request->file_name, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;
        $path = "uploads/{$request->type}s/" . date('Y/m/d') . "/{$fileName}";

        // Get S3 client
        $disk = Storage::disk('s3');
        $client = $disk->getClient();
        $bucket = config('filesystems.disks.s3.bucket');

        // Generate presigned URL (valid for 1 hour)
        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $path,
            'ContentType' => $request->file_type,
        ]);

        $presignedRequest = $client->createPresignedRequest($command, '+1 hour');
        $presignedUrl = (string) $presignedRequest->getUri();

        // Create temporary upload record
        $temporaryUpload = new \App\Models\TemporaryUpload();
        $temporaryUpload->user_id = $user->id;
        $temporaryUpload->type = $request->type;
        $temporaryUpload->s3_path = $path;
        $temporaryUpload->original_name = $request->file_name;
        $temporaryUpload->mime_type = $request->file_type;
        $temporaryUpload->size = $request->file_size;
        $temporaryUpload->save();

        return response()->json([
            'success' => true,
            'presigned_url' => $presignedUrl,
            'temp_upload_id' => $temporaryUpload->id,
            's3_path' => $path,
            'file_url' => Storage::disk('s3')->url($path),
        ]);
    }

    /**
     * Confirm that S3 upload completed successfully
     */
    public function confirmUpload(Request $request, $tempUploadId)
    {
        $request->validate([
            's3_path' => 'required|string',
        ]);

        $user = Auth::user();

        $temporaryUpload = \App\Models\TemporaryUpload::where('id', $tempUploadId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Verify file exists on S3
        if (!Storage::disk('s3')->exists($request->s3_path)) {
            return response()->json(['error' => '文件上传失败'], 422);
        }

        // Mark as confirmed
        $temporaryUpload->confirmed = true;
        $temporaryUpload->save();

        return response()->json([
            'success' => true,
            'temp_upload_id' => $temporaryUpload->id,
            'file_url' => Storage::disk('s3')->url($temporaryUpload->s3_path),
        ]);
    }

    /**
     * Upload a video file before post creation (Legacy - for backwards compatibility)
     * Returns media_id to be attached to post later
     */
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,mov,avi|max:1048576', // 1GB max
        ]);

        $user = Auth::user();

        // Create a temporary media model that will be attached to a post later
        // We'll use a temporary model class to store the file
        $temporaryUpload = new \App\Models\TemporaryUpload();
        $temporaryUpload->user_id = $user->id;
        $temporaryUpload->type = 'video';
        $temporaryUpload->save();

        // Add the video to media library
        $media = $temporaryUpload->addMedia($request->file('video'))
            ->toMediaCollection('videos');

        return response()->json([
            'success' => true,
            'media_id' => $media->id,
            'temp_upload_id' => $temporaryUpload->id,
            'file_name' => $media->file_name,
            'size' => $media->size,
            'url' => $media->getUrl(),
        ]);
    }

    /**
     * Upload an image file before post creation
     * Returns media_id to be attached to post later
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
        ]);

        $user = Auth::user();

        // Create a temporary media model
        $temporaryUpload = new \App\Models\TemporaryUpload();
        $temporaryUpload->user_id = $user->id;
        $temporaryUpload->type = 'image';
        $temporaryUpload->save();

        // Add the image to media library with responsive images
        $media = $temporaryUpload->addMedia($request->file('image'))
            ->withResponsiveImages()
            ->toMediaCollection('images');

        return response()->json([
            'success' => true,
            'media_id' => $media->id,
            'temp_upload_id' => $temporaryUpload->id,
            'file_name' => $media->file_name,
            'size' => $media->size,
            'url' => $media->getUrl(),
            'thumb' => $media->hasGeneratedConversion('thumb')
                ? $media->getUrl('thumb')
                : $media->getUrl(),
        ]);
    }

    /**
     * Delete a temporary upload (if user cancels)
     */
    public function deleteTemporaryUpload($id)
    {
        $user = Auth::user();

        $temporaryUpload = \App\Models\TemporaryUpload::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // This will also delete associated media files
        $temporaryUpload->delete();

        return response()->json([
            'success' => true,
            'message' => 'Temporary upload deleted successfully',
        ]);
    }
}
