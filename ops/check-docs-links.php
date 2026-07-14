<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$excluded = ['.git', 'backend/vendor', 'frontend/node_modules', 'example'];
$files = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (! $file->isFile() || strtolower($file->getExtension()) !== 'md') {
        continue;
    }

    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if (array_filter($excluded, fn (string $prefix): bool => $relative === $prefix || str_starts_with($relative, $prefix.'/'))) {
        continue;
    }

    $files[] = $file->getPathname();
}

sort($files);
$errors = [];
$checked = 0;

foreach ($files as $path) {
    $contents = file_get_contents($path);
    if ($contents === false) {
        $errors[] = relativePath($root, $path).': cannot read file';
        continue;
    }

    preg_match_all('/!?\[[^\]]*\]\((<[^>]+>|[^\s)]+)(?:\s+["\'][^)]*["\'])?\)/u', $contents, $matches, PREG_OFFSET_CAPTURE);

    foreach ($matches[1] as [$rawTarget, $offset]) {
        $target = trim($rawTarget, '<>');
        if ($target === '' || preg_match('#^(?:https?://|mailto:|tel:|data:|javascript:)#i', $target)) {
            continue;
        }

        $checked++;
        $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
        [$fileTarget, $fragment] = array_pad(explode('#', $target, 2), 2, '');
        $fileTarget = rawurldecode(preg_replace('/\?.*$/', '', $fileTarget) ?? $fileTarget);

        if ($fileTarget === '') {
            $resolved = $path;
        } elseif (str_starts_with($fileTarget, '/')) {
            // Root-leading paths in project docs describe application routes,
            // not repository files.
            continue;
        } else {
            $resolved = normalizePath(dirname($path).'/'.$fileTarget);
        }

        if (! file_exists($resolved)) {
            $errors[] = sprintf('%s:%d: missing target %s', relativePath($root, $path), $line, $target);
            continue;
        }

        if ($fragment !== '' && is_file($resolved) && strtolower(pathinfo($resolved, PATHINFO_EXTENSION)) === 'md') {
            $headings = markdownAnchors((string) file_get_contents($resolved));
            if (! in_array(rawurldecode($fragment), $headings, true)) {
                $errors[] = sprintf('%s:%d: missing anchor #%s in %s', relativePath($root, $path), $line, $fragment, relativePath($root, $resolved));
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Documentation link check failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

printf("Documentation link check passed: %d Markdown files, %d internal links.\n", count($files), $checked);

function normalizePath(string $path): string
{
    $absolute = str_starts_with($path, '/');
    $parts = [];
    foreach (explode('/', str_replace('\\', '/', $path)) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }
        if ($part === '..') {
            array_pop($parts);
            continue;
        }
        $parts[] = $part;
    }

    return ($absolute ? '/' : '').implode('/', $parts);
}

function relativePath(string $root, string $path): string
{
    return str_replace('\\', '/', ltrim(substr($path, strlen($root)), '/\\'));
}

/** @return list<string> */
function markdownAnchors(string $markdown): array
{
    preg_match_all('/^#{1,6}\s+(.+?)\s*#*$/mu', $markdown, $matches);
    $anchors = [];
    $counts = [];

    foreach ($matches[1] as $heading) {
        $heading = preg_replace('/[`*_~]/u', '', $heading) ?? $heading;
        $heading = preg_replace('/\s/u', '-', mb_strtolower($heading)) ?? $heading;
        $base = trim(preg_replace('/[^\p{L}\p{N}_-]/u', '', $heading) ?? $heading, '-');
        $count = $counts[$base] ?? 0;
        $anchors[] = $count === 0 ? $base : $base.'-'.$count;
        $counts[$base] = $count + 1;
    }

    return $anchors;
}
