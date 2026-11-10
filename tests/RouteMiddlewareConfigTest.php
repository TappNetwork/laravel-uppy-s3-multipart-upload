<?php

namespace Tapp\LaravelUppyS3MultipartUpload\Tests;

class RouteMiddlewareConfigTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        // An app that deliberately allows guest uploads keeps that option.
        $app['config']->set('uppy-s3-multipart-upload.routes.middleware', ['web']);
    }

    public function test_the_route_middleware_can_be_configured()
    {
        $this->postJson('/s3/multipart', ['filename' => 'video.mp4', 'type' => 'video/mp4'])
            ->assertOk()
            ->assertJsonPath('uploadId', 'test-upload-id');

        $this->assertSame(1, $this->s3CallCount());
    }
}
