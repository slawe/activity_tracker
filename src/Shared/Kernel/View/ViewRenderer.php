<?php

declare(strict_types=1);

namespace App\Shared\Kernel\View;

use RuntimeException;

final class ViewRenderer
{
    public function __construct(
        private readonly string $viewsPath,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $templatePath = $this->viewsPath . '/' . ltrim($template, '/');
        if (!is_file($templatePath)) {
            throw new RuntimeException(sprintf('View "%s" does not exist.', $template));
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        if ($template === 'layout.php') {
            return $content;
        }

        return $this->render('layout.php', [
            ...$data,
            'content' => $content,
        ]);
    }
}
