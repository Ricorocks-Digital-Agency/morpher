<?php

namespace RicorocksDigitalAgency\Morpher;

use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;

class Morpher
{
    protected $allMorphs;
    protected $morphs = [];
    protected $inspections = [];

    public function test(string $morph)
    {
        return tap(new Inspection(), fn($inspection) => $this->inspections[$morph][] = $inspection);
    }

    public function setup()
    {
        if (!config('morpher.enabled')) {
            return;
        }

        Event::listen(MigrationStarted::class, fn($event) => $this->prepareMorphs($event));
        Event::listen(MigrationEnded::class, fn($event) => $this->runMorphs($event));
    }

    protected function prepareMorphs($event)
    {
        if (!$this->isBuildingDatabase($event)) {
            return;
        }

        $this->getMorphs($event->migration)->each(fn($morphs) => $morphs->prepare());
    }

    protected function isBuildingDatabase($event)
    {
        return $event->method == "up";
    }

    protected function getMorphs($migration)
    {
        return $this->morphs[get_class($migration)] ??= $this->allMorphs()
            ->filter(fn($morph) => $morph::migration() == get_class($migration))
            ->map(fn($morph) => app()->make($morph));
    }

    protected function allMorphs()
    {
        if ($this->allMorphs) {
            return $this->allMorphs;
        }

        static::includeMorphClasses();

        $this->allMorphs = collect(get_declared_classes())
            ->filter(fn($className) => is_subclass_of($className, Morph::class));

        return $this->allMorphs;
    }

    protected static function includeMorphClasses()
    {
        collect(config('morpher.paths', []))
            ->flatMap(fn($directory) => File::allFiles($directory))
            ->each(fn(\SplFileInfo $fileInfo) => include_once $fileInfo->getRealPath());
    }

    protected function runMorphs($event)
    {
        if (!$this->isBuildingDatabase($event)) {
            return;
        }

        DB::transaction(
            fn() => $this->getMorphs($event->migration)
                ->filter(fn($morph) => $morph->canRun())
                ->each(
                    function ($morph) use ($event) {
                        $inspections = collect(data_get($this->inspections, get_class($morph)));
                        $inspections->each?->runBefore($morph);
                        $morph->run($event);
                        $inspections->each?->runAfter($morph);
                    }
                )
        );
    }
}
