# Subtitle Google Translator
A php library to translate subtitle srt format with google translate.
## Installation
### Using Composer
It's recomended to install this library by [Composer](https://getcomposer.org/) :
```
composer require hsnfirdaus/subtitle-google-translator
```
### Manual Installation
You can just manually download this repository as zip and extract to your project directory and include the `src/SubtitleTranslator.php` file.
### Calling the class
You can call this sdk class like this :
```php
require __DIR__ . '/vendor/autoload.php';
$translator = new Hsnfirdaus\SubtitleTranslator($source_lang,$target_lang);
```
### Parameters on Calling
| Parameter       	| Type     | Default Value	| Details                     				 |
| ----------------	| -------- | -------------	| ------------------------------------------ |
| `$source_lang`	| `string` | auto 			| The source language code (en,id,da, e.t.c) |
| `$target_lang`	| `string` | id				| The target language code (en,id,da, e.t.c) |
### Response
The response of method on this class is raw srt.
### Note
Input subtitle type must be srt and output type will be srt to.
# Usage
- **[From Raw](#from-raw)**
  - [Parameters](#parameters)
- **[From File](#from-file)**
  - [Parameters](#parameters-1)

## From Raw
```php
$translator = new Hsnfirdaus\SubtitleTranslator();
$translated = $translator->fromRaw($raw_subtitle);
echo $translated;
```
### Parameters
| Parameter        | Type     | Default Value | Details                     |
| ---------------- | -------- | ------------- | --------------------------- |
| `$raw_subtitle`  | `string` | null          | The raw text from subtitle. |
## From File
```php
$translator = new Hsnfirdaus\SubtitleTranslator();
$translated = $translator->fromFile($file_path);
echo $translated;
```
### Parameters
| Parameter     | Type     | Default Value | Details                        |
| ------------- | -------- | ------------- | ------------------------------ |
| `$file_path`  | `string` | null          | The realpath of subtitle file. |