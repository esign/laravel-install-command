<?php

namespace Esign\InstallCommand\Tests;

use PHPUnit\Framework\Attributes\Test;
use Esign\InstallCommand\Exceptions\CouldNotInstallNodePackagesException;
use Esign\InstallCommand\Tests\Support\InstallCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

final class InstallCommandTest extends TestCase
{
    #[Test]
    public function it_can_publish_files(): void
    {
        $this->artisan(InstallCommand::class, ['--force' => true]);

        $this->assertFileExists(app_path('Services/UserService.php'));
    }

    #[Test]
    public function it_can_publish_folders(): void
    {
        $this->artisan(InstallCommand::class, ['--force' => true]);

        $this->assertFileExists(base_path('resources/vendor/stubs/js/app.js'));
        $this->assertFileExists(base_path('resources/vendor/stubs/views/layouts/app.blade.php'));
    }

    #[Test]
    public function it_can_append_after_the_search_value_in_a_file(): void
    {
        $this->artisan(InstallCommand::class, ['--force' => true]);

        $this->assertTrue(str_contains(
            haystack: File::get(app_path('Models/User.php')),
            needle: <<<PHP
                public function isAdmin(): bool
                {
                    return false;
                }
            PHP,
        ));
    }

    #[Test]
    public function it_can_install_composer_packages(): void
    {
        $this->artisan(InstallCommand::class, ['--force' => true]);

        Process::assertRan('composer require my/composer-package my/specific-composer-package:^1.0');
        Process::assertRan('composer require --dev my/dev-composer-package');
    }

    #[Test]
    public function it_can_throw_an_exception_when_no_package_json_file_is_present(): void
    {
        File::delete(base_path('package.json'));
        $command = $this->artisan(InstallCommand::class, ['--force' => true]);

        $command->expectsOutput("Could not find package.json file in the root of your project. Please create one using `npm init`");
    }

    #[Test]
    public function it_can_install_node_packages(): void
    {
        $this->artisan(InstallCommand::class, ['--force' => true]);

        Process::assertRan('npm install my/node-package my/specific-node-package@^1.0');
        Process::assertRan('npm install --save-dev my/dev-node-package');
    }

    #[Test]
    public function it_does_not_overwrite_existing_files_without_force_flag(): void
    {
        $filePath = app_path('Services/UserService.php');

        // First run to create the file
        $this->artisan(InstallCommand::class);
        $originalContent = File::get($filePath);

        // Modify the file
        $modifiedContent = '<?php // Modified content';
        File::put($filePath, $modifiedContent);

        // Run the command again without --force
        $this->artisan(InstallCommand::class);

        // File should not be overwritten
        $this->assertEquals($modifiedContent, File::get($filePath));
    }

    #[Test]
    public function it_overwrites_existing_files_with_force_flag(): void
    {
        $filePath = app_path('Services/UserService.php');

        // First run to create the file
        $this->artisan(InstallCommand::class, ['--force' => true]);
        $originalContent = File::get($filePath);

        // Modify the file
        $modifiedContent = '<?php // Modified content';
        File::put($filePath, $modifiedContent);

        // Run the command with --force
        $this->artisan(InstallCommand::class, ['--force' => true]);

        // File should be overwritten back to original
        $this->assertEquals($originalContent, File::get($filePath));
    }

    #[Test]
    public function it_does_not_overwrite_existing_folders_without_force_flag(): void
    {
        // First run to create the folder
        $this->artisan(InstallCommand::class, ['--force' => true]);
        $originalFile = base_path('resources/vendor/stubs/js/app.js');

        // Create a new file in the folder
        File::put($originalFile, '// Modified content');

        // Run the command again without --force
        $this->artisan(InstallCommand::class);

        // Folder should not be overwritten
        $this->assertEquals('// Modified content', File::get($originalFile));
    }

    #[Test]
    public function it_publishes_missing_files_in_existing_folders_without_force_flag(): void
    {
        $existingFile = base_path('resources/vendor/stubs/js/app.js');
        $missingFile = base_path('resources/vendor/stubs/views/layouts/app.blade.php');

        $this->artisan(InstallCommand::class, ['--force' => true]);

        File::put($existingFile, '// Modified content');
        File::delete($missingFile);

        $this->assertFileDoesNotExist($missingFile);

        $this->artisan(InstallCommand::class);

        $this->assertEquals('// Modified content', File::get($existingFile));
        $this->assertFileExists($missingFile);
    }

    #[Test]
    public function it_overwrites_existing_folders_with_force_flag(): void
    {
        $originalFile = base_path('resources/vendor/stubs/js/app.js');

        // First run to create the folder
        $this->artisan(InstallCommand::class, ['--force' => true]);
        $originalContent = File::get($originalFile);

        // Modify the file
        File::put($originalFile, '// Modified content');

        // Run the command with --force
        $this->artisan(InstallCommand::class, ['--force' => true]);

        // Folder should be overwritten back to original
        $this->assertEquals($originalContent, File::get($originalFile));
    }

    #[Test]
    public function it_creates_file_with_appended_content_when_target_does_not_exist_without_force_flag(): void
    {
        $filePath = app_path('Models/User.php');
        $appendedSnippet = trim(File::get(__DIR__ . '/Support/stubs/app/Models/User.php'));

        File::delete($filePath);
        $this->assertFileDoesNotExist($filePath);

        $this->artisan(InstallCommand::class);

        $this->assertFileExists($filePath);
        $this->assertStringContainsString($appendedSnippet, File::get($filePath));
        $this->assertSame(1, substr_count(File::get($filePath), $appendedSnippet));
    }

    #[Test]
    public function it_creates_file_with_appended_content_when_target_does_not_exist_with_force_flag(): void
    {
        $filePath = app_path('Models/User.php');
        $appendedSnippet = trim(File::get(__DIR__ . '/Support/stubs/app/Models/User.php'));

        File::delete($filePath);
        $this->assertFileDoesNotExist($filePath);

        $this->artisan(InstallCommand::class, ['--force' => true]);

        $this->assertFileExists($filePath);
        $this->assertStringContainsString($appendedSnippet, File::get($filePath));
        $this->assertSame(1, substr_count(File::get($filePath), $appendedSnippet));
    }

    #[Test]
    public function it_does_not_append_to_existing_files_without_force_flag(): void
    {
        $filePath = app_path('Models/User.php');
        $appendedSnippet = trim(File::get(__DIR__ . '/Support/stubs/app/Models/User.php'));

        // First run
        $this->artisan(InstallCommand::class);
        $contentAfterFirstRun = File::get($filePath);

        // Run the command again without --force
        $this->artisan(InstallCommand::class);

        // File should not be modified again
        $this->assertEquals($contentAfterFirstRun, File::get($filePath));
        $this->assertSame(1, substr_count(File::get($filePath), $appendedSnippet));
    }

    #[Test]
    public function it_appends_to_existing_files_with_force_flag(): void
    {
        $filePath = app_path('Models/User.php');
        $appendedSnippet = trim(File::get(__DIR__ . '/Support/stubs/app/Models/User.php'));

        // First run
        $this->artisan(InstallCommand::class);
        $contentAfterFirstRun = File::get($filePath);

        // Run the command with --force
        $this->artisan(InstallCommand::class, ['--force' => true]);

        // File should be appended to again
        $contentAfterSecondRun = File::get($filePath);
        $this->assertNotEquals($contentAfterFirstRun, $contentAfterSecondRun);
        $this->assertStringContainsString($contentAfterFirstRun, $contentAfterSecondRun);
        $this->assertSame(2, substr_count($contentAfterSecondRun, $appendedSnippet));
    }

    #[Test]
    public function it_shows_publish_overview_for_published_files(): void
    {
        $command = $this->artisan(InstallCommand::class, ['--force' => true]);

        $command->expectsOutput('📄 Publish overview: 4 published, 0 skipped.');
    }

    #[Test]
    public function it_shows_publish_overview_for_skipped_files(): void
    {
        $this->artisan(InstallCommand::class, ['--force' => true]);
        $command = $this->artisan(InstallCommand::class);

        $command->expectsOutput('📄 Publish overview: 0 published, 4 skipped.');
    }

    #[Test]
    public function it_can_filter_publishable_files_by_target_path(): void
    {
        $this->artisan(InstallCommand::class, ['--force' => true, '--filter' => ['Services']])
            ->expectsOutput('📄 Publish overview: 1 published, 3 skipped.')
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Services/UserService.php'));
    }

    #[Test]
    public function it_can_filter_publishable_folders_by_target_path(): void
    {
        $this->artisan(InstallCommand::class, ['--force' => true, '--filter' => ['resources']])
            ->expectsOutput('📄 Publish overview: 2 published, 2 skipped.')
            ->assertExitCode(0);

        $this->assertFileExists(base_path('resources/vendor/stubs/js/app.js'));
        $this->assertFileExists(base_path('resources/vendor/stubs/views/layouts/app.blade.php'));
    }

    #[Test]
    public function it_can_filter_individual_files_inside_publishable_folders(): void
    {
        File::deleteDirectory(base_path('resources/vendor/stubs'));

        $this->artisan(InstallCommand::class, ['--force' => true, '--filter' => ['js']])
            ->expectsOutput('📄 Publish overview: 1 published, 3 skipped.')
            ->assertExitCode(0);

        $this->assertFileExists(base_path('resources/vendor/stubs/js/app.js'));
        $this->assertFileDoesNotExist(base_path('resources/vendor/stubs/views/layouts/app.blade.php'));
    }

    #[Test]
    public function it_can_filter_appendable_files_by_target_path(): void
    {
        File::delete(app_path('Models/User.php'));

        $this->artisan(InstallCommand::class, ['--force' => true, '--filter' => ['Models']])
            ->expectsOutput('📄 Publish overview: 1 published, 3 skipped.')
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Models/User.php'));
    }

    #[Test]
    public function it_shows_skipped_when_no_files_match_the_filter(): void
    {
        $this->artisan(InstallCommand::class, ['--filter' => ['NonExistentPath']])
            ->expectsOutput('📄 Publish overview: 0 published, 4 skipped.')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_performs_case_insensitive_filtering(): void
    {
        $this->artisan(InstallCommand::class, ['--force' => true, '--filter' => ['services']])
            ->expectsOutput('📄 Publish overview: 1 published, 3 skipped.')
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Services/UserService.php'));
    }

    #[Test]
    public function it_can_filter_with_multiple_values(): void
    {
        File::delete(app_path('Models/User.php'));

        $this->artisan(InstallCommand::class, ['--force' => true, '--filter' => ['Services', 'Models']])
            ->expectsOutput('📄 Publish overview: 2 published, 2 skipped.')
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Services/UserService.php'));
        $this->assertFileExists(app_path('Models/User.php'));
    }
}
