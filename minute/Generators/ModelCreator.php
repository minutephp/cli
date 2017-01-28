<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 7/30/2016
 * Time: 9:11 AM
 */
namespace Minute\Generators {

    use App\Config\BootLoader;
    use Illuminate\Support\Str;
    use Minute\Database\Database;
    use Minute\Resolver\Resolver;
    use Minute\Utils\PathUtils;
    use Minute\Writers\TemplateWriter;

    class ModelCreator {
        /**
         * @var BootLoader
         */
        private $bootLoader;
        /**
         * @var Database
         */
        private $database;
        /**
         * @var Resolver
         */
        private $resolver;
        /**
         * @var TemplateWriter
         */
        private $writer;
        /**
         * @var PathUtils
         */
        private $utils;

        /**
         * CreateModels constructor.
         *
         * @param TemplateWriter $writer
         * @param BootLoader $bootLoader
         * @param Database $database
         * @param Resolver $resolver
         * @param PathUtils $utils
         */
        public function __construct(TemplateWriter $writer, BootLoader $bootLoader, Database $database, Resolver $resolver, PathUtils $utils) {
            $this->bootLoader = $bootLoader;
            $this->database   = $database;
            $this->resolver   = $resolver;
            $this->utils      = $utils;

            $this->writer = $writer;
            $this->writer->setTemplate('model.txt');
        }

        public function write() {
            $pdo    = $this->database->getConnection();
            $tables = $pdo->select(sprintf("SELECT TABLE_NAME as `table`, COLUMN_NAME as pk FROM information_schema.columns WHERE table_schema = '%s' AND COLUMN_KEY = 'PRI'", $pdo->getDatabaseName()));

            foreach ($tables as $table) {
                $name = $table->table;
                if (!$this->resolver->getModel($name)) {
                    $path = sprintf('%s/app/Model/%s.php', $this->bootLoader->getBaseDir(), ucfirst(Str::camel(Str::singular("$name"))));

                    if (!file_exists($path)) {
                        $this->writer->write($path, ['class' => $this->utils->filename($path), 'table' => $table->table, 'pk' => $table->pk], 'Created new model');
                    }
                }
            }
        }
    }
}