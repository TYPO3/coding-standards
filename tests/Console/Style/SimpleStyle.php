<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 project.
 *
 * (c) 2019-2023 Benni Mack
 *               Simon Gilli
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CodingStandards\Tests\Console\Style;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\TrimmedBufferOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Terminal;

/**
 * Simple output decorator helpers for testing.
 *
 * Parts of this file are copied from symfony/console.
 */
class SimpleStyle extends OutputStyle
{
    public const MAX_LINE_LENGTH = 120;

    private $input;
    private $questionHelper;
    private $progressBar;
    private $lineLength;
    private $bufferedOutput;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->bufferedOutput = new TrimmedBufferOutput(\DIRECTORY_SEPARATOR === '\\' ? 4 : 2, $output->getVerbosity(), false, clone $output->getFormatter());
        // Windows cmd wraps lines as soon as the terminal width is reached, whether there are following chars or not.
        $width = (new Terminal())->getWidth() ?: self::MAX_LINE_LENGTH;
        $this->lineLength = min($width - (int)(\DIRECTORY_SEPARATOR === '\\'), self::MAX_LINE_LENGTH);

        parent::__construct($output);
    }

    /**
     * Formats a message as a block of text.
     *
     * @param array|string $messages The message to write in the block
     * @param string|null  $type     The block type (added in [] on first line)
     * @param string|null  $style    The style to apply to the whole block
     * @param string       $prefix   The prefix for the block
     * @param bool         $padding  Whether to add vertical padding
     * @param bool         $escape   Whether to escape the message
     */
    public function block($messages, $type = null, $style = null, $prefix = ' ', $padding = false, $escape = true): void
    {
        $messages = \is_array($messages) ? array_values($messages) : [$messages];

        $this->writeln($this->createBlock($messages, $type, $style, $prefix, $padding, $escape));
    }

    /**
     * @inheritDoc
     */
    public function title($message): void
    {
        $this->writeln([
            sprintf('<comment>%s</>', OutputFormatter::escapeTrailingBackslash($message)),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function section($message): void
    {
        $this->writeln([
            sprintf('<comment>%s</>', OutputFormatter::escapeTrailingBackslash($message)),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function listing(array $elements): void
    {
        $elements = array_map(fn ($element) => sprintf(' * %s', $element), $elements);

        $this->writeln($elements);
    }

    /**
     * @inheritDoc
     */
    public function text($message): void
    {
        $messages = \is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->writeln(sprintf(' %s', $message));
        }
    }

    /**
     * Formats a command comment.
     *
     * @param array|string $message
     */
    public function comment($message): void
    {
        $this->block($message, null, null, '<fg=default;bg=default> // </>', false, false);
    }

    /**
     * @inheritDoc
     */
    public function success($message): void
    {
        $this->block($message, 'OK', 'fg=black;bg=green', ' ', true);
    }

    /**
     * @inheritDoc
     */
    public function error($message): void
    {
        $this->block($message, 'ERROR', 'fg=white;bg=red', ' ', true);
    }

    /**
     * @inheritDoc
     */
    public function warning($message): void
    {
        $this->block($message, 'WARNING', 'fg=black;bg=yellow', ' ', true);
    }

    /**
     * @inheritDoc
     */
    public function note($message): void
    {
        $this->block($message, 'NOTE', 'fg=yellow', ' ! ');
    }

    /**
     * @inheritDoc
     */
    public function caution($message): void
    {
        $this->block($message, 'CAUTION', 'fg=white;bg=red', ' ! ', true);
    }

    /**
     * @inheritDoc
     */
    public function table(array $headers, array $rows): void
    {
        $style = clone Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');

        $table = new Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->setStyle($style);

        $table->render();
        $this->newLine();
    }

    /**
     * Formats a horizontal table.
     */
    public function horizontalTable(array $headers, array $rows): void
    {
        $style = clone Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');

        $table = new Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->setStyle($style);
        $table->setHorizontal(true);

        $table->render();
        $this->newLine();
    }

    /**
     * Formats a list of key/value horizontally.
     *
     * Each row can be one of:
     * * 'A title'
     * * ['key' => 'value']
     * * new TableSeparator()
     *
     * @param array|string|TableSeparator ...$list
     */
    public function definitionList(...$list): void
    {
        $style = clone Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');

        $table = new Table($this);
        $headers = [];
        $row = [];
        foreach ($list as $value) {
            if ($value instanceof TableSeparator) {
                $headers[] = $value;
                $row[] = $value;
                continue;
            }
            if (\is_string($value)) {
                $headers[] = new TableCell($value, ['colspan' => 2]);
                $row[] = null;
                continue;
            }
            if (!\is_array($value)) {
                throw new InvalidArgumentException('Value should be an array, string, or an instance of TableSeparator.');
            }
            $headers[] = key($value);
            $row[] = current($value);
        }

        $table->setHeaders($headers);
        $table->setRows([$row]);
        $table->setHorizontal();
        $table->setStyle($style);

        $table->render();
        $this->newLine();
    }

    /**
     * @inheritDoc
     */
    public function ask(string $question, string $default = null, callable $validator = null): mixed
    {
        $question = new Question($question, $default);
        $question->setValidator($validator);

        return $this->askQuestion($question);
    }

    /**
     * @inheritDoc
     */
    public function askHidden(string $question, callable $validator = null): mixed
    {
        $question = new Question($question);

        $question->setHidden(true);
        $question->setValidator($validator);

        return $this->askQuestion($question);
    }

    /**
     * @inheritDoc
     */
    public function confirm(string $question, bool $default = true): bool
    {
        return $this->askQuestion(new ConfirmationQuestion($question, $default));
    }

    /**
     * @inheritDoc
     */
    public function choice(string $question, array $choices, mixed $default = null): mixed
    {
        if ($default !== null) {
            $values = array_flip($choices);
            $default = $values[$default] ?? $default;
        }

        return $this->askQuestion(new ChoiceQuestion($question, $choices, $default));
    }

    /**
     * @inheritDoc
     */
    public function progressStart(int $max = 0): void
    {
        $this->progressBar = $this->createProgressBar($max);
        $this->progressBar->start();
    }

    /**
     * @inheritDoc
     */
    public function progressAdvance(int $step = 1): void
    {
        $this->getProgressBar()->advance($step);
    }

    /**
     * @inheritDoc
     */
    public function progressFinish(): void
    {
        $this->getProgressBar()->finish();
        $this->newLine(2);
        $this->progressBar = null;
    }

    /**
     * @inheritDoc
     */
    public function createProgressBar(int $max = 0): ProgressBar
    {
        $progressBar = parent::createProgressBar($max);

        if ('\\' !== \DIRECTORY_SEPARATOR || getenv('TERM_PROGRAM') === 'Hyper') {
            $progressBar->setEmptyBarCharacter('░'); // light shade character \u2591
            $progressBar->setProgressCharacter('');
            $progressBar->setBarCharacter('▓'); // dark shade character \u2593
        }

        return $progressBar;
    }

    /**
     * @return mixed
     */
    public function askQuestion(Question $question)
    {
        if ($this->input->isInteractive()) {
            $this->autoPrependBlock();
        }

        if (!$this->questionHelper) {
            $this->questionHelper = new SymfonyQuestionHelper();
        }

        $answer = $this->questionHelper->ask($this->input, $this, $question);

        if ($this->input->isInteractive()) {
            $this->newLine();
            $this->bufferedOutput->write("\n");
        }

        return $answer;
    }

    /**
     * @inheritDoc
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL): void
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            parent::writeln($message, $type);
            $this->writeBuffer($message, true, $type);
        }
    }

    /**
     * @inheritDoc
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL): void
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            parent::write($message, $newline, $type);
            $this->writeBuffer($message, $newline, $type);
        }
    }

    /**
     * @inheritDoc
     */
    public function newLine($count = 1): void
    {
        parent::newLine($count);
        $this->bufferedOutput->write(str_repeat("\n", $count));
    }

    /**
     * Returns a new instance which makes use of stderr if available.
     *
     * @return self
     */
    public function getErrorStyle()
    {
        return new self($this->input, $this->getErrorOutput());
    }

    private function getProgressBar(): ProgressBar
    {
        if (!$this->progressBar) {
            throw new RuntimeException('The ProgressBar is not started.');
        }

        return $this->progressBar;
    }

    private function autoPrependBlock(): void
    {
        $chars = substr(str_replace(\PHP_EOL, "\n", $this->bufferedOutput->fetch()), -2);

        if (!isset($chars[0])) {
            $this->newLine(); //empty history, so we should start with a new line.

            return;
        }
        //Prepend new line for each non LF chars (This means no blank line was output before)
        $this->newLine(2 - substr_count($chars, "\n"));
    }

    private function autoPrependText(): void
    {
        $fetched = $this->bufferedOutput->fetch();
        //Prepend new line if last char isn't EOL:
        if (substr($fetched, -1) !== "\n") {
            $this->newLine();
        }
    }

    private function writeBuffer(string $message, bool $newLine, int $type): void
    {
        // We need to know if the last chars are PHP_EOL
        $this->bufferedOutput->write($message, $newLine, $type);
    }

    private function createBlock(iterable $messages, string $type = null, string $style = null, string $prefix = ' ', bool $padding = false, bool $escape = false): array
    {
        $indentLength = 0;
        $prefixLength = Helper::strlenWithoutDecoration($this->getFormatter(), $prefix);
        $lines = [];

        if ($type !== null) {
            $type = sprintf('[%s] ', $type);
            $indentLength = \strlen($type);
            $lineIndentation = str_repeat(' ', $indentLength);
        }

        // wrap and add newlines for each element
        foreach ($messages as $key => $message) {
            if ($escape) {
                $message = OutputFormatter::escape($message);
            }

            //$lines = array_merge($lines, explode(\PHP_EOL, wordwrap($message, $this->lineLength - $prefixLength - $indentLength, \PHP_EOL, true)));
            $lines = array_merge($lines, [$message]);

            if (\count($messages) > 1 && $key < \count($messages) - 1) {
                $lines[] = '';
            }
        }

        $firstLineIndex = 0;
        if ($padding && $this->isDecorated()) {
            $firstLineIndex = 1;
            array_unshift($lines, '');
            $lines[] = '';
        }

        foreach ($lines as $i => &$line) {
            if ($type !== null) {
                $line = $firstLineIndex === $i ? $type . $line : $lineIndentation . $line;
            }

            $line = $prefix . $line;
            //$line .= str_repeat(' ', $this->lineLength - Helper::strlenWithoutDecoration($this->getFormatter(), $line));

            if ($style) {
                $line = sprintf('<%s>%s</>', $style, $line);
            }
        }

        return $lines;
    }
}
