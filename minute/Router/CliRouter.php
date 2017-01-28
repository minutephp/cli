<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 11/2/2016
 * Time: 5:46 PM
 */
namespace Minute\Router {

    use App\Config\BootLoader;
    use Minute\Event\RouterEvent;
    use Minute\Mime\MimeUtils;

    class CliRouter {
        /**
         * @var BootLoader
         */
        private $bootLoader;
        /**
         * @var MimeUtils
         */
        private $mimeUtils;

        /**
         * CliRouter constructor.
         *
         * @param BootLoader $bootLoader
         * @param MimeUtils $mimeUtils
         */
        public function __construct(BootLoader $bootLoader, MimeUtils $mimeUtils) {
            $this->bootLoader = $bootLoader;
            $this->mimeUtils  = $mimeUtils;
        }

        public function handle(RouterEvent $event) {
            if (php_sapi_name() === 'cli-server') {
                $method = $event->getMethod();

                if (($method === 'GET') && (!$event->getRoute())) {
                    $base      = realpath($this->bootLoader->getBaseDir() . '/public');
                    $filePath  = realpath($base . '/' . ltrim($event->getPath(), '/'));
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

                    if ($filePath && is_file($filePath) && preg_match('/^(css|js|jpg|jpeg|gif|png|ico|mp3|mp4|map|woff|woff2|ttf)$/i', $extension)) {
                        if ((strpos($filePath, $base) === 0) && ($filePath != $base . DIRECTORY_SEPARATOR . 'index.php') && substr(basename($filePath), 0, 1) != '.') {
                            $mime = $this->mimeUtils->getMimeType($filePath);

                            header("Content-type: $mime");
                            readfile($filePath);
                            exit;
                        }
                    }
                }
            }
        }
    }
}