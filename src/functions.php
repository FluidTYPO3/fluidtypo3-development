<?php

function determineVersionFromArguments(array $arguments): string
{
    $versions = ['major', 'minor', 'bugfix'];

    $currentVersion = (function(): string {
        include('ext_emconf.php');
        return reset($EM_CONF)['version'];
    })();

    foreach ($arguments as $argument) {
        if (in_array($argument, $versions, true)) {
            // Version is a shortcut, infer the actual version from current version + bump type:
            return inferVersion($currentVersion, $argument);
        } elseif (preg_match('/\\d+\\.\\d+\\.\\d+/', $argument)) {
            return $argument;
        }
    }

    // Fallback: no version specified, release is an assumed bugfix release.
    return inferVersion($currentVersion, 'bugfix');
}

function determineStabilityFromArguments(array $arguments): string
{
    foreach ($arguments as $argument) {
        if (in_array($argument, Versioner::VALID_STABILITIES, true)) {
            return $argument;
        }
    }
    return 'stable';
}

function inferVersion(string $currentVersion, string $bumpType): string
{
    [$major, $minor, $bugfix] = explode('.', $currentVersion);
    switch ($bumpType) {
        case 'bugfix': return implode('.', [$major, $minor, $bugfix + 1]);
        case 'minor': return implode('.', [$major, $minor + 1, 0]);
        case 'major': return implode('.', [$major + 1, 0, 0]);
    }
    throw new Exception('Unsupported version bump: ' . $bumpType);
}

function commandOrFail(
    string $command,
    bool $expectsEmptyOutput = false,
    ?string $failureReason = null
): array {
    global $dry;
    $code = 0;
    $output = [];
    if ($dry) {
        return ['DRY, would run: ' . $command];
    }
    exec($command, $output, $code);
    if (0 < $code || ($expectsEmptyOutput && 0 < count($output))) {
        $message = ' ! Command failed! ' . $command;
        if ($failureReason) {
            $message .= PHP_EOL;
            $message .= 'Reason for failure: ' . $failureReason;
        }
        if ($expectsEmptyOutput) {
            $message .= PHP_EOL;
            $message .= ' ! No output was expected from command but output occurred:';
            $message .= implode(PHP_EOL, $output);
        }
        exitWithMessage($message, ($expectsEmptyOutput && 0 === $code) ? 1 : $code);
    }
    return $output;
}

function exitWithMessage(string $message, int $code = 0): void
{
    echo $message;
    echo PHP_EOL;
    exit($code);
}

function message(string $message, string $mark = '✓'): void
{
    echo sprintf(' %s ' . $message, $mark);
    echo PHP_EOL;
}

class Versioner
{
    public const PARAMETER_VERSION = 'version';
    public const PARAMETER_STABILITY = 'state';
    public const FILENAME_EXTENSION_CONFIGURATION = 'ext_emconf.php';
    public const STABILITY_STABLE = 'stable';
    public const STABILITY_BETA = 'beta';
    public const STABILITY_ALPHA = 'alpha';
    public const STABILITY_EXPERIMENTAL = 'experimental';
    public const STABILITY_OBSOLETE = 'obsolete';
    public const VALID_STABILITIES = [
        Versioner::STABILITY_STABLE,
        Versioner::STABILITY_BETA,
        Versioner::STABILITY_ALPHA,
        Versioner::STABILITY_OBSOLETE,
        Versioner::STABILITY_EXPERIMENTAL,
    ];

    public function write(string $directory, string $version, string $stability = self::STABILITY_STABLE): bool
    {
        $extensionConfigurationFilename = $this->getExtensionConfigurationFilename($directory);

        // Remove v prefix for version tags
        $version = str_replace('v', '', $version);

        if (!$this->writeExtensionConfigurationFile($extensionConfigurationFilename, $version, $stability)) {
            throw new \RuntimeException(
                'Could not write ' . $extensionConfigurationFilename . ' - please check permissions'
            );
        }
        return true;
    }

    protected function getExtensionConfigurationFilename(?string $directory = null): string
    {
        $directory = rtrim($directory ?? trim(shell_exec('pwd')), '/');
        return $directory . '/' . self::FILENAME_EXTENSION_CONFIGURATION;
    }

    protected function readExtensionConfigurationFile(?string $filename = null): array
    {
        $filename = $filename ?? (rtrim(trim(shell_exec('pwd')), '/') . '/ext_emconf.php');
        if (!file_exists($filename)) {
            throw new \RuntimeException('Extension configuration file ' . $filename . ' does not exist');
        }
        include $filename;
        return $EM_CONF;
    }

    protected function writeExtensionConfigurationFile(string $filename, string $version, string $stability): bool
    {
        $configuration = $this->readExtensionConfigurationFile($filename);
        $extensionKey = key($configuration);
        $configuration[$extensionKey][self::PARAMETER_VERSION] = $version;
        $configuration[$extensionKey][self::PARAMETER_STABILITY] = $stability;
        $contents = '<' . '?php' . PHP_EOL . '$EM_CONF[\'' . $extensionKey . '\'] = ' . var_export($configuration[$extensionKey], true) . ';' . PHP_EOL;
        return file_put_contents($filename, $contents);
    }
}

