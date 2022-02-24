# Monolog Markdown Formatter
A formatter that prints the stack trace in markdown like so:

## :exclamation: Exception occurred here:

| Function                                 | Location                                                                   |
|------------------------------------------|----------------------------------------------------------------------------|
| MarkdownFormatterTest->testStackstrace() | markdown-formatter/vendor/phpunit/phpunit/src/Framework/TestCase.php:1545  |
| PHPUnit\Framework\TestCase->runTest()    | markdown-formatter/vendor/phpunit/phpunit/src/Framework/TestCase.php:1151  |
| PHPUnit\Framework\TestCase->runBare()    | markdown-formatter/vendor/phpunit/phpunit/src/Framework/TestResult.php:726 |
| PHPUnit\Framework\TestResult->run()      | markdown-formatter/vendor/phpunit/phpunit/src/Framework/TestCase.php:903   |
| PHPUnit\Framework\TestCase->run()        | markdown-formatter/vendor/phpunit/phpunit/src/Framework/TestSuite.php:677  |
| PHPUnit\Framework\TestSuite->run()       | markdown-formatter/vendor/phpunit/phpunit/src/Framework/TestSuite.php:677  |
| PHPUnit\Framework\TestSuite->run()       | markdown-formatter/vendor/phpunit/phpunit/src/TextUI/TestRunner.php:663    |
| PHPUnit\TextUI\TestRunner->run()         | markdown-formatter/vendor/phpunit/phpunit/src/TextUI/Command.php:143       |
| PHPUnit\TextUI\Command->run()            | markdown-formatter/vendor/phpunit/phpunit/src/TextUI/Command.php:96        |
| PHPUnit\TextUI\Command::main()           | markdown-formatter/vendor/phpunit/phpunit/phpunit:98                       |


**Context:**
```json

```

## Usage
`composer require brille24/markdown-formatter`

Define the class `Brille24\MarkdownFormatter\MarkdownFormatter` as a service and use it (assuming that auto-wiring is on):
```yaml
monolog:
    type: 'service'
    id: "Brille24\MarkdownFormatter\MarkdownFormatter"
```

If you want to customize the base path that the location is relative to you can pass the first argument to the root of the project eg.

```yaml
service:
    Brille24\MarkdownFormatter\MarkdownFormatter:
        arguments:
            - '/some/path/to/the/project'
```

