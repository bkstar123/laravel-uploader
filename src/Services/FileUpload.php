<?php
/**
 * FileUpload service
 *
 * @author: tuanha
 * @last-mod: 16-05-2019
 */
namespace Bkstar123\LaravelUploader\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Bkstar123\LaravelUploader\Abstracts\FileUploadAbstract;

class FileUpload extends FileUploadAbstract
{
    /**
     * @var $uploadError
     */
    public $uploadError;

    /**
     * Inject dependencies & initialize default settings
     *
     */
    public function __construct()
    {
        $this->initializeDefaults();
    }

    /**
     * Validate the uploaded file for extension & size
     *
     * @return bool
     */
    protected function uploadValidate(UploadedFile $uploadedFile, $settings)
    {
        if (!$uploadedFile->isValid()) {
            $this->uploadError = $uploadedFile->getErrorMessage();
            return false;
        }

        if (!in_array($this->getExtension($uploadedFile), $settings['allowedExtensions'])) {
            $this->uploadError = 'The extension ' . $this->getExtension($uploadedFile) . " is not allowed";
            return false;
        }

        if ($this->getFileSize($uploadedFile) > $settings['maxFileSize']) {
            $this->uploadError = "The file size exceeds the max value set by the application";
            return false;
        }

        return true;
    }

    /**
     * Return the file extension as extracted from the origin file name
     *
     * @param Illuminate\Http\UploadedFile $uploadedFile
     *
     * @return string
     */
    protected function getExtension(UploadedFile $uploadedFile)
    {
        return $uploadedFile->getClientOriginalExtension();
    }

    /**
     * Generate an unique path for storing file from the filename
     *
     * @param Illuminate\Http\UploadedFile $uploadedFile
     *
     * @return string
     */
    protected function generatePath(UploadedFile $uploadedFile)
    {
        $originName = $this->getOriginName($uploadedFile);
        $uniqueString = uniqid(rand(), true)."_".$originName."_".getmypid()."_".gethostname()."_".time();
        return md5($uniqueString);
    }

    /**
     * Return the original filename
     *
     * @param Illuminate\Http\UploadedFile $uploadedFile
     *
     * @return string
     */
    protected function getOriginName(UploadedFile $uploadedFile)
    {
        return $uploadedFile->getClientOriginalName();
    }

    /**
     * Return the file size
     *
     * @param Illuminate\Http\UploadedFile $uploadedFile
     *
     * @return double
     */
    protected function getFileSize(UploadedFile $uploadedFile)
    {
        return $uploadedFile->getClientSize();
    }

    /**
     * Physically store the  uploaded file
     *
     * @param Illuminate\Http\UploadedFile $uploadedFile
     * @param array $settings
     *
     * @return array
     */
    protected function storeFile(UploadedFile $uploadedFile, $settings)
    {
        $subDirectory = $this->generatePath($uploadedFile);

        $storeLocation = $settings['directory'].DIRECTORY_SEPARATOR.$subDirectory;

        $path = $uploadedFile->store($storeLocation, $settings['disk']);

        $url = Storage::disk($settings['disk'])->url($path);

        return [
            'filename' => $this->getOriginName($uploadedFile),
            'path' => $path,
            'url' => $url,
            'disk' => $settings['disk']
        ];
    }
}
