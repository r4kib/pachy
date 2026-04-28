# AGENTS.md

## Project Context
- **Framework:** Laravel Zero (`laravel-zero/framework`).
- **Binary:** `pachy` (the main CLI entry point in the root).

## Build
- **Command:** `php pachy app:build`
- **Gotcha (Windows):** Builds often fail with `Resource temporarily unavailable` due to file locks in `C:\Users\rakib\AppData\Local\Temp\box\`.
- **Resolution:** If the build fails, manually delete the contents of that temporary directory and retry.

## Testing
- **Command:** `php vendor/bin/pest`
