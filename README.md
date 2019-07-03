# laravel-uploader    
Provide a Laravel backend for handling file upload  

## 1.Requirements  

It is recommended to install this package with PHP version 7.1.3+ and Laravel Framework version 5.5+   

## 2.Installation  
    composer require bkstar123/laravel-uploader

You can upload files to the backend either via a normal form submit or via AJAX calls (built by yourself or any third party plugins).  

**Note**: 
- Form submit supports only single file upload. For multiple files upload, you should use AJAX to send files in parallel requests   

- The package also includes a frontend fileinput plugin from Krajee for quickly demonstrating the AJAX uploading. The assets included in the package are barely minimum and may not be up to date. You should consult http://plugins.krajee.com/file-input for the latest version and more advanced use cases of the plugin  

- The Krajee plugin's full version can be installed via ```npm install bootstrap-fileinput```  

- In order to use the included frontend assets, run
```php artisan vendor:publish --tag=bkstar123_fileupload.assets```  

## 3.Usage

### 3.1 In Controller

Firstly, you typehint ```Bkstar123\LaravelUploader\Contracts\FileUpload``` in a controller constructor to inject a FileUpload service.  
```php
use Bkstar123\LaravelUploader\Contracts\FileUpload;
-----------------
protected $fileupload;

public function __construct(FileUpload $fileupload)
{
    $this->fileupload = $fileupload;
}
```

Then, in the method which is supposed to handle a file upload request, you call  
```php
$this->fileupload->handle(Request $request, string $fieldname, array $uploadSettings)
```

Where:  
- **$request**: full Laravel request object  
- **$fieldname**: name of the file input field in your frontend  
- **$uploadSettings** (optional): inline settings to customize the behavior of the backend  

**$uploadSettings** is an asociative array with the following possible keys:   
- ```directory```: the root directory containing your uploaded files  
- ```disk```: the storage disk for file upload. Check Laravel official documentation for more details, e.g: ```public```, ```s3```  
- ```maxFileSize``` (in bytes): the maximum size of an uploaded file that can be accepted by the backend  
- ```allowedExtensions```: array of acceptable file extensions, e.g: ```['jpg', 'png', 'pdf']```  

The backend default settings are as follows:  
```
- 'directory': 'media'
- 'disk': 'public'
- 'maxFileSize':  50 MB
- 'allowedExtensions': 'png','jpg','jpeg','mp4','doc','docx','ppt','pptx','xls','xlsx','txt','pdf'
```

You can change these default settings by using the following environment variables in .env:  
- ```BKSTAR123_LARAVEL_UPLOADER_DEFAULT_DISK```
- ```BKSTAR123_LARAVEL_UPLOADER_DEFAULT_DIRECTORY```
- ```BKSTAR123_LARAVEL_UPLOADER_DEFAULT_MAX_FILE_SIZE``` (in bytes)

**Note**: The inline settings will overwrite the default ones  

If the upload succeeds,```$this->fileupload->handle(Request $request, string $fieldname, array $uploadSettings)``` will return the following data for being further persisted to database:  

```php
[
    'filename' => 'the original file name',
    'path' => 'path to file location relative to the disk storage',
    'url' => 'public url to access the file in browser',
    'disk' => 'name of storage disk'
]
```

If the uploaded file is not valid, then ```false``` will be returned and an error message will be set for ```$this->fileupload->uploadError```  

### 3.2 In frontend view

#### 3.2.1 Normal form upload
(Only support single file upload)  

**Example**:  
```html
<form action="/upload" method="POST" role="form" enctype="multipart/form-data">
    @csrf()
    <input class="form-control" type="file" name="photo" id="photo" />
    <input type="submit" name="submit" value="Upload" />
</form>
```

#### 3.2.2 AJAX uploading using default frontend assets from Krajee

(Support sending multiple files in parallel requests)  

**Example**:  
```html
<!DOCTYPE html>
<html>
<head>
    <title>File Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
    <script src="{{ mix('/js/app.js') }}"></script>

    <!-- You must embed fileinput CSS and Javascript as follows -->
    <script src="/vendor/fileupload/js/fileinput/fileinput.min.js"></script>
    <link href="/vendor/fileupload/css/fileinput/fileinput.min.css" rel="stylesheet">
</head>
<body>

<input class="form-control" type="file" name="photo" id="photo" multiple />


<script type="text/javascript">
    $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("#photo").fileinput(
            {
                uploadUrl: '/upload',
                maxFileCount: 5,
                maxFileSize: 10420, //in KBs
                allowedFileExtensions: ['jpg', 'png', 'pdf'],
                previewFileType:'any',
                showUpload: false
            }
        ).on("filebatchselected", function(event, files) {
            $("#photo").fileinput("upload");  
        });
    });
</script>
</body>
</html>
```

**Note**: When using Krajee fileinput plugin, you must return a json response from the controller method which handles the upload request as follows:  

```php
if ($path = $this->fileupload->handle($request, 'photo', $uploadSettings)) {
    // Persist $path data to database
    return json_encode([]);
}

return json_encode([
    'error' => $this->fileupload->uploadError
]);
```

#### 3.2.3 AJAX uploading using @bkstar18/jquery-ajax-uploader plugin (***written by myself***)

You can check its full documentation at https://github.com/bkstar123/jquery-ajax-uploader  

**Example use**:

***a) Installation***
- ```npm install --save-dev @bkstar18/jquery-ajax-uploader```  

- In ```resources/js/bootstrap.js```, place the following line:  
```javascript
try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap'); 
    require('@bkstar18/jquery-ajax-uploader'); // Add this line
} catch (e) {}
```

- Then, compile your assets using ```laravel-mix```: ```npm run production```  

- Alternatively, if you do not want to bundle this plugin into the main app.js, you can place the following line in ```webpack.mix.js```:  
```javascript
mix.js('resources/js/app.js', 'public/js')
   .copy('node_modules/@bkstar18/jquery-ajax-uploader/dist/bkstar123-ajax-uploader.min.js', 'public/js/bkstar123-ajax-uploader.min.js') // Add this line
   .sass('resources/sass/app.scss', 'public/css');
```

Then, include ```<script src="/js/bkstar123-ajax-uploader.min.js"></script>``` in any view where you want to use the plugin. Remember to load JQuery before using the plugin.  

***b) In HTML section***  
```html
<div class="form-group">
    <label for="image-upload">Upload Images</label>
    <input type="file" class="form-control" name="image" id="image-upload" multiple>
    <div class="gallery" id="gallery"></div>
</div>
```

***c) In Javascript section***  
```javascript
$(document).ready(function () {
    $('#image-upload').bkstar123_ajaxuploader({
        allowedExtensions: ['png','jpg','jpeg'],
        batchSize: 5,
        outerClass: 'col-md-12',
        uploadUrl: '/api/upload',
        beforeSend: (xhr) => {
            xhr.setRequestHeader('X-AUTHOR', 'TUANHA');
        },
        onResponse: (response) => {
            let res = JSON.parse(response)
            $('#gallery').append(`<img id=${res.data.filename} src="${res.data.url}" width="50px">`);
        }
    });
});
```

***d) In Laravel Controller method***  
```php
public function upload(Request $request, FileUpload $fileupload)
{
    $data = $fileupload->handle($request, 'image', ['allowedExtensions' => ['jpg', 'png', 'jpeg']]);
    if (!$data) {
        return response()->json(['error' => $fileupload->uploadError], 422);
    }
    // Saving data to database
    return response()->json(['success' => "{$data['filename']} has been successfully uploaded", 'data' => $data], 200);
}
```

**Note**:  
You should make sure that the page html layout has the following ```<meta>``` tag:  
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

Otherwise, the request may be blocked by Laravel by default. See more details at https://laravel.com/docs/5.8/csrf#csrf-x-csrf-token.  

The plugin automatically checks the existence of this ```<meta>``` tag, get its content and associate ```X-CSRF-TOKEN``` header with the uploading request.  
 
Alternatively, ***if and only if*** this ```<meta>``` tag does not exist (maybe you do not want to use, or for somewhat reasons), then you can include ```X-CSRF-TOKEN``` request header before sending files to server via ```beforeSend(xhr)``` hook as follows:  

```javascript
$(document).ready(function () {
    $('#image-upload').bkstar123_ajaxuploader({
        ...
        beforeSend: (xhr) => {
            xhr.setRequestHeader('X-CSRF-TOKEN', {!! json_encode(csrf_token()) !!});
        },
        ...
    });
});
```

### 3.3 Physically remove an uploaded file

You can physically delete an uploaded file as following example:    
```php
$this->fileupload->delete($photo->disk, $photo->path)
```

In this example, the **photos** table must have ```disk``` and ```path``` columns to persist a ```photo``` instance.  

You should physically remove an uploaded file only after deleting its relevent record in the database.    