<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 7/30/2016
 * Time: 6:32 PM
 */
namespace Minute\Readers {

    use Auryn\Injector;
    use Minute\Resolver\Resolver;
    use Minute\Routing\Router;
    use Minute\Utils\PathUtils;
    use Symfony\Component\Routing\Route;

    class RouteSorter {
        /**
         * @var array
         */
        protected $routes = [];
        /**
         * @var Resolver
         */
        private $resolver;
        /**
         * @var PathUtils
         */
        private $utils;

        /**
         * RouteSorter constructor.
         *
         * @param Resolver $resolver
         * @param PathUtils $utils
         */
        public function __construct(Resolver $resolver, PathUtils $utils) {
            $this->resolver = $resolver;
            $this->utils    = $utils;
        }

        public function sortRoutes() {
            if (empty($this->routes)) {
                if ($files = $this->resolver->getRoutes()) {
                    foreach ($files as $file) {
                        $dir = dirname($file, 2);
                        /** @var Router $router */
                        $router = (new Injector())->make('Minute\Routing\Router');
                        require_once($file);
                        $routes = $router->getRouteCollection();
                        /** @var Route $route */
                        foreach ($routes as $route) {
                            $method   = $route->getMethods()[0];
                            $defaults = $route->getDefaults();

                            if ($controller = $defaults['controller']) {
                                $parts = explode('@', $controller, 2);
                                list($classPath, $fn) = [$this->utils->unixPath($parts[0]), $parts[1] ?? 'index'];
                            } else {
                                list($classPath, $fn) = [null, 'index'];
                            }

                            $classPath = preg_replace('/\.php$/', '', $classPath);
                            $path      = $this->utils->unixPath(sprintf('%s/Controller/%s.php', $dir, $classPath));
                            $action    = [$this->resolver->getController($classPath), $fn];

                            $this->routes[] = array_merge($defaults, ['route' => $route, 'controller' => $controller, 'dir' => $dir, 'path' => $path, 'classPath' => $classPath,
                                                                      'fn' => $fn, 'action' => $action, 'method' => $method]);

                        }
                    }
                }
            }

            return $this->routes;
        }
    }
}