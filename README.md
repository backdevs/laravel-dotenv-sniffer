# Dotenv Sniffer
A code sniffer for environment variables not defined in a .env file.

## Requirements
The main system requirements are:
- [PHP](https://www.php.net/downloads) `^8.1`
- [Tokenizer extension](https://www.php.net/manual/en/book.tokenizer.php)

## Installation
```bash
composer require --dev backdevs/dotenv-sniffer
```

## Usage
```bash
vendor/bin/desniff .env.example ./config ./app
```

## Options and Arguments
Options
- `--no-fail` - Don't fail if errors are found (exit code = 0)
- `-w` | `--warn-with-default` - Treat variables with default values passed to [Laravel](https://laravel.com/)\'s [`env()`](https://laravel.com/docs/10.x/helpers#method-env) and [`Env::get()`](https://github.com/illuminate/support/blob/a9ee2804c7625bb7f40a2cabb538a1f3d8ba4e28/Env.php#L74-L100) helpers as warnings
- `-c` | `--fail-code` - The exit code to use when failing (default: 1), useful in CI/CD pipelines

Arguments
- `env-file` - The .env file to check against (e.g.: `.env`, `.env.example`, `.env.dev`)
- `paths` - One or more files and/or directories to check
