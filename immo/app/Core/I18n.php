<?php
declare(strict_types=1);

final class I18n
{
    private array $translations = [];
    private string $locale;

    public function __construct(string $locale, array $supported, string $langPath)
    {
        $this->locale = in_array($locale, $supported, true) ? $locale : $supported[0];
        $file = rtrim($langPath, '/') . '/' . $this->locale . '.php';
        $this->translations = is_file($file) ? require $file : [];
    }

    public function getLocale(): string { return $this->locale; }

    public function t(string $key, array $replace = []): string
    {
        $value = $this->translations[$key] ?? $key;
        foreach ($replace as $name => $replacement) {
            $value = str_replace(':' . $name, (string) $replacement, $value);
        }
        return $value;
    }
}
