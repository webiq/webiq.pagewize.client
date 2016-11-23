<?php
namespace PagewizeClient;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Constraint;
use Intervention\Image\ImageCache;
/**
 * Class PagewizeImageManager
 *
 * Image class with caching functionality enabled
 *
 * @license LICENSE.md
 * @package PagewizeClient
 */
class PagewizeImageManager
{
    /**
     * Resizes the image and places the result into cache
     *
     * @param string      $source   - Source (full url)
     * @param null|int    $width    - Preferred width of the iamge
     * @param null|int    $height   - Preferred height of the image
     * @param null|string $fileType - Request other file type (i.e. ico)
     * @param null|int    $blur     - Apply blur to the image
     *
     * @return Image
     */
    public static function processImageRequest($source, $width = null, $height = null, $fileType = null, $blur = null)
    {
        if (empty($source)) {
            die('No source file defined..');
        }

        // make sure this image exists!
        if (!file_exists($source)) {
            die($source . ' is unknown image');
        }

        // make sure the blur value is correct
        if (!is_null($blur) && ($blur % 10 == 0 || $blur > 100)) {
            die('blur has to be in steps of 10 and not more then 100');
        }

        // create instance of the image manager
        $manager = new ImageManager();

        //get/set the image from/in the cache manager
        $image = $manager->cache(function ($img) use ($source, $width, $height, $fileType, $blur) {
            /**
             * @var ImageCache $img
             * @var Image      $image
             */
            $image = $img->make($source);

            // encode the file if need be
            if (!is_null($fileType)) {
                $image->encode($fileType);
            }

            // when both are null we do not know how or what to resize so just take the existing with/height
            if (is_null($width) && is_null($height)) {
                $width = $image->getWidth();
                $height = $image->getHeight();
            }

            if (!is_null($blur)) {
                $image->blur($blur);
            }

            // resize according to specification
            $image = $image->resize($width, $height, function ($constraint) {
                /** @var Constraint $constraint */
                $constraint->aspectRatio();
            });

            // return the object
            return $image;
        }, 5, true);

        // output in another filetype
        $fileType = (!is_null($fileType) ? $fileType : $image->mime);

        // output that mf
        return $image->response($fileType);
    }
}