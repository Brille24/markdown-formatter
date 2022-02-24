<?php

use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class MarkdownFormatterTest extends TestCase{

    /** @var MarkdownFormatter */
    private $formatter;


    public function setup(): void
    {
        $this->formatter = new Brille24\MarkdownFormatter\MarkdownFormatter();
    }

    public function testDebugMessage() {
        $output = $this->formatter->format([
            'level_name' => 'DEBUG',
            'level' => Logger::DEBUG,
            'message' => 'Some debugging that needs to be logged',
            'context' => [
                'this is ignored' => 'of course'
            ]
        ]);

        $this->assertSame(':information_source: Some debugging that needs to be logged', $output);
    }

    public function testErrorOutputWithoutContext(): void {
        $output = $this->formatter->format([
            'level_name' => 'ERROR',
            'level' => Logger::ERROR,
            'message' => 'Error in some function',
            'context' => []
        ]);
       $this->assertSame(<<<MARKDOWN
## :exclamation: Error in some function



**Context:**
```json

```
MARKDOWN,
    $output
        );
    }

    public function testErrorOutputWithContext(): void {
        $output = $this->formatter->format([
            'level_name' => 'ERROR',
            'level' => Logger::ERROR,
            'message' => 'Error in some function',
            'context' => [
                'some_context' => true,
                'numbers' => 10,
            ]
        ]);
       $this->assertSame(<<<MARKDOWN
## :exclamation: Error in some function



**Context:**
```json
{
    "some_context": true,
    "numbers": 10
}
```
MARKDOWN,
    $output
        );
    }

    public function testErrorOutputWithException(): void
    {
        $output = $this->formatter->format([
            'level_name' => 'ERROR',
            'level' => Logger::ERROR,
            'message' => 'Error in some function',
            'context' => [
                'some_context' => true,
                'numbers' => 10,
            ]
        ]);
       $this->assertSame(<<<MARKDOWN
## :exclamation: Error in some function



**Context:**
```json
{
    "some_context": true,
    "numbers": 10
}
```
MARKDOWN,
    $output
        );
    }

    public function testStackstrace(): void {
        $exception = new Exception();

        $output = $this->formatter->format([
            'level_name' => 'ERROR',
            'level' => Logger::ERROR,
            'message' => 'Exception occurred here:',
            'context' => [
                'exception' => $exception,
            ],
        ]);

        $this->assertSame(<<<MARKDOWN
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
MARKDOWN, $output);
    }

    public function testTrunctation() {
        $output = $this->formatter->formatContext([
            'hello' => str_repeat('kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093', 1000)
        ]);

        $this->assertSame(<<<MARKDOWN
{
    "hello": "kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093k...
MARKDOWN, $output);
    }
}

