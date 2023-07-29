<?php

namespace Tapp\LaravelUppyS3MultipartUpload\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class UppyS3MultipartController extends Controller
{
    protected $client;

    protected $bucket;

    public function __construct()
    {
        $this->client = Storage::disk('s3')->getClient();

        $this->bucket = config('filesystems.disks.s3.bucket');
    }

    /**
     * Encode URI.
     *
     * @param string $str
     *
     * @return string The encoded URI string
     */
    protected function encodeURIComponent(string $str)
    {
        if (!function_exists('encodeURIComponent')) {
            $revert = ['%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')', '%2F'=>'/'];

            return strtr(rawurlencode($str), $revert);
        }

        return encodeURIComponent($str);
    }

    /**
     * Add the preflight response header so it's possible to use the X-CSRF-TOKEN on Uppy request header.
     *
     * @return string JSON with 204 status no content
     */
    public function createPreflightHeader(Request $request)
    {
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-CSRF-TOKEN');

        return response()
            ->json([
                'message' => 'No content',
            ], 204);
    }

    /**
     * Create a multipart upload.
     *
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#createmultipartupload  S3 Syntax
     * @see https://docs.aws.amazon.com/AmazonS3/latest/API/API_CreateMultipartUpload.html  S3 Syntax
     * $result = $client->createMultipartUpload([
     *    'ACL' => 'private|public-read|public-read-write|authenticated-read|aws-exec-read|bucket-owner-read|bucket-owner-full-control',
     *    'Bucket' => '<string>', // REQUIRED
     *    'BucketKeyEnabled' => true || false,
     *    'CacheControl' => '<string>',
     *    'ContentDisposition' => '<string>',
     *    'ContentEncoding' => '<string>',
     *    'ContentLanguage' => '<string>',
     *    'ContentType' => '<string>',
     *    'ExpectedBucketOwner' => '<string>',
     *    'Expires' => <integer || string || DateTime>,
     *    'GrantFullControl' => '<string>',
     *    'GrantRead' => '<string>',
     *    'GrantReadACP' => '<string>',
     *    'GrantWriteACP' => '<string>',
     *    'Key' => '<string>', // REQUIRED
     *    'Metadata' => ['<string>', ...],
     *    'ObjectLockLegalHoldStatus' => 'ON|OFF',
     *    'ObjectLockMode' => 'GOVERNANCE|COMPLIANCE',
     *    'ObjectLockRetainUntilDate' => <integer || string || DateTime>,
     *    'RequestPayer' => 'requester',
     *    'SSECustomerAlgorithm' => '<string>',
     *    'SSECustomerKey' => '<string>',
     *    'SSECustomerKeyMD5' => '<string>',
     *    'SSEKMSEncryptionContext' => '<string>',
     *    'SSEKMSKeyId' => '<string>',
     *    'ServerSideEncryption' => 'AES256|aws:kms',
     *    'StorageClass' => 'STANDARD|REDUCED_REDUNDANCY|STANDARD_IA|ONEZONE_IA|INTELLIGENT_TIERING|GLACIER|DEEP_ARCHIVE|OUTPOSTS',
     *    'Tagging' => '<string>',
     *    'WebsiteRedirectLocation' => '<string>',
     * ]);
     * @see https://github.com/transloadit/uppy/blob/master/packages/%40uppy/aws-s3-multipart/src/index.js  Uppy call to this endpoint
     * return this.#client.post('s3/multipart', {
     *           filename: file.name,
     *           type: file.type,
     *           metadata,
     *       }, { signal }).then(assertServerError)
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string JSON with the uploaded parts
     */
    public function createMultipartUpload(Request $request)
    {
        $type = $request->input('type');
        $filenameRequest = $request->input('filename');
        $fileExtension = pathinfo($filenameRequest, PATHINFO_EXTENSION);
        $folder = config('uppy-s3-multipart-upload.s3.bucket.folder') ? config('uppy-s3-multipart-upload.s3.bucket.folder').'/' : '';
        $key = $folder.Str::ulid().'.'.$fileExtension;

        try {
            $result = $this->client->createMultipartUpload([
                'Bucket'             => $this->bucket,
                'Key'                => $key,
                'ContentType'        => $type,
                'ContentDisposition' => 'inline',
            ]);
        } catch (Throwable $exception) {
            return response()
                ->json([
                    'message' => $exception->getMessage(),
                ], $exception->getStatusCode());
        }

        return response()
            ->json([
                'uploadId' => $result['UploadId'],
                'key'      => $result['Key'],
            ]);
    }

    /**
     * List the multipart uploaded parts.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $uploadId
     *
     * @return string JSON with the uploaded parts
     */
    public function getUploadedParts(Request $request, string $uploadId)
    {
        $key = $request->input('key');

        $parts = $this->listPartsPage($key, $uploadId, 0);

        return response()
            ->json($parts);
    }

    /**
     * Get the uploaded parts. Retry the part if it's truncated.
     *
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#listparts  S3 Syntax
     * $result = $client->listParts([
     *           'Bucket' => '<string>', // REQUIRED
     *           'ExpectedBucketOwner' => '<string>',
     *           'Key' => '<string>', // REQUIRED
     *           'MaxParts' => <integer>,
     *           'PartNumberMarker' => <integer>,
     *           'RequestPayer' => 'requester',
     *           'UploadId' => '<string>', // REQUIRED
     *       ]);
     * @see https://github.com/transloadit/uppy/blob/master/packages/%40uppy/aws-s3-multipart/src/index.js  Uppy call to this endpoint
     * return this.#client.get(`s3/multipart/${uploadId}?key=${filename}`, { signal })
     *          .then(assertServerError)
     *
     * @param string $key
     * @param string $uploadId
     * @param int    $partIndex
     *
     * @return \Illuminate\Support\Collection
     */
    private function listPartsPage(string $key, string $uploadId, int $partIndex, $parts = null)
    {
        $parts = $parts ?? collect();

        $results = $this->client->listParts([
            'Bucket'           => $this->bucket,
            'Key'              => $key,
            'UploadId'         => $uploadId,
            'PartNumberMarker' => $partIndex,
        ]);

        if ($results['Parts']) {
            $parts = $parts->concat($results['Parts']);

            if ($results['IsTruncated']) {
                $results = $this->listPartsPage($key, $uploadId, $results['NextPartNumberMarker'], $parts);
                $parts = $parts->concat($results['Parts']);
            }
        }

        return $parts;
    }

    /**
     * Completes a multipart upload by assembling previously uploaded parts.
     *
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#completemultipartupload  S3 Syntax
     * $result = $client->completeMultipartUpload([
     *           'Bucket' => '<string>', // REQUIRED
     *           'ExpectedBucketOwner' => '<string>',
     *           'Key' => '<string>', // REQUIRED
     *           'MultipartUpload' => [
     *               'Parts' => [
     *                   [
     *                       'ETag' => '<string>',
     *                       'PartNumber' => <integer>,
     *                   ],
     *                   // ...
     *               ],
     *           ],
     *           'RequestPayer' => 'requester',
     *           'UploadId' => '<string>', // REQUIRED
     *       ]);
     * @see https://github.com/transloadit/uppy/blob/master/packages/%40uppy/aws-s3-multipart/src/index.js  Uppy call to this endpoint
     * return this.#client.post(`s3/multipart/${uploadIdEnc}/complete?key=${filename}`, { parts }, { signal })
     *           .then(assertServerError)
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $uploadId
     *
     * @return string
     */
    public function completeMultipartUpload(Request $request, string $uploadId)
    {
        $key = $this->encodeURIComponent($request->input('key'));

        $parts = $request->input('parts');

        // TODO: isValid Part
        // if (!Array.isArray(parts) || !parts.every(isValidPart)) {
        // return res.status(400).json({ error: 's3: `parts` must be an array of {ETag, PartNumber} objects.' })
        // }
        //
        // function isValidPart (part) {
        //     return part && typeof part === 'object' && typeof part.PartNumber === 'number' && typeof part.ETag === 'string'
        //}

        $result = $this->client->completeMultipartUpload([
            'Bucket'          => $this->bucket,
            'Key'             => $key,
            'UploadId'        => $this->encodeURIComponent($uploadId),
            'MultipartUpload' => [
                'Parts' => $parts,
            ],
        ]);

        $location = $result['Location'];

        return response()
            ->json([
                'location' => $location,
            ]);
    }

    /**
     * Aborts a multipart upload, deleting the uploaded parts.
     *
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#abortmultipartupload   S3 Syntax
     * $result = $client->abortMultipartUpload([
     *           'Bucket' => '<string>', // REQUIRED
     *           'ExpectedBucketOwner' => '<string>',
     *           'Key' => '<string>', // REQUIRED
     *           'RequestPayer' => 'requester',
     *           'UploadId' => '<string>', // REQUIRED
     *       ]);
     * @see https://github.com/transloadit/uppy/blob/master/packages/%40uppy/aws-s3-multipart/src/index.js  Uppy call to this endpoint
     * return this.#client.delete(`s3/multipart/${uploadIdEnc}?key=${filename}`, undefined, { signal })
     *           .then(assertServerError)
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $uploadId
     *
     * @return string JSON empty
     */
    public function abortMultipartUpload(Request $request, string $uploadId)
    {
        $key = $request->input('key');

        $result = $this->client->abortMultipartUpload([
            'Bucket'   => $this->bucket,
            'Key'      => $this->encodeURIComponent($key),
            'UploadId' => $this->encodeURIComponent($uploadId),
        ]);

        return response()
            ->json([]);
    }

    /**
     * Presign a URL for a part.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string JSON with the URL
     */
    public function signPartUpload(Request $request)
    {
        $url = $this->getSignedUrl($request, $request->route('partNumber'));

        return response()
            ->json([
                'url' => $url,
            ]);
    }

    /**
     * Get the presigned URL for a part.
     *
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#uploadpart  S3 Syntax
     * $result = $client->uploadPart([
     *           'Body' => <string || resource || Psr\Http\Message\StreamInterface>,
     *           'Bucket' => '<string>', // REQUIRED
     *           'ContentLength' => <integer>,
     *           'ContentSHA256' => '<string>',
     *           'ExpectedBucketOwner' => '<string>',
     *           'Key' => '<string>', // REQUIRED
     *           'PartNumber' => <integer>, // REQUIRED
     *           'RequestPayer' => 'requester',
     *           'SSECustomerAlgorithm' => '<string>',
     *           'SSECustomerKey' => '<string>',
     *           'SSECustomerKeyMD5' => '<string>',
     *           'SourceFile' => '<string>',
     *           'UploadId' => '<string>', // REQUIRED
     *       ]);
     * @see https://github.com/transloadit/uppy/blob/master/packages/%40uppy/aws-s3-multipart/src/index.js  Uppy call to this endpoint
     * return this.#client.get(`s3/multipart/${uploadId}/${partNumber}?key=${filename}`, { signal })
     *           .then(assertServerError)
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $partNumber
     *
     * @return string
     */
    public function getSignedUrl(Request $request, int $partNumber)
    {
        $key = $this->encodeURIComponent($request->input('key'));

        $command = $this->client->getCommand('UploadPart', [
            'Bucket'     => $this->bucket,
            'Key'        => $key,
            'UploadId'   => $request->route('uploadId'),
            'PartNumber' => (int) $partNumber,
        ]);

        $result = $this->client->createPresignedRequest($command, config('uppy-s3-multipart-upload.s3.presigned_url.expiry_time'));

        return (string) $result->getUri();
    }
}
