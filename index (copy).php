<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
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



        list($originalWidth, $originalHeight) = getimagesize('./source/'.$filename);
        $width = $originalWidth;
        $height = $originalHeight;
        $ratio = $originalWidth/$originalHeight;
        $product_list = $this->getProductList();
        $expectedwidth = $product_list[$product]['width'];
        $expectedheight = $product_list[$product]['height'];

         $src = imagecreatefromjpeg('source/01.jpg');
         print_r("End");
         $dst = imagecreatetruecolor($expectedwidth, $expectedheight);
        $dst1 = imagecopyresampled($dst, $src, 0, 0, 0, 0, $expectedwidth, $expectedheight, $expectedwidth, $expectedheight);
        $original = new Image($width, $height, './source/'.$filename);
        $optimized = new Image($originalWidth, $originalHeight, './source/'.$filename);


        return new ImageResult($original, $optimized);
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
