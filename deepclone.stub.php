<?php

/**
 * @generate-class-entries
 */

/**
 * Walk a PHP value graph and return a pure-array payload that fully describes
 * it. Equivalent to {@see \Symfony\Component\VarExporter\DeepCloner::toArray()}
 * but implemented in C for a 4–5× speedup.
 *
 * The returned array contains only scalars and nested arrays — no objects, no
 * resources — so it is suitable for json_encode(), var_export(), or any other
 * serializer that handles plain PHP arrays. The exact wire format is documented
 * by Symfony's DeepCloner; this extension produces and consumes the same shape.
 *
 * Throws \Exception with code 5731 (DC_ERR_NOT_INSTANTIABLE) when $value
 * contains a resource or a non-instantiable class (Reflection*, *IteratorIterator,
 * etc.). The message is the bare class/type name.
 */
function deepclone_to_array(mixed $value): array {}

/**
 * Reconstruct a PHP value from a payload previously produced by
 * {@see deepclone_to_array()}. Equivalent to
 * {@see \Symfony\Component\VarExporter\DeepCloner::fromArray($data)->clone()}.
 *
 * Throws \ValueError if $data is malformed.
 * Throws \Exception with code 5732 (DC_ERR_CLASS_NOT_FOUND) if the payload
 * references a class that no longer exists. The message is the bare class name.
 */
function deepclone_from_array(array $data): mixed {}
