<?php


namespace App\Service;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageService
{

    private $targetDirectory;

    private $cacheManager;

    public function __construct($targetDirectory, CacheManager $cacheManager)
    {
        $this->targetDirectory = $targetDirectory;
        $this->cacheManager = $cacheManager;

    }

    public function setImageExtension($file) {
        return $file . '.jpg';
    }

    public function uploadFiles(&$imageFiles)
    {
        $images = array();
        foreach ($imageFiles as $imageFile) {
            $newFilename = uniqid();
            array_push($images, $newFilename);
            // Move the file to the directory where images are stored
            try {
                $imageFile->move(
                    $this->targetDirectory,
                    $this->setImageExtension($newFilename)
                );
            } catch (FileException $e) {
                echo 'Error while uploading a file'; // ... handle exception if something happens during file upload
            }

            // updates the 'newFilename' property to store the jpeg file name
            // instead of its contents

        }
        return $images;
    }

    public function deleteFile($file)
    {
        $path = 'media/image/' . $file;
        if (file_exists($path)) {
            try {
                unlink($path);
                $this->cacheManager->remove($path);
            } catch (FileException $e) {
                echo 'Error while deleting a file'; // ... handle exception if something happens during file upload
            }
        } else {
            echo 'File not found.';
        }

    }


}
