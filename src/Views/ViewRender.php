<?php
namespace App\Views;
use DateTime;
use Exception;

class ViewRender
{
    private string $templateDir = __DIR__ . "/templates";
    private array $data = [];
    private array $sections = [];
    private array $sectionStack = [];
    private ?string $layout = null;
    private string $content = "";

    public function __construct()
    {
       
    }

    public function setTemplateDir(string $dir): void
    {
        $this->templateDir = rtrim($dir, '/');
    }

    public function assign(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function mergeData(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    public function render(string $template, array $data = []): string
    {
        $this->mergeData($data);
        $this->sections = [];
        $this->sectionStack = [];
        $this->layout = null;
        $this->content = '';

        $content = $this->renderTemplate($template);

        if ($this->layout) {
            return $this->renderTemplate($this->layout);
        }

        return $content;
    }

    private function renderTemplate(string $template): string
    {
        $templateFile = $this->templateDir . '/' . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new Exception("Template file not found: {$templateFile}");
        }

        extract($this->data);
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }

    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }

    public function section(string $name): void
    {
        $this->sectionStack[] = $name;
        ob_start();
    }

    public function endSection(): void
    {
        if (empty($this->sectionStack)) {
            throw new Exception("No section started");
        }
        
        $name = array_pop($this->sectionStack);
        $content = ob_get_clean();
        
        $this->sections[$name] = $content;
    }

    public function showSection(string $name, string $default = ''): string
    {
        $result = $this->sections[$name] ?? $default;
        error_log("ShowSection called for '{$name}': " . ($result ? "'{$result}'" : 'EMPTY/DEFAULT'));
        return $result;
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]);
    }

    public function include(string $template, array $data = []): string
    {
        $originalData = $this->data;
        $this->mergeData($data);
        
        $content = $this->renderTemplate($template);
        
        $this->data = $originalData;
        return $content;
    }

    public function escape($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public function raw($value): string
    {
        return (string)$value;
    }

    public function date($date, string $format = 'Y-m-d H:i:s'): string
    {
        if ($date instanceof DateTime) {
            return $date->format($format);
        }
        
        return date($format, is_numeric($date) ? $date : strtotime($date));
    }

    public function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        return strlen($text) > $length
            ? substr($text, 0, $length) . $suffix
            : $text;
    }
}