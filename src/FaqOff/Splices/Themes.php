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
        return $theme;
    }
}
