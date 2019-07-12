<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use Soatok\AnthroKit\Splice;

/**
 * Class Themes
 * @package Soatok\FaqOff\Splices
 */
class Themes extends Splice
{
    /**
     * @param string $name
     * @param string $description
     * @param array $custom
     * @param string|null $url
     * @return int|null
     * @throws \Exception
     */
    public function createTheme(
        string $name,
        string $description,
        array $custom = [],
        ?string $url = null
    ): ?int {
        $css = $custom['css_files'] ?? [];
        $js = $custom['js_files'] ?? [];
        $twig = $custom['twig_vars'] ?? [];
        $this->db->beginTransaction();
        if (!$url) {
            // No URL given, generate one.
            $url = $this->generateUniqueUrl($name);
        } elseif ($this->db->exists(
            "SELECT count(*) FROM faqoff_themes WHERE url = ?",
            $url
        )) {
            // Collision detected. Generate unique URL.
            $url = $this->generateUniqueUrl($name, $url);
        }

        $themeId = $this->db->insertGet(
            'faqoff_themes',
            $this->encode([
                'name' => $name,
                'url' => $url,
                'description' => $description,
                'css_files' => $css,
                'js_files' => $js,
                'twig_vars' => $twig
            ]),
            'themeid'
        );
        if (!$themeId) {
            throw new \Exception('Could not create new theme');
        }

        $this->db->commit();
        return $themeId;
    }

    /**
     * @return array
     */
    public function getAllThemes(): array
    {
        $themes = [];
        $allThemes = $this->db->run('SELECT * FROM faqoff_themes ORDER BY name ASC');
        foreach ($allThemes as $theme) {
            $themes[] = $this->decode($theme);
        }
        return $themes;
    }

    /**
     * @param int $themeId
     * @return array
     */
    public function getById(int $themeId): array
    {
        $theme = $this->db->row(
            "SELECT * FROM faqoff_themes WHERE themeid = ?",
            $themeId
        );
        if (!$theme) {
            return [];
        }
        return $this->decode($theme);
    }


    /**
     * @param int $themeId
     * @param string $name
     * @param string $description
     * @param array $custom
     * @return bool
     */
    public function updateTheme(
        int $themeId,
        string $name,
        string $description,
        array $custom = []
    ): bool {
        $css = $custom['css_files'] ?? [];
        $js = $custom['js_files'] ?? [];
        $twig = $custom['twig_vars'] ?? [];
        $this->db->beginTransaction();
        $this->db->update(
            'faqoff_themes',
            $this->encode([
                'name' => $name,
                'description' => $description,
                'css_files' => $css,
                'js_files' => $js,
                'twig_vars' => $twig
            ]),
            ['themeid' => $themeId]
        );
        return $this->db->commit();
    }

    /**
     * Decode the JSON-encoded columns
     *
     * @param array $theme
     * @return array
     */
    protected function encode(array $theme): array
    {
        $theme['twig_vars'] = json_encode($theme['twig_vars'] ?? []);
        $theme['css_files'] = json_encode($theme['css_files'] ?? []);
        $theme['js_files'] = json_encode($theme['js_files'] ?? []);
        return $theme;
    }

    /**
     * Decode the JSON-encoded columns
     *
     * @param array $theme
     * @return array
     */
    protected function decode(array $theme): array
    {
        $theme['twig_vars'] = json_decode($theme['twig_vars'] ?? '[]', true);
        $theme['css_files'] = json_decode($theme['css_files'] ?? '[]', true);
        $theme['js_files'] = json_decode($theme['js_files'] ?? '[]', true);
        if (is_null($theme['twig_vars'])) {
            $theme['twig_vars'] = [];
        }
        return $theme;
    }

    /**
     * @param string $name
     * @param string|null $url
     * @return string
     */
    protected function generateUniqueUrl(string $name, ?string $url = null): string
    {
        if ($url) {
            $base = preg_replace('#\-\d$#', '', $url);
        } else {
            $base = preg_replace('#[^a-z0-9\-]#', '-', strtolower($name));
        }
        $base = trim($base, '-');
        $url = $base;
        $i = 1;
        while ($this->db->exists(
            "SELECT count(*) FROM faqoff_themes WHERE url = ?",
            $url
        )) {
            $url = $base . '-' . (++$i);
        }
        return $url;
    }
}
