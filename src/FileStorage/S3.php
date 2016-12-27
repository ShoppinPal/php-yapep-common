<?php

namespace ShoppinPal\YapepCommon\FileStorage;

use Aws\S3\S3Client;
use Psr\Http\Message\StreamInterface;
use YapepBase\Config;

class S3
{

    const ACL_PRIVATE = 'private';

    const ACL_PUBLIC_READ = 'public-read';

    const ACL_PUBLIC_READ_WRITE = 'public-read-write';

    const ACL_AUTHENTICATED_READ = 'authenticated-read';

    const ACL_AWS_EXEC_READ = 'aws-exec-read';

    const ACL_BUCKET_OWNER_READ = 'bucket-owner-read';

    const ACL_BUCKET_OWNER_FULL_CONTROL = 'bucket-owner-full-control';

    /** @var S3Client */
    protected $s3Client;

    /** @var string */
    protected $bucketName;

    public function __construct($configName)
    {
        $config       = Config::getInstance();
        $region       = $config->get('commonResource.s3.' . $configName . '.region', '');
        $clientConfig = [
            'version'     => $config->get('commonResource.s3.' . $configName . '.version', 'latest'),
            'credentials' => [
                'key'    => $config->get('commonResource.s3.' . $configName . '.accessKeyId'),
                'secret' => $config->get('commonResource.s3.' . $configName . '.accessSecret'),
            ],
        ];

        $this->bucketName = $config->get('commonResource.s3.' . $configName . '.bucketName');

        if ($region) {
            $clientConfig['region'] = $region;
        }

        $this->s3Client = new S3Client($clientConfig);
    }

    /**
     * @param string                          $key
     * @param string|resource|StreamInterface $body
     * @param string                          $acl
     * @param string                          $contentType
     * @param int|string|\DateTime            $expires
     *
     * @return void
     */
    public function putObject($key, $body, $acl = null, $contentType = null, $expires = null)
    {
        $args = [
            'Bucket' => $this->bucketName,
            'Key'    => $key,
            'Body'   => $body,
        ];

        if ($acl) {
            $args['ACL'] = $acl;
        }

        if ($contentType) {
            $args['ContentType'] = $contentType;
        }

        if ($expires) {
            $args['Expires'] = $expires;
        }

        $this->s3Client->putObject($args);
    }

    /**
     *
     *
     * @param string               $key
     * @param string               $acl
     * @param string               $contentType
     * @param int|string|\DateTime $expires
     *
     * @return void
     */
    public function createMultipartUpload($key, $acl = null, $contentType = null, $expires = null)
    {
        $args = [
            'Bucket' => $this->bucketName,
            'Key'    => $key,
        ];

        if ($acl) {
            $args['ACL'] = $acl;
        }

        if ($contentType) {
            $args['ContentType'] = $contentType;
        }

        if ($expires) {
            $args['Expires'] = $expires;
        }

        $this->s3Client->putObject($args);

    }

    /**
     * @param string $key
     * @param string $uploadId
     *
     * @return void
     */
    public function completeMultipartUpload($key, $uploadId)
    {
        $this->s3Client->completeMultipartUpload([
            'Bucket'   => $this->bucketName,
            'Key'      => $key,
            'UploadId' => $uploadId,
        ]);
    }

    /**
     * @param string $key
     * @param string $uploadId
     *
     * @return void
     */
    public function abortMultipartUpload($key, $uploadId)
    {
        $this->s3Client->abortMultipartUpload([
            'Bucket'   => $this->bucketName,
            'Key'      => $key,
            'UploadId' => $uploadId,
        ]);
    }

    /**
     *
     *
     * @param string                          $key
     * @param string                          $uploadId
     * @param int                             $partNumber
     * @param string|resource|StreamInterface $body
     *
     * @return void
     */
    public function uploadPart($key, $uploadId, $partNumber, $body)
    {
        $this->s3Client->uploadPart([
            'Bucket'     => $this->bucketName,
            'Key'        => $key,
            'UploadId'   => $uploadId,
            'PartNumber' => $partNumber,
            'Body'       => $body,
        ]);
    }

}
