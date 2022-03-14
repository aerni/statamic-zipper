<?php

namespace Aerni\Zipper;

use Statamic\Tags\Tags;

class ZipperTags extends Tags
{
    protected static $handle = 'zip';

    public function wildcard(): ?string
    {
        if (! $this->canCreateZip()) {
            return null;
        }

        return action([ZipperController::class, 'create'], [
            'filename' => $this->filename(),
            'files' => $this->files(),
        ]);
    }

    protected function filename(): string
    {
        return $this->params->get('filename') ?? time();
    }

    protected function files(): ?array
    {
        $files = $this->context->get($this->method);

        if (! $this->hasFilesToZip($files)) {
            return null;
        }

        $value = $files->value();

        // Handle asset fields with `max_files: 1`
        if ($value instanceof \Statamic\Assets\Asset) {
            return [$value->id()];
        }

        if ($value instanceof \Statamic\Assets\OrderedQueryBuilder) {
            $value = $value->get();
        }

        return $value->map(function ($file) {
            return $file->id();
        })->all();
    }

    protected function hasFilesToZip($files): bool
    {
        if (is_null($files->raw())) {
            return false;
        }

        if (! $files instanceof \Statamic\Fields\Value) {
            return false;
        }

        if (! $files->fieldtype() instanceof \Statamic\Fieldtypes\Assets\Assets) {
            return false;
        }

        return true;
    }

    protected function canCreateZip(): bool
    {
        if (is_null($this->files())) {
            return false;
        }

        return true;
    }
}
