<?php
class Image
{
    private $width;
    private $height;
    private $path;

    /**
     * Image constructor.
     * @param int $width
     * @param int $height
     * @param string $path
     */
    public function __construct(int $width, int $height, string $path)
    {
        $this->width = $width;
        $this->height = $height;
        $this->path = $path;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getPath()
    {
        return $this->path;
    }
}

class ImageResult
{
    private $original;
    private $optimized;

    /**
     * ImageResult constructor.
     * @param Image $original
     * @param Image $optimized
     */
    public function __construct(Image $original, Image $optimized)
    {
        $this->original = $original;
        $this->optimized = $optimized;
    }

    public function getOriginal()
    {
        return $this->original;
    }

    public function getOptimized()
    {
        return $this->optimized;
    }
}

class ImageOptimizer
{
    private $productsCsv = './products.json';
    private $sourcePath = './source';

    public function getProductList()
    {
        $productsJson = file_get_contents($this->productsCsv);
        $products = json_decode($productsJson, true);
        return $products;
    }

    public function getFileList()
    {
        $entries = scandir($this->sourcePath);
        foreach ($entries as $i => $entry) {
            if (in_array($entry, array('.', '..', '.DS_Store'))) {
                unset($entries[$i]);
            }
        }
        return $entries;
    }

    /**
     * Saves and optimized images and returns metadata about the original images and the output image
     *
     * @param string $filename
     * @param string $product
     * @return Array( Image $original, Image $optimized)
     *
     */
    public function optimize($filename, $product)
    {
        // add your code here
        // ...
        // which will optimize the image dimensions
        // so they fit well with the product
        $this->filename = $filename;
        list($this->width, $this->height) = getimagesize('./source/'.$filename);

        $product_list = $this->getProductList();
        $expectedwidth = $product_list[$product]['width'];
        $expectedheight = $product_list[$product]['height'];

        $original_ratio = $this->width/$this->height;
        $expected_ratio = $expectedwidth/$expectedheight;

        if($original_ratio != $expected_ratio) {
          $option = "crop";
        } else {
          if($expectedwidth > $expectedheight) {
            $option = "landscape";
          } else if ($expectedwidth < $expectedheight) {
            $option = "portrait";
          } else if($expectedwidth == $expectedheight){
              $option = "square";
          }

        }

        $this->image = imagecreatefromjpeg('./source/'.$filename);
        $resized = $this->resizeImage($expectedwidth, $expectedheight, $option);

        $original = new Image($this->width, $this->height, './source/'.$this->filename);
        $optimized = new Image($resized['optimalWidth'], $resized['optimalWidth'], './tmp/'.$this->filename);


        return new ImageResult($original, $optimized);
    }

    public function resizeImage($newWidth, $newHeight, $option) {

      switch ($option)
       {
           case 'square':
               $optimalWidth = $newWidth;
               $optimalHeight= $newHeight;
               break;
           case 'portrait':
               $ratio = $this->width / $this->height;
               $optimalWidth = $newHeight * $ratio;
               $optimalHeight= $newHeight;
               break;
           case 'landscape':
               $ratio = $this->height / $this->width;
               $optimalWidth = $newWidth;
               $optimalHeight= $newWidth * $ratio;
               break;
           case 'crop':

               $heightRatio = $this->height / $newHeight;
               $widthRatio  = $this->width /  $newWidth;

               if ($heightRatio < $widthRatio) {
                   $optimalRatio = $heightRatio;
               } else {
                   $optimalRatio = $widthRatio;
               }

               $optimalHeight = $this->height / $optimalRatio;
               $optimalWidth  = $this->width  / $optimalRatio;
               break;
       }


    $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
    imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

    if ($option == 'crop') {
        $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
        $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );

        $crop = $this->imageResized;
        $this->imageResized = imagecreatetruecolor($newWidth , $newHeight);
        imagecopyresampled($this->imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
    }
    imagejpeg($this->imageResized, './tmp/'.$this->filename, 100);
    return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);

  }


}


$Optimizer = new ImageOptimizer();

$files = $Optimizer->getFileList();
$products = $Optimizer->getProductList();
?>

    <h1>Select your photo and product</h1>
    <form method="POST">
        <table>
            <tr>
                <td>
                    <?php foreach ($files as $file) { ?>
                        <div>
                            <input type="radio" name="file" value="<?php echo $file; ?>"
                                   id="<?php echo $file; ?>_file" <?php echo $_POST['file'] == $file ? 'checked' : ''; ?> />
                            <label for="<?php echo $file; ?>_file"><?php echo $file; ?></label>
                        </div>
                    <?php } ?>
                </td>
                <td>
                    <button type="submit">crop image</button>
                </td>
                <td>
                    <?php foreach ($products as $i => $product) { ?>
                        <div>
                            <input type="radio" name="product" value="<?php echo $i; ?>"
                                   id="<?php echo $i ?>_product" <?php echo $_POST['product'] == $i ? 'checked' : ''; ?> />
                            <label for="<?php echo $i; ?>_product"><?php echo $product['label']; ?></label>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </form>


<?php
$result = null;
if (isset($_POST['file']) && isset($_POST['product'])) {
    $result = $Optimizer->optimize($_POST['file'], $_POST['product']);
}

if (!empty($result)): ?>
    <h1>Optimized output</h1>
    <table>
        <tr>
            <td>
                <b>Original:</b><br/>
                file: <?php echo $result->getOriginal()->getPath(); ?><br/>
                size: <?php echo $result->getOriginal()->getWidth() . ' x ' . $result->getOriginal()->getHeight() . 'px'; ?>
                <br/>
                <img src="<?php echo $result->getOriginal()->getPath(); ?>" height="300"/><br/>
            </td>
            <td>
                <b>Optimized:</b><br/>
                file: <?php echo $result->getOptimized()->getPath(); ?><br/>
                size: <?php echo $result->getOptimized()->getWidth() . ' x ' . $result->getOptimized()->getHeight() . 'px'; ?>
                <br/>
                <img src="<?php echo $result->getOptimized()->getPath(); ?>" height="300"/><br/>
            </td>
        </tr>
    </table>
<?php endif; ?>
