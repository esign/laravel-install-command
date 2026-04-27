## From v1 to v2

### Publishing behavior is now conservative by default

Previously, `PublishableFile` and `PublishableFolder` entries would always overwrite existing files, and `AppendableFile` entries would always append even if the content was already present.

From v2 onwards, the installer is conservative by default:

- `PublishableFile`: skips the file if the target already exists.
- `PublishableFolder`: evaluates files individually — missing files are published, existing files are skipped.
- `AppendableFile`: skips appending if the content is already present in the target file.

Pass `--force` to restore the previous overwrite behaviour:

```bash
php artisan app:install --force
```

### `--force` and `--filter` options are now registered automatically

The `--force` option (and the new `--filter` option) are now registered by the base `InstallCommand` via its `configure()` method. If you previously added `{--force}` to your command's `$signature`, remove it to avoid a duplicate option error:

```php
// Before
protected $signature = 'app:install {--force : Overwrite existing files}';

// After
protected $signature = 'app:install';
```

If your command overrides `configure()`, make sure to call `parent::configure()` so the base options are still registered.

### `FileInstaller` method signatures have changed

The signatures of the following `FileInstaller` methods have changed:

```php
// Before
public function publishFile(string $path, string $target): void
public function publishFolder(string $path, string $target): void
public function appendToFile(string $path, string $target, ?string $search): void

// After
public function publishFile(string $path, string $target, bool $force = true, array $filters = []): PublishResult
public function publishFolder(string $path, string $target, bool $force = true, array $filters = []): array
public function appendToFile(string $path, string $target, ?string $search, bool $force = true, array $filters = []): PublishResult
```

If you call these methods directly in your own code, update the call sites to account for the new parameters and return types.

### Publish overview is now printed after every run

After installing files, the command now prints a summary of what was published and what was skipped:

```text
📄 Publish overview: 3 published, 1 skipped.
Published files:
 + /path/to/source.stub -> /path/to/target.php
Skipped files:
 - /path/to/source.stub -> /path/to/target.php
```

If you override `displayPublishResults()` in a subclass, review your implementation against the updated method signature.
