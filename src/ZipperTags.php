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

        $value = ($files instanceof \Statamic\Fields\Value) ? $files->value() : collect($files);

        // Handle asset fields with `max_files: 1`
        if ($value instanceof \Statamic\Assets\Asset) {
            return [$value->id()];
        }

        if ($value instanceof \Statamic\Assets\OrderedQueryBuilder) {
            $value = $value->get();
        }

        return $value->map(function ($file) {
            if (is_string($file)) {
                return $file;
            }

            if ($file instanceof \Statamic\Assets\Asset) {
                return $file->id();
            }
        })->filter()->all();
    }

    protected function hasFilesToZip($files): bool
    {
        if (is_null($files)) {
            return false;
        }


        if ($files instanceof \Statamic\Fields\Value) {
            if (is_null($files->raw())) {
                return false;
            }

            if (! $files->fieldtype() instanceof \Statamic\Fieldtypes\Assets\Assets) {
                return false;
            }
        }

        if (is_array($files)) {
            return collect($files)->map(function ($file) {
                return $this->hasFilesToZip($file) ?? null;
            })->filter()->isNotEmpty();
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
