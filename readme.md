# PHP obfuscator

![banner](./banner.svg)

The `Minify.php` script will remove all the page setup of an existing PHP source code i.e. remove comments, unneeded carriage returns, multiples spaces, ...

Then, once minified, the script will obfuscate your code based on your settings.

## Obfuscation

Take a look on the `src/settings.json`, it looks like:

```json
{
    "replace": {
        "$commentTokens": "$n",
        "$compress": "$xZ0",
        "$content": "$c",
        "removeComments": "rmCts"
    }
}
```

The `replace` array contains a list of keys (like `$commentTokens`) and a list of values (like `$n`).

You should define there the list of everything you want to obfuscate like the name of your class, name of your functions or variables.

In the example provided above, every occurrences of `$commentTokens` in the source file will be replaced by `$n` and this will be done for each keys.

## How to run

The `minify` script requires two arguments, the name of the `input` file and the name of the name of the resulting, `output`, file.

```bash
php Minify.php input=src/Minify.php output=samples/test.php
```

The command line above will process the `[src/Minify.php](https://github.com/cavo789/php_obfuscator/blob/main/src/minify.php)`, keep that file unmodified but read his content, minify and obfuscate it and create then the `https://github.com/cavo789/php_obfuscator/blob/main/samples/test.php` file.

## Sample

The file [samples/test.php](samples/test.php) has been created by running this command: `php Minify.php input=src/Minify.php output=samples/test.php`.

The original file is [src/Minify.php](src/Minify.php).

## Why

I've created this script years ago (in 2013) when I've started to develop and sell a *web application firewall*.

This software took me several hundred hours and, at the time, I wanted to secure my work and make it as difficult as possible for a dishonest person to appropriate my work and sell it on their behalf.

Making the code unreadable seemed like a good solution at the time.

## Author

Christophe Avonture
