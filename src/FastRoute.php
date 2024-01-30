<?php
declare(strict_types=1);

namespace FastRoute;

use Closure;
use FastRoute\Cache\FileCache;

use function is_string;

final class FastRoute
{
    /**
     * @param Closure(RouteCollector):void   $routeDefinitionCallback
     * @param class-string<RouteParser>      $routeParser
     * @param class-string<DataGenerator>    $dataGenerator
     * @param class-string<Dispatcher>       $dispatcher
     * @param class-string<RouteCollector>   $routeCollector
     * @param Cache|class-string<Cache>|null $cacheDriver
     */
    private function __construct(
        private readonly Closure $routeDefinitionCallback,
        private readonly string $routeParser,
        private readonly string $dataGenerator,
        private readonly string $dispatcher,
        private readonly string $routeCollector,
        private readonly Cache|string|null $cacheDriver,
    ) {
    }

    /** @param Closure(RouteCollector):void $routeDefinitionCallback */
    public static function recommendedSettings(Closure $routeDefinitionCallback): self
    {
        return new self(
            $routeDefinitionCallback,
            RouteParser\Std::class,
            DataGenerator\MarkBased::class,
            Dispatcher\MarkBased::class,
            RouteCollector::class,
            FileCache::class,
        );
    }

    public function disableCache(): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routeCollector,
            null,
        );
    }

    /** @param Cache|class-string<Cache> $driver */
    public function withCache(Cache|string $driver): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routeCollector,
            $driver,
        );
    }

    public function useCharCountDispatcher(): self
    {
        return $this->useCustomDispatcher(DataGenerator\CharCountBased::class, Dispatcher\CharCountBased::class);
    }

    public function useGroupCountDispatcher(): self
    {
        return $this->useCustomDispatcher(DataGenerator\GroupCountBased::class, Dispatcher\GroupCountBased::class);
    }

    public function useGroupPosDispatcher(): self
    {
        return $this->useCustomDispatcher(DataGenerator\GroupPosBased::class, Dispatcher\GroupPosBased::class);
    }

    public function useMarkDispatcher(): self
    {
        return $this->useCustomDispatcher(DataGenerator\MarkBased::class, Dispatcher\MarkBased::class);
    }

    /**
     * @param class-string<DataGenerator> $dataGenerator
     * @param class-string<Dispatcher>    $dispatcher
     */
    public function useCustomDispatcher(string $dataGenerator, string $dispatcher): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->routeParser,
            $dataGenerator,
            $dispatcher,
            $this->routeCollector,
            $this->cacheDriver,
        );
    }

    public function dispatcher(string $cacheKey): Dispatcher
    {
        $loader = function (): array {
            $collector = new $this->routeCollector(
                new $this->routeParser(),
                new $this->dataGenerator(),
            );

            ($this->routeDefinitionCallback)($collector);

            return $collector->getData();
        };

        if ($this->cacheDriver === null) {
            return new $this->dispatcher($loader());
        }

        $cache = is_string($this->cacheDriver)
            ? new $this->cacheDriver()
            : $this->cacheDriver;

        return new $this->dispatcher($cache->get($cacheKey, $loader));
    }
}
