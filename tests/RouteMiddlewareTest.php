<?php

namespace Tapp\LaravelUppyS3MultipartUpload\Tests;

use Illuminate\Auth\GenericUser;

class RouteMiddlewareTest extends TestCase
{
    public function test_a_guest_cannot_start_a_multipart_upload()
    {
        $this->postJson('/s3/multipart', ['filename' => 'video.mp4', 'type' => 'video/mp4'])
            ->assertUnauthorized();

        $this->assertSame(0, $this->s3CallCount(), 'No S3 call may be made for a guest');
    }

    public function test_a_guest_cannot_get_a_presigned_part_url()
    {
        $this->getJson('/s3/multipart/test-upload-id/1?key=video.mp4')
            ->assertUnauthorized();
    }

    public function test_a_guest_cannot_list_complete_or_abort_an_upload()
    {
        $this->getJson('/s3/multipart/test-upload-id?key=video.mp4')->assertUnauthorized();
        $this->postJson('/s3/multipart/test-upload-id/complete?key=video.mp4', ['parts' => []])->assertUnauthorized();
        $this->deleteJson('/s3/multipart/test-upload-id?key=video.mp4')->assertUnauthorized();

        $this->assertSame(0, $this->s3CallCount(), 'No S3 call may be made for a guest');
    }

    public function test_a_logged_in_user_can_start_an_upload_and_presign_a_part()
    {
        $this->actingAs(new GenericUser(['id' => 1]));

        $key = $this->postJson('/s3/multipart', ['filename' => 'video.mp4', 'type' => 'video/mp4'])
            ->assertOk()
            ->assertJsonPath('uploadId', 'test-upload-id')
            ->json('key');

        $this->assertSame('CreateMultipartUpload', $this->s3->getLastCommand()->getName());
        $this->assertSame('test-bucket', $this->s3->getLastCommand()['Bucket']);

        $url = $this->getJson('/s3/multipart/test-upload-id/1?key='.$key)
            ->assertOk()
            ->json('url');

        $this->assertStringContainsString('X-Amz-Signature=', $url);
    }
}
