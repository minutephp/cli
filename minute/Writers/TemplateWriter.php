<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 7/30/2016
 * Time: 10:40 AM
 */
namespace Minute\Writers {

    use Minute\Log\LoggerEx;
    use Minute\Utils\PathUtils;
    use StringTemplate\Engine;

    class TemplateWriter {
        /**
         * @var string
         */
        protected $dir;
        /**
         * @var string
         */
        private $data;
        /**
         * @var LoggerEx
         */
        private $logger;
        /**
         * @var Engine
         */
        private $engine;
        /**
         * @var PathUtils
         */
        private $utils;

        /**
         * TemplateWriter constructor.
         *
         * @param LoggerEx $logger
         * @param Engine $engine
         * @param PathUtils $utils
         */
        public function __construct(LoggerEx $logger, Engine $engine, PathUtils $utils) {
            $this->dir    = realpath(sprintf('%s/../../data', __DIR__));
            $this->logger = $logger;
            $this->engine = $engine;
            $this->utils  = $utils;
        }

        public function setTemplate(string $template, string $type = 'file') {
            if ($type === 'file') {
                $file = file_exists($template) ? $template : sprintf("%s/%s", $this->dir, $template);

                if (file_exists($file)) {
                    $this->data = file_get_contents($file);
                } else {
                    throw new \Error("Cannot load template: $file");
                }
            } else {
                $this->data = $template;
            }

            return $this;
        }

        public function readData($replacements) {
            return $this->engine->render($this->data, $replacements);
        }

        public function write($path, $replacements = [], $info = null) {
            @mkdir($this->utils->dirname($path), 0777, true);

            if ($result = file_put_contents($path, $this->readData($replacements))) {
                $this->logger->info(($info ?? "Written file") . ": " . realpath($path));

                return $path;
            }

            throw new \Error("Cannot write file: $path");
        }

        /**
         * @return string
         */
        public function getDir(): string {
            return $this->dir;
        }
    }
}