# Security Policy

## Reporting a vulnerability

This extension is part of the Symfony ecosystem and follows the
[Symfony security process](https://symfony.com/security).

If you discover a security issue in this extension, **do not open a public
GitHub issue**. Instead, please report it privately by emailing
**security@symfony.com**.

You will receive a response within 48 hours. If the issue is confirmed, a fix
will be prepared and a coordinated disclosure scheduled.

## Supported versions

Only the latest minor release of this extension receives security updates
during the `0.x` development phase. Once `1.0` is tagged, the supported
versions table here will be updated to reflect a stable support window.

## Threat model

`deepclone_to_array()` is intended to be called on values produced by trusted
PHP code in the same process. It is **not** designed as a sandbox: it executes
`__sleep`, `__serialize`, and other magic methods on the values it walks, and
will reach any class autoloader configured in the process.

`deepclone_from_array()` validates the structure of the payload it is given
and throws `\ValueError` on malformed input. It instantiates classes named in
the payload via the standard PHP class loader. Treat its input the same way
you would treat input to `unserialize()`: only call it on payloads from
trusted sources.
