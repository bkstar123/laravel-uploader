<?php
/**
 * FileUpload contract
 *
 * @author: tuanha
 * @last-mod: 15-06-2019
 */
namespace Bkstar123\LaravelUploader\Contracts;

use Illuminate\Http\Request;

interface FileUpload
{

    /**
     * Handle the uploaded file
     *
     * @param Illuminate\Http\Request $request
     * @param string $fieldName
     * @param array $settings
     *
     * @return mixed $path|false
     */
    public function handle(Request $request, $fieldName, $settings = []);
}
