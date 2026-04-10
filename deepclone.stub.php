<?php

/**
 * @generate-class-entries
 */

namespace DeepClone {
    /**
     * Thrown by {@see \deepclone_to_array()} when the input contains a value
     * that has no meaningful array representation: a resource, an anonymous
     * class, a Reflection object, an IteratorIterator subclass, or any other
     * type the extension cannot round-trip.
     *
     * The exception message is the bare class or type name (e.g.
     * "stream resource", "ReflectionClass").
     */
    class NotInstantiableException extends \InvalidArgumentException {}

    /**
     * Thrown by {@see \deepclone_from_array()} when the payload references a
     * class that no longer exists in the running PHP process.
     *
     * The exception message is the bare class name.
     */
    class ClassNotFoundException extends \InvalidArgumentException {}
}

namespace {
    /**
     * Walk a PHP value graph and return a pure-array payload that fully
     * describes it. Equivalent to
     * {@see \Symfony\Component\VarExporter\DeepCloner::toArray()} but
     * implemented in C for a 4–5× speedup.
     *
     * The returned array contains only scalars and nested arrays — no objects,
     * no resources — so it is suitable for json_encode(), var_export(), or any
     * other serializer that handles plain PHP arrays. The exact wire format is
     * documented by Symfony's DeepCloner; this extension produces and consumes
     * the same shape.
     *
     * @throws \DeepClone\NotInstantiableException when $value contains a
     *         resource or a non-instantiable class (Reflection*,
     *         *IteratorIterator, anonymous classes, …).
     */
    /**
     * @param list<string>|null $allowedClasses Classes that may be serialized.
     *        null (default) allows all classes. An empty array allows none.
     *        Closures require "Closure" in the list.
     *
     * @throws \ValueError when $allowedClasses is not valid or a class is not allowed.
     */
    function deepclone_to_array(mixed $value, ?array $allowedClasses = null): array {}

    /**
     * Reconstruct a PHP value from a payload previously produced by
     * {@see deepclone_to_array()}. Equivalent to
     * {@see \Symfony\Component\VarExporter\DeepCloner::fromArray($data)->clone()}.
     *
     * @param list<string>|null $allowedClasses Classes that may be instantiated.
     *        null (default) allows all classes. An empty array allows none.
     *        Closures require "Closure" in the list.
     *
     * @throws \ValueError when $data is malformed or a class is not allowed.
     * @throws \DeepClone\ClassNotFoundException when the payload references a
     *         class that no longer exists.
     */
    function deepclone_from_array(array $data, ?array $allowedClasses = null): mixed {}
}
