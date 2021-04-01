<?php
declare(strict_types=1);

namespace Brille24\MarkdownFormatter;

use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use function Symfony\Component\String\u;

final class MarkdownFormatter implements FormatterInterface
{
    /** @var string */
    private $projectPath;

    /** @var array<string, string> */
    private $logLevelSymbols = [
        'INFO' => ':information_source:',
        'DEBUG' => ':information_source:',
        'WARN' => '### :warning:',
        'ERROR' => '## :exclamation:',
    ];

    public function __construct(string $projectPath, ?array $logLevelSymbols = null)
    {
        $this->projectPath = $projectPath;

        if ($logLevelSymbols !== null) {
            $this->logLevelSymbols = $logLevelSymbols;
        }
    }

    /** {@inheritDoc} */
    public function format(array $record)
    {
        $errorSymbol = $this->logLevelSymbols[$record['level_name']] ?? '';
        $headline = $errorSymbol.' '.$this->formatMessage($record).PHP_EOL;

        $stacktrace = $this->formatStackTrace($record);
        $context = $this->formatContext($record['context']);

        if ($record['level'] < Logger::WARNING) {
            return $headline;
        }

        return sprintf(
            <<<MARKDOWN
%s

%s

**Context:**
```json
%s
```
MARKDOWN, $headline, $stacktrace, $context
        );
    }

    /**
     * Formats a set of log records.
     *
     * @param array $records A set of records to format
     *
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record).PHP_EOL.PHP_EOL;
        }

        return $message;
    }

    /** @param array<string, mixed> $context */
    public function formatContext(array $context): string
    {
        unset($context['exception']);

        if (count($context) > 0) {
            return (string)u((json_encode($context, JSON_PRETTY_PRINT, 20)))->truncate(1000, '...');
        }

        return '';
    }

    private function replacePath(string $absolutePath): string
    {
        $projectRoot = $this->projectPath;
        if (strpos($absolutePath, $projectRoot) === 0) {
            return substr($absolutePath, strlen($projectRoot) + 1);
        }

        return $absolutePath;
    }

    private function formatMessage(array &$record): string
    {
        $message = $record['message'];

        foreach ($record['context'] as $key => $context) {
            $searchTerm = "{{$key}}";
            if (strpos($message, $searchTerm) !== false) {
                $message = str_replace($searchTerm, (string)$context, $message);
                unset($record['context'][$key]);
            }
        }

        return $message;
    }

    private function formatStackTrace(array &$record): string
    {
        $message = '';
        if (array_key_exists('exception', $record['context'])) {
            /** @var Exception $exception */
            $exception = $record['context']['exception'];
            $message .= '| Function | Location |'.PHP_EOL;
            $message .= '|----------|----------|'.PHP_EOL;
            foreach ($exception->getTrace() as $trace) {
                $message .= '|';
                $class = $trace['class'] ?? '\\';
                $type = $trace['type'] ?? '';
                if (array_key_exists('function', $trace)) {
                    $function = $trace['function'].'()';
                } else {
                    $function = '<no-function>';
                }

                $message .= $class.$type.$function.' | ';
                if (array_key_exists('file', $trace)) {
                    $line = $trace['line'] ?? '';
                    $message .= $this->replacePath($trace['file']).':';
                    $message .= $line.' |'.PHP_EOL;
                }
            }
            $message .= PHP_EOL;
        }

        return $message;
    }
}
