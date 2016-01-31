<?php

namespace Darryldecode\Backend\Base\Services\Bus;

use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionParameter;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Container\Container;
use Darryldecode\Backend\Base\Contracts\Bus\Dispatcher as DispatcherContract;

class Dispatcher implements DispatcherContract
{
    /**
     * The container implementation.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * All of the command-to-handler mappings.
     *
     * @var array
     */
    protected $mappings = [];

    /**
     * The fallback mapping Closure.
     *
     * @var \Closure
     */
    protected $mapper;

    /**
     * Create a new command dispatcher instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Marshal a command and dispatch it to its appropriate handler.
     *
     * @param  mixed  $command
     * @param  array  $array
     * @return mixed
     */
    public function dispatchFromArray($command, array $array)
    {
        return $this->dispatch($this->marshalFromArray($command, $array));
    }

    /**
     * Marshal a command and dispatch it to its appropriate handler.
     *
     * @param  mixed  $command
     * @param  \ArrayAccess  $source
     * @param  array  $extras
     * @return mixed
     */
    public function dispatchFrom($command, ArrayAccess $source, array $extras = [])
    {
        return $this->dispatch($this->marshal($command, $source, $extras));
    }

    /**
     * Marshal a command from the given array.
     *
     * @param  string  $command
     * @param  array  $array
     * @return mixed
     */
    protected function marshalFromArray($command, array $array)
    {
        return $this->marshal($command, new Collection, $array);
    }

    /**
     * Marshal a command from the given array accessible object.
     *
     * @param  string  $command
     * @param  \ArrayAccess  $source
     * @param  array  $extras
     * @return mixed
     */
    protected function marshal($command, ArrayAccess $source, array $extras = [])
    {
        $injected = [];

        $reflection = new ReflectionClass($command);

        if ($constructor = $reflection->getConstructor()) {
            $injected = array_map(function ($parameter) use ($command, $source, $extras) {
                return $this->getParameterValueForCommand($command, $source, $parameter, $extras);

            }, $constructor->getParameters());
        }

        return $reflection->newInstanceArgs($injected);
    }

    /**
     * Get a parameter value for a marshaled command.
     *
     * @param  string  $command
     * @param  \ArrayAccess  $source
     * @param  \ReflectionParameter  $parameter
     * @param  array  $extras
     * @return mixed
     */
    protected function getParameterValueForCommand($command, ArrayAccess $source,
        ReflectionParameter $parameter, array $extras = [])
    {
        if (array_key_exists($parameter->name, $extras)) {
            return $extras[$parameter->name];
        }

        if (isset($source[$parameter->name])) {
            return $source[$parameter->name];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        MarshalException::whileMapping($command, $parameter);
    }

    /**
     * Dispatch a command to its appropriate handler.
     *
     * @param  mixed  $command
     * @return mixed
     */
    public function dispatch($command)
    {
        return $this->dispatchNow($command);
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
     *
     * @param  mixed  $command
     * @return mixed
     */
    public function dispatchNow($command)
    {
        return $this->container->call([$command, 'handle']);
    }


    /**
     * Get the given handler segment for the given command.
     *
     * @param  mixed  $command
     * @param  int  $segment
     * @return string
     */
    protected function inflectSegment($command, $segment)
    {
        $className = get_class($command);

        if (isset($this->mappings[$className])) {
            return $this->getMappingSegment($className, $segment);
        } elseif ($this->mapper) {
            return $this->getMapperSegment($command, $segment);
        }

        throw new InvalidArgumentException("No handler registered for command [{$className}]");
    }

    /**
     * Get the given segment from a given class handler.
     *
     * @param  string  $className
     * @param  int  $segment
     * @return string
     */
    protected function getMappingSegment($className, $segment)
    {
        return explode('@', $this->mappings[$className])[$segment];
    }

    /**
     * Get the given segment from a given class handler using the custom mapper.
     *
     * @param  mixed  $command
     * @param  int  $segment
     * @return string
     */
    protected function getMapperSegment($command, $segment)
    {
        return explode('@', call_user_func($this->mapper, $command))[$segment];
    }

    /**
     * Register command-to-handler mappings.
     *
     * @param  array  $commands
     * @return void
     */
    public function maps(array $commands)
    {
        $this->mappings = array_merge($this->mappings, $commands);
    }

    /**
     * Register a fallback mapper callback.
     *
     * @param  \Closure  $mapper
     * @return void
     */
    public function mapUsing(Closure $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Map the command to a handler within a given root namespace.
     *
     * @param  mixed  $command
     * @param  string  $commandNamespace
     * @param  string  $handlerNamespace
     * @return string
     */
    public static function simpleMapping($command, $commandNamespace, $handlerNamespace)
    {
        $command = str_replace($commandNamespace, '', get_class($command));

        return $handlerNamespace.'\\'.trim($command, '\\').'Handler@handle';
    }
}
