<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

/**
 * Prrepl database-backed i18n layer.
 *
 * Languages and translations are stored in the canonical database tables:
 *   i18n_languages
 *   i18n_translations
 *
 * This class deliberately does not use a filesystem i18n directory.
 */
final class I18n
{
    private string $language = 'fr';
    private ?int $languageId = null;
    /** @var array<string,string> */
    private array $translations = [];
    /** @var array<string,array<string,mixed>> */
    private array $languages = [];

    public function __construct(private PDO $db, private array $config = [])
    {
        $this->loadLanguages();
        $this->selectInitialLanguage();
        $this->loadTranslations();
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $code): void
    {
        $code = strtolower(trim($code));
        if ($code === '') {
            return;
        }

        if (!isset($this->languages[$code])) {
            return;
        }

        $this->language = $code;
        $this->languageId = (int)$this->languages[$code]['id'];
        $_SESSION['prrepl_language'] = $code;
        $this->loadTranslations();
    }

    /** @return array<int,array<string,mixed>> */
    public function getLanguages(): array
    {
        return array_values($this->languages);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->translations);
    }

    /**
     * Translate a key. If no translation exists, the supplied fallback is used;
     * otherwise the key itself is returned. Optional replacements use {{name}}.
     */
    public function translate(string $key, ?string $fallback = null, array $replace = []): string
    {
        $value = $this->translations[$key] ?? ($fallback ?? $key);

        foreach ($replace as $name => $replacement) {
            $value = str_replace('{{' . $name . '}}', (string)$replacement, $value);
        }

        return $value;
    }

    public function __invoke(string $key, ?string $fallback = null, array $replace = []): string
    {
        return $this->translate($key, $fallback, $replace);
    }

    private function loadLanguages(): void
    {
        try {
            $stmt = $this->db->query(
                'SELECT id, code, name, native_name, is_default
                 FROM i18n_languages
                 WHERE is_active = 1
                 ORDER BY is_default DESC, id ASC'
            );

            foreach ($stmt->fetchAll() as $row) {
                $code = strtolower(trim((string)$row['code']));
                if ($code === '') {
                    continue;
                }
                $this->languages[$code] = $row;
            }
        } catch (PDOException) {
            // Keep the application usable even if the translation tables are empty
            // or temporarily unavailable. The canonical database still remains the
            // source of truth whenever the tables are available.
        }
    }

    private function selectInitialLanguage(): void
    {
        $requested = strtolower(trim((string)($_SESSION['prrepl_language'] ?? '')));
        if ($requested !== '' && isset($this->languages[$requested])) {
            $this->language = $requested;
            $this->languageId = (int)$this->languages[$requested]['id'];
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if ($userId !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT l.id, l.code
                     FROM users u
                     LEFT JOIN i18n_languages l ON l.id = u.preferred_language_id AND l.is_active = 1
                     WHERE u.id = :id
                     LIMIT 1'
                );
                $stmt->execute(['id' => (int)$userId]);
                $row = $stmt->fetch();
                if ($row && !empty($row['code'])) {
                    $this->language = strtolower((string)$row['code']);
                    $this->languageId = (int)$row['id'];
                    $_SESSION['prrepl_language'] = $this->language;
                    return;
                }
            } catch (PDOException) {
                // Fall through to office/default language.
            }
        }

        $officeId = $_SESSION['office_id'] ?? null;
        if ($officeId !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT l.id, l.code
                     FROM offices o
                     LEFT JOIN i18n_languages l ON l.id = o.default_language_id AND l.is_active = 1
                     WHERE o.id = :id
                     LIMIT 1'
                );
                $stmt->execute(['id' => (int)$officeId]);
                $row = $stmt->fetch();
                if ($row && !empty($row['code'])) {
                    $this->language = strtolower((string)$row['code']);
                    $this->languageId = (int)$row['id'];
                    return;
                }
            } catch (PDOException) {
                // Fall through to database default.
            }
        }

        foreach ($this->languages as $code => $row) {
            if (!empty($row['is_default'])) {
                $this->language = $code;
                $this->languageId = (int)$row['id'];
                return;
            }
        }

        // Prrepl's default UI language is French when the DB has no default row.
        if (isset($this->languages['fr'])) {
            $this->language = 'fr';
            $this->languageId = (int)$this->languages['fr']['id'];
        }
    }

    private function loadTranslations(): void
    {
        $this->translations = [];

        if ($this->languageId === null) {
            return;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT translation_key, translation_value
                 FROM i18n_translations
                 WHERE language_id = :language_id'
            );
            $stmt->execute(['language_id' => $this->languageId]);

            foreach ($stmt->fetchAll() as $row) {
                $this->translations[(string)$row['translation_key']] = (string)$row['translation_value'];
            }
        } catch (PDOException) {
            // Missing translations must never take the application down.
        }
    }
}
