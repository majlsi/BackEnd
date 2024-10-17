<?php

namespace Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Lang;
use NcJoes\OfficeConverter\OfficeConverter;

class UploadHelper
{

    public static function uploadFile(Request $request, $fileName)
    {
        if ($request->hasFile($fileName)) {
            $file = $request->file($fileName);
            $path = $request->get('path');

            $destinationPath = public_path() . "/uploads" . $path;
            $name = preg_replace('/\s+/', '', $file->getClientOriginalName());
            $filename = time() . '_' . $name;
            if ($file->move($destinationPath, $filename)) {
                $filePath = "uploads" . $path . '/' . $filename;
                return $filePath;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public static function uploadPresentationNotes(Request $request, $fileName)
    {
        if ($request->hasFile($fileName)) {
            $file = $request->file($fileName);
            $path = $request->get('path');
            $filename = $request->get('file_name');

            $destinationPath = public_path() . "/uploads" . $path;
            // $name = preg_replace('/\s+/', '', $file->getClientOriginalName());
            if (file_exists($destinationPath . '/' . $filename)) {
                unlink($destinationPath . '/' . $filename);
            }
            if ($file->move($destinationPath, $filename)) {
                $filePath = "uploads" . $path . '/' . $filename;
                return $filePath;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public static function uploadFiles(Request $request, $fileName)
    {
        if ($request->hasFile($fileName)) {
            $files = $request->file($fileName);
            $path = $request->get('path');
            $filesPaths = [];
            $destinationPath = public_path() . "/uploads" . $path;
            foreach ($files as $key => $file) {
                $name = preg_replace('/\s+/', '', $file->getClientOriginalName());
                $filename = time() . '_' . $name;
                if ($file->move($destinationPath, $filename)) {
                    $filePath = "uploads" . $path . '/' . $filename;
                    $filesPaths[] = $filePath;
                } else {
                    return null;
                }
            }
            return $filesPaths;
        } else {
            return null;
        }
    }

    public static function message()
    {
        return array(
            'files.*.required' => ['message' => Lang::get('validation.custom.file.required', [], 'en')
                , 'message_ar' => Lang::get('validation.custom.file.required', [], 'ar')],

            'files.*.mimes' => ['message' => Lang::get('validation.custom.file.mimes', [], 'en')
                , 'message_ar' => Lang::get('validation.custom.file.mimes', [], 'ar')],

            'files.*.max' => ['message' => Lang::get('validation.custom.file.max', [], 'en')
                , 'message_ar' => Lang::get('validation.custom.file.max', [], 'ar')],
        );
    }

    public static function fileMessage()
    {
        return array(
            'file.required' => ['message' => Lang::get('validation.required', [], 'en')
                , 'message_ar' => Lang::get('validation.required', [], 'ar')],

            'file.mimes' => ['message' => Lang::get('validation.mimes', [], 'en')
                , 'message_ar' => Lang::get('validation.mimes', [], 'ar')],

            'file.max' => ['message' => Lang::get('validation.max.file', [], 'en')
                , 'message_ar' => Lang::get('validation.max.file', [], 'ar')],
        );
    }

    // public static function imageMessage()
    // {
    //     return array(
    //         'file.required' => ['message' => Lang::get('validation.required', [], 'en')
    //             , 'message_ar' => Lang::get('validation.required', [], 'ar')],

    //         'file.mimes' => ['message' => Lang::get('validation.mimes', [], 'en')
    //             , 'message_ar' => Lang::get('validation.mimes', [], 'ar')],

    //         'fil5e.maxi.file' => ['message' => Lang::get('validation.custom.profile_image.max', [], 'en')
    //             , 'message_ar' => Lang::get('validation.custom.profile_image.max', [], 'ar')],
    //     );
    // }

    public static function convertAttachmentsToImages($attachments)
    {
        $availableExtensions = ['doc', 'docx', 'odt', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf'];
        $imageExtensions = ['jpeg', 'jpg', 'png'];
        foreach ($attachments as $attachment) {
            $ext = pathinfo(public_path() . $attachment->attachment_url, PATHINFO_EXTENSION);
            $path = public_path() . '/uploads/attachments/' . $attachment->id;
            $filePath = public_path() . '/' . $attachment->attachment_url;
            $fileName = pathinfo(public_path() . '/' . $attachment->attachment_url, PATHINFO_FILENAME);
            if (in_array(strtolower($ext), $availableExtensions)) {
                if (File::isDirectory($path)) {
                    rename($path, $path . '_'.time());
                }
                File::makeDirectory($path, 0777, true, true);
                if ($ext != 'pdf') {
                    $fileTimestemp = explode("_", $fileName)[0];
                    $newFileName = $fileTimestemp . '_' . $attachment->id . '.' . $ext;
                    $newFilePath = public_path() . '/' . $newFileName;
                    rename($filePath, $newFilePath);
                    $pdfFilePath = UploadHelper::convertFileToPdf($newFilePath, $path, $fileName);
                    UploadHelper::convertPdfToImages($pdfFilePath, $path, $fileName, true);
                    rename($newFilePath, $filePath);
                } else {
                    $pdfFilePath = $filePath;
                    UploadHelper::convertPdfToImages($pdfFilePath, $path, $fileName);
                }
            } elseif (in_array(strtolower($ext), $imageExtensions)) {
                if (File::isDirectory($path)) {
                    rename($path, $path . '_'.time());
                }
                File::makeDirectory($path, 0777, true, true);
                \File::copy($filePath, "$path/$fileName.$ext");
            }

        }
    }

    public static function convertFileToPdf($filePath, $path, $fileName)
    {
        $converter = new OfficeConverter($filePath, $path, config('officeConverter.bin'), config('officeConverter.exportHome'));

        $result = $converter->convertTo($fileName . '.pdf');

        $pdfFilePath = "$path/$fileName.pdf";

        return $pdfFilePath;
    }

    public static function convertPdfToImages($pdfFilePath, $pathToSaveImage, $imageName, $deletePdf = false, $relativePath = null)
    {
        $pdf = new \Spatie\PdfToImage\Pdf($pdfFilePath);
        $pdf->setCompressionQuality(100);
        $pages = $pdf->getNumberOfPages();

        $imagesPaths = [];

        for ($i = 1; $i <= $pages; $i++) {
            $pdf->setPage($i)->saveImage("$pathToSaveImage/$imageName-$i.jpg");
            if ($relativePath != null) {

                $imagesPaths[] = "$relativePath/$imageName-$i.jpg";
            }

        }

        if ($deletePdf) {
            unlink($pdfFilePath);
        }

        if ($relativePath != null) {
            return $imagesPaths;
        }
    }

    public static function convertDocumentToImages($document)
    {
        $availableExtensions = ['doc', 'docx', 'odt', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf'];
        $imageExtensions = ['jpeg', 'jpg', 'png'];
        
        $ext = pathinfo(public_path() . $document->document_url, PATHINFO_EXTENSION);
        $path = public_path() . '/uploads/documents/' . $document->id;
        $filePath = public_path() . '/' . $document->document_url;
        $fileName = pathinfo(public_path() . '/' . $document->document_url, PATHINFO_FILENAME);
        if (in_array(strtolower($ext), $availableExtensions)) {
            if (File::isDirectory($path)) {
                rename($path, $path .  '_'.time());
            }
            File::makeDirectory($path, 0777, true, true);

            if ($ext != 'pdf') {
                $fileTimestemp = explode("_", $fileName)[0];
                $newFileName = $fileTimestemp . '_' . $document->id . '.' . $ext;
                $newFilePath = public_path() . '/' . $newFileName;
                rename($filePath, $newFilePath);
                $pdfFilePath = UploadHelper::convertFileToPdf($newFilePath, $path, $fileName);
                UploadHelper::convertPdfToImages($pdfFilePath, $path, $fileName, true);
                rename($newFilePath, $filePath);
            } else {
                $pdfFilePath = $filePath;
                UploadHelper::convertPdfToImages($pdfFilePath, $path, $fileName);
            }
        } elseif (in_array(strtolower($ext), $imageExtensions)) {
             if (File::isDirectory($path)) {
                rename($path, $path . '_'.time());
            }
            File::makeDirectory($path, 0777, true, true);
            \File::copy($filePath, "$path/$fileName.$ext");
        }
    } 

    public static function convertApprovalDocumentToImages($document)
    {
        $availableExtensions = ['doc', 'docx', 'odt', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf'];
        $imageExtensions = ['jpeg', 'jpg', 'png'];
        
        $ext = pathinfo(public_path() . $document->attachment_url, PATHINFO_EXTENSION);
        $path = public_path() . '/uploads/approvals/' . $document->id;
        $filePath = public_path() . '/' . $document->attachment_url;
        $fileName = pathinfo(public_path() . '/' . $document->attachment_url, PATHINFO_FILENAME);
        if (in_array(strtolower($ext), $availableExtensions)) {
            if (File::isDirectory($path)) {
                rename($path, $path .  '_'.time());
            }
            File::makeDirectory($path, 0777, true, true);

            if ($ext != 'pdf') {
                $fileTimestemp = explode("_", $fileName)[0];
                $newFileName = $fileTimestemp . '_' . $document->id . '.' . $ext;
                $newFilePath = public_path() . '/' . $newFileName;
                rename($filePath, $newFilePath);
                $pdfFilePath = UploadHelper::convertFileToPdf($newFilePath, $path, $fileName);
                UploadHelper::convertPdfToImages($pdfFilePath, $path, $fileName, true);
                rename($newFilePath, $filePath);
            } else {
                $pdfFilePath = $filePath;
                UploadHelper::convertPdfToImages($pdfFilePath, $path, $fileName);
            }
        } elseif (in_array(strtolower($ext), $imageExtensions)) {
             if (File::isDirectory($path)) {
                rename($path, $path . '_'.time());
            }
            File::makeDirectory($path, 0777, true, true);
            \File::copy($filePath, "$path/$fileName.$ext");
        }
    }
}
