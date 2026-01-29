<?php

class Log
{
    private string $logDir;

    public function __construct($path = null)
    {
        $this->logDir = $path ?? __DIR__ . '/logs/';
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->write('debug', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    private function write(string $type, string $message, array $context): void
    {
        $file = $this->logDir . $type . '.log';

        $log = $this->formatMessage($message, $context);

        file_put_contents($file, $log, FILE_APPEND);
    }

    private function formatMessage(string $message, array $context): string
    {
        $date = date('Y-m-d H:i:s');

        $ctx = '';
        if (!empty($context)) {
            $ctx = ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return "[{$date}] {$message}{$ctx}\n";
    }
}

?>
