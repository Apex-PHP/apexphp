<?php

namespace Framework\Core;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use Exception;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];

    public function set(string $abstract, $concrete = null): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->set($abstract, $concrete);
        $this->singletons[$abstract] = true;
    }

    public function get(string $id)
    {
        // Se já existe uma instância singleton, retorna
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Resolve a dependência
        $concrete = $this->bindings[$id] ?? $id;

        if ($concrete instanceof \Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->resolve($concrete);
        }

        // Se for singleton, guarda a instância
        if (isset($this->singletons[$id])) {
            $this->instances[$id] = $object;
        }

        return $object;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || class_exists($id);
    }

    private function resolve(string $concrete)
    {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new Exception("Cannot resolve parameter {$parameter->getName()} in class {$concrete}");
                }
            } elseif ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
            } else {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new Exception("Cannot resolve parameter {$parameter->getName()} in class {$concrete}");
                }
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    public function make(string $abstract, array $parameters = [])
    {
        $concrete = $this->bindings[$abstract] ?? $abstract;

        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $constructorParameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($constructorParameters as $param) {
            $type = $param->getType();

            // Se foi passado um parâmetro manual, usa ele
            if (isset($parameters[$param->getName()])) {
                $dependencies[] = $parameters[$param->getName()];
                continue;
            }

            if ($type === null) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    throw new Exception("Cannot resolve parameter {$param->getName()}");
                }
            } elseif ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
            } else {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    throw new Exception("Cannot resolve parameter {$param->getName()}");
                }
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
