# php-standard

This repository contains tools for enforcing a personal PHP coding style
standard and a specific project organization structure.

It's highly opinionated. You've been warned. No crybabies please.

## Organization

This project is divided into three parts:
- **Chroma**: A coding standard for PHP Code Sniffer. Ironically, its not currently possible to enforce the standard rules into itself because the PHP Code Sniffer expects a very specific style. However, the standard is enforced on every other part of the library.
- **phpstd CLI tool**: `phpstd` is a small CLI application with commands for linting, cleaning, validating, and setting up new projects.
- **phpmd.xml**: A `phpmd.xml` file is bundled for additional checks.

## Style Guide

TODO

## Contributing

Pull requests are accepted on GitHub. Bug fixes and small improvements are welcome. However, big style changes will be carefully review. Many code style decisions are purely personal (snake_case vs camelCase).

## License

This code is licensed under the MIT license. See LICENSE for more information.
