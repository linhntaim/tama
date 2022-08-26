<?php

namespace App\Trading\Console\Commands\Telegram;

use JsonException;
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Text descriptor.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 *
 * @internal
 */
class TextDescriptor implements DescriptorInterface
{
    protected OutputInterface $output;

    /**
     * @throws JsonException
     */
    public function describe(OutputInterface $output, object $object, array $options = []): void
    {
        $this->output = $output;

        switch (true) {
            case $object instanceof Command:
                $this->describeCommand($object, $options);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Object of type "%s" is not describable.', get_debug_type($object)));
        }
    }

    /**
     * Writes content to output.
     */
    protected function write(string $content, bool $decorated = false): void
    {
        $this->output->write($content, false, $decorated ? OutputInterface::OUTPUT_NORMAL : OutputInterface::OUTPUT_RAW);
    }

    /**
     * @throws JsonException
     */
    protected function describeInputArgument(InputArgument $argument, array $options = []): void
    {
        if (null !== $argument->getDefault() && (!is_array($argument->getDefault()) || count($argument->getDefault()))) {
            $default = sprintf('<comment> [default: %s]</comment>', $this->formatDefaultValue($argument->getDefault()));
        }
        else {
            $default = '';
        }

        $totalWidth = $options['total_width'] ?? Helper::width($argument->getName());
        $spacingWidth = $totalWidth - strlen($argument->getName());

        $this->writeText(sprintf('  <info>%s</info>  %s%s%s',
            $argument->getName(),
            str_repeat(' ', $spacingWidth),
            // + 4 = 2 spaces before <info>, 2 spaces after </info>
            preg_replace('/\s*[\r\n]\s*/', PHP_EOL . str_repeat(' ', $totalWidth + 4), $argument->getDescription()),
            $default
        ), $options);
    }

    /**
     * @throws JsonException
     */
    protected function describeInputOption(InputOption $option, array $options = []): void
    {
        if ($option->acceptValue() && null !== $option->getDefault() && (!is_array($option->getDefault()) || count($option->getDefault()))) {
            $default = sprintf('<comment> [default: %s]</comment>', $this->formatDefaultValue($option->getDefault()));
        }
        else {
            $default = '';
        }

        $value = '';
        if ($option->acceptValue()) {
            $value = '=' . strtoupper($option->getName());

            if ($option->isValueOptional()) {
                $value = '[' . $value . ']';
            }
        }

        $totalWidth = $options['total_width'] ?? $this->calculateTotalWidthForOptions([$option]);
        $synopsis = sprintf('%s%s',
            $option->getShortcut() ? sprintf('-%s, ', $option->getShortcut()) : '    ',
            sprintf($option->isNegatable() ? '--%1$s|--no-%1$s' : '--%1$s%2$s', $option->getName(), $value)
        );

        $spacingWidth = $totalWidth - Helper::width($synopsis);

        $this->writeText(sprintf('  <info>%s</info>  %s%s%s%s',
            $synopsis,
            str_repeat(' ', $spacingWidth),
            // + 4 = 2 spaces before <info>, 2 spaces after </info>
            preg_replace('/\s*[\r\n]\s*/', PHP_EOL . str_repeat(' ', $totalWidth + 4), $option->getDescription()),
            $default,
            $option->isArray() ? '<comment> (multiple values allowed)</comment>' : ''
        ), $options);
    }

    /**
     * @throws JsonException
     */
    protected function describeInputDefinition(InputDefinition $definition, array $options = []): void
    {
        $totalWidth = $this->calculateTotalWidthForOptions($definition->getOptions());
        foreach ($definition->getArguments() as $argument) {
            $totalWidth = max($totalWidth, Helper::width($argument->getName()));
        }

        if ($definition->getArguments()) {
            $this->writeText('<comment>Arguments:</comment>', $options);
            $this->writeText(PHP_EOL);
            foreach ($definition->getArguments() as $argument) {
                $this->describeInputArgument($argument, array_merge($options, ['total_width' => $totalWidth]));
                $this->writeText(PHP_EOL);
            }
        }

        if ($definition->getArguments() && $definition->getOptions()) {
            $this->writeText(PHP_EOL);
        }

        if ($definition->getOptions()) {
            $laterOptions = [];

            $this->writeText('<comment>Options:</comment>', $options);
            foreach ($definition->getOptions() as $option) {
                if (strlen($option->getShortcut() ?? '') > 1) {
                    $laterOptions[] = $option;
                    continue;
                }
                $this->writeText(PHP_EOL);
                $this->describeInputOption($option, array_merge($options, ['total_width' => $totalWidth]));
            }
            foreach ($laterOptions as $option) {
                $this->writeText(PHP_EOL);
                $this->describeInputOption($option, array_merge($options, ['total_width' => $totalWidth]));
            }
        }
    }

    /**
     * @throws JsonException
     */
    protected function describeCommand(Command $command, array $options = []): void
    {
        if ($description = $command->getDescription()) {
            $this->writeText('<comment>Description:</comment>', $options);
            $this->writeText(PHP_EOL);
            $this->writeText('  ' . $description);
            $this->writeText(PHP_EOL . PHP_EOL);
        }

        $this->writeText('<comment>Usage:</comment>', $options);
        foreach (array_merge([$command->getSynopsisForDescriptor(true)], $command->getAliases(), $command->getUsages()) as $usage) {
            $this->writeText(PHP_EOL);
            $this->writeText('  ' . OutputFormatter::escape($usage), $options);
        }
        $this->writeText(PHP_EOL);

        $definition = $command->getDefinitionForDescriptor();
        if ($definition->getOptions() || $definition->getArguments()) {
            $this->writeText(PHP_EOL);
            $this->describeInputDefinition($definition, $options);
            // $this->writeText(PHP_EOL);
        }

        $help = $command->getProcessedHelp();
        if ($help && $help !== $description) {
            $this->writeText(PHP_EOL);
            $this->writeText('<comment>Help:</comment>', $options);
            $this->writeText(PHP_EOL);
            $this->writeText('  ' . preg_replace('/\r*\n/', PHP_EOL . '  ', $help), $options);
            // $this->writeText(PHP_EOL);
        }
    }

    private function writeText(string $content, array $options = []): void
    {
        $this->write(
            isset($options['raw_text']) && $options['raw_text'] ? strip_tags($content) : $content,
            !isset($options['raw_output']) || !$options['raw_output']
        );
    }

    /**
     * Formats input option/argument default value.
     * @throws JsonException
     */
    private function formatDefaultValue(mixed $default): string
    {
        if (INF === $default) {
            return 'INF';
        }

        if (is_string($default)) {
            $default = OutputFormatter::escape($default);
        }
        elseif (is_array($default)) {
            foreach ($default as $key => $value) {
                if (is_string($value)) {
                    $default[$key] = OutputFormatter::escape($value);
                }
            }
        }

        return str_replace('\\\\', '\\', json_encode_readable($default));
    }

    /**
     * @param InputOption[] $options
     */
    private function calculateTotalWidthForOptions(array $options): int
    {
        $totalWidth = 0;
        foreach ($options as $option) {
            // "-" + shortcut + ", --" + name
            $nameLength = 1 + max(Helper::width($option->getShortcut()), 1) + 4 + Helper::width($option->getName());
            if ($option->isNegatable()) {
                $nameLength += 6 + Helper::width($option->getName()); // |--no- + name
            }
            elseif ($option->acceptValue()) {
                $valueLength = 1 + Helper::width($option->getName()); // = + value
                $valueLength += $option->isValueOptional() ? 2 : 0; // [ + ]

                $nameLength += $valueLength;
            }
            $totalWidth = max($totalWidth, $nameLength);
        }

        return $totalWidth;
    }
}
