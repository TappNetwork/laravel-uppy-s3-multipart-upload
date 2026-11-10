<?php

namespace Tapp\LaravelUppyS3MultipartUpload\Tests;

use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Orchestra\Testbench\TestCase as Orchestra;
use Tapp\LaravelUppyS3MultipartUpload\LaravelUppyS3MultipartUploadServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Stands in for the S3 API: records every command the controller sends
     * and answers with a fake upload, so no test touches the network.
     */
    protected MockHandler $s3;

    protected function getPackageProviders($app)
    {
        return [
            LaravelUppyS3MultipartUploadServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $this->s3 = new MockHandler();
        for ($i = 0; $i < 5; $i++) {
            $this->s3->append(fn (CommandInterface $command) => new Result([
                'UploadId' => 'test-upload-id',
                'Key'      => $command['Key'] ?? null,
            ]));
        }

        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
        $app['config']->set('session.driver', 'array');
        $app['config']->set('filesystems.disks.s3', [
            'driver'  => 's3',
            'key'     => 'test-key',
            'secret'  => 'test-secret',
            'region'  => 'us-east-1',
            'bucket'  => 'test-bucket',
            'handler' => $this->s3,
        ]);
    }

    /**
     * Number of S3 commands the controller has sent so far.
     */
    protected function s3CallCount(): int
    {
        return 5 - count($this->s3);
    }
}
