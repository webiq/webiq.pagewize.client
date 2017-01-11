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
     * @param array       $config   - Configuration
     * @param string      $source   - Source (full url)
     * @param null|int    $width    - Preferred width of the iamge
     * @param null|int    $height   - Preferred height of the image
     * @param null|string $fileType - Request other file type (i.e. ico)
     * @param null|int    $blur     - Apply blur to the image
     *
     * @return Image
     */
    public static function processImageRequest(
        $config,
        $source,
        $width = null,
        $height = null,
        $fileType = null,
        $blur = null
    ) {
        // -1 on memory limit, prevent the library from running out of memory
        // when processing large images (8mb+)
        ini_set('memory_limit', -1);

        if (empty($source)) {
            die('No source file defined..');
        }

        // add http to the request
        if (!preg_match("~^(?:f|ht)tps?://~i", $source)) {
            $source = "http:" . $source;
        }

        // check that we can fetch the image
        $headers = get_headers($source);
        $httpResponse = substr($headers[0], 9, 3);

        // make sure this image exists!
        if ($httpResponse != '200') {
            die($source . ' is unknown image');
        }

        // make sure the blur value is correct
        if (!is_null($blur) && ($blur % 10 == 0 || $blur > 100)) {
            die('blur has to be in steps of 10 and not more then 100');
        }

        // create instance of the image manager
        $manager = new ImageManager($config);

        // get the source of the image
        $source = file_get_contents($source);

        // should not happen, but you never know
        if ($source === false) {
            die('unable to fetch the image source');
        }

        //get/set the image from/in the cache manager
        $image = $manager->cache(function ($img) use ($source, $width, $height, $fileType, $blur, $config) {
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