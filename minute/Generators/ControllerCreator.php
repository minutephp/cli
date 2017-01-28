<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 7/30/2016
 * Time: 1:11 PM
 */
namespace Minute\Generators {

    use Minute\Log\LoggerEx;
    use Minute\Utils\PathUtils;
    use Minute\Writers\TemplateWriter;

    class ControllerCreator {
        /**
         * @var TemplateWriter
         */
        private $writer;
        /**
         * @var LoggerEx
         */
        private $logger;
        /**
         * @var PathUtils
         */
        private $utils;

        /**
         * ControllerCreator constructor.
         *
         * @param TemplateWriter $writer
         * @param LoggerEx $logger
         * @param PathUtils $utils
         */
        public function __construct(TemplateWriter $writer, LoggerEx $logger, PathUtils $utils) {
            $this->writer = $writer;
            $this->writer->setTemplate('controller.txt');
            $this->logger = $logger;
            $this->utils  = $utils;
        }

        public function newController($path, $controller) {
            $namespace = $this->utils->dosPath($this->utils->dirname($controller));
            $class     = $this->utils->filename($controller);

            if ($this->writer->write($path, ['namespace' => $namespace === '.' ? '' : "\\$namespace", 'class' => $class])) {
                require_once($path);
            }
        }

        public function newFunction($path, $func) {
            $code = file_get_contents($path);

            if (preg_match('/^(.*?)class/s', $code, $matches)) {
                $brackets = 1 + substr_count($matches[1], '{');
                $len      = strlen($code);
                $pos      = $len;

                while (($brackets-- > 0) && ($pos !== false)) {
                    $pos = strrpos($code, '}', $pos - $len - 1);
                }

                if ($pos !== false) {
                    $updated = sprintf("%s\n\n%s\n\t%s", trim(substr($code, 0, $pos)), $func, substr($code, $pos));
                    if (file_put_contents($path, $updated)) {
                        $this->logger->info("Created new function in $path");

                        return true;
                    }
                } else {
                    trigger_error("Cannot modify file $path. Unable to find class brackets", E_WARNING);
                }
            }

            return false;
        }
    }
}