# Pagination Settings

This file is the reusable pagination reference for future edits in this repo.

If a future AI task asks about pagination settings, this is the first file to read.

## Source Of Truth

- Shared Blade partial: `resources/views/partials/shared-pagination.blade.php`
- Active rollout target: server-rendered list pages that already paginate or clearly need pagination controls

## Rules

1. Reuse the shared partial instead of writing page-specific pagination markup.
2. Treat `resources/views/partials/shared-pagination.blade.php` as the single UI source of truth.
3. Keep hover simple: dark hover state only. No lift animation and no hover shadow.
4. Keep the page indicator text-only: `Page X / Y`.
5. Keep `Jump to` available on shared pagination.
6. Preserve active filters/search query params when changing pages.
7. Use `->withQueryString()` on filtered single-paginator pages.
8. Use named paginators on pages with multiple paginators so they do not fight over the same `page` query key.
9. Apply pagination only to pages that actually paginate server-side or need navigation across paged results.
10. Do not add page-specific paginator HTML or page-specific paginator CSS unless the shared partial is intentionally being upgraded for the whole site.

## Multi-Paginator Rule

If a page has more than one paginator:

- assign explicit page names in the controller
- example:

```php
->paginate(20, ['*'], 'documents_page')->withQueryString();
->paginate(24, ['*'], 'users_page')->withQueryString();
```

The shared partial reads the paginator page name automatically, so `Jump to` and page links stay aligned.

## Shared Include Pattern

```blade
@include('partials.shared-pagination', [
    'paginator' => $documents,
    'itemLabel' => 'documents',
])
```

## Active Controller Wiring

- `app/Http/Controllers/RecordsController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/RepresentativeController.php`

## Active Rollout Targets

- `resources/views/records/index.blade.php`
- `resources/views/admin/documents.blade.php`
- `resources/views/admin/my-documents.blade.php`
- `resources/views/admin/users.blade.php`
- `resources/views/dashboard/documents.blade.php`
- `resources/views/office/my-documents.blade.php`
- `resources/views/office/dashboard.blade.php`
- `resources/views/ict/index.blade.php`
- `resources/views/office/search.blade.php`

## Legacy-Aligned View

- `resources/views/representative/search.blade.php`
  - aligned to the shared partial for consistency
  - currently not wired by the active `/office/search` route

## Notes

- `office/search` is a multi-paginator page and must keep distinct page names for documents vs users.
- Old classes like `.page-btn`, `.pagination-bar`, `.pagination-wrap`, and `.page-link` in older views should be treated as legacy leftovers, not the source of truth.
- Quick audit command:

```powershell
rg -n "paginate\\(|shared-pagination|hasPages\\(|links\\(" app resources -S
```
- Legacy pagination CSS/markup may still exist in some views; when touching those views again, prefer removing dead pagination-specific code if it is safe to do so.
