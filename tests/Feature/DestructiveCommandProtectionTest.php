<?php

namespace Tests\Feature;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

class DestructiveCommandProtectionTest extends TestCase
{
    public function test_destructive_artisan_commands_are_blocked_in_production(): void
    {
        App::shouldReceive('environment')->andReturn(true);

        foreach (['migrate:fresh', 'migrate:refresh', 'migrate:reset', 'db:wipe'] as $command) {
            try {
                Event::dispatch(new CommandStarting($command, new ArrayInput([]), new NullOutput()));
                $this->fail("Expected [{$command}] to be blocked in production.");
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString($command, $exception->getMessage());
            }
        }
    }
}
