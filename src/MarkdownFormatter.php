<?php
declare(strict_types=1);

namespace Brille24\MarkdownFormatter;

use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use function Symfony\Component\String\u;
use function array_reduce;

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
        'CRITICAL' => '## :fire:',
    ];

    public function __construct(?string $projectPath = null, ?array $logLevelSymbols = null)
    {
        $this->projectPath = $projectPath ?? realpath(__DIR__.'/../../');

        if ($logLevelSymbols !== null) {
            $this->logLevelSymbols = $logLevelSymbols;
        }
    }

    /** {@inheritDoc} */
    public function format(array $record)
    {
        $errorSymbol = $this->logLevelSymbols[$record['level_name']] ?? ':question:';
        $headline = $errorSymbol.' '.$this->formatMessage($record);

        $stacktrace = $this->formatStackTrace($record);
        $context = $this->formatContext($record['context']);

        if ($record['level'] < Logger::WARNING) {
            return $headline;
        }

        $context = sprintf(
            <<<MARKDOWN
**Context:**
```json
%s
```
MARKDOWN, $context);

        return implode("\n\n", [$headline, $stacktrace, $context]);
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
        if (!array_key_exists('exception', $record['context'])) {
            return '';
        }

        $messages = ['function' => ['Function'], 'location' => ['Location']];
        /** @var Exception $exception */
        $exception = $record['context']['exception'];
        foreach ($exception->getTrace() as $trace) {
            $class = $trace['class'] ?? '\\';
            $type = $trace['type'] ?? '';
            if (array_key_exists('function', $trace)) {
                $function = $trace['function'].'()';
            } else {
                $function = '<no-function>';
            }
            $messages['function'][] = $class.$type.$function;

            if (array_key_exists('file', $trace)) {
                $line = $trace['line'] ?? '';
                $messages['location'][] = $this->replacePath($trace['file']).':'. $line;
            }
        }

        $message = '';
        $leftColumnWidth = $this->getColumnWidth($messages['function']);
        $rightColumnWidth = $this->getColumnWidth($messages['location']);
        foreach (range(0, count($messages['function']) - 1) as $i) {
            if ($i === 1) {
                $message .= '|'.str_repeat('-', $leftColumnWidth + 2).'|'.str_repeat('-', $rightColumnWidth + 2).'|'.PHP_EOL;
            }
            $leftSpacer = $leftColumnWidth - strlen($messages['function'][$i]) + 1;
            $rightSpacer = $rightColumnWidth - strlen($messages['location'][$i]) + 1;
            $message.='|';
            $message .= ' '.$messages['function'][$i]. str_repeat(' ', $leftSpacer);
            $message.='|';
            $message .= ' '.$messages['location'][$i].str_repeat(' ', $rightSpacer);
            $message.='|'.PHP_EOL;
        }

        return $message;
    }

    private function getColumnWidth(array $messages): int
    {
        return array_reduce(
            $messages,
            function (int $maxLength, string $current) {
                return max($maxLength, strlen($current));
            },
            0);
    }
}

