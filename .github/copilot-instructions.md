# GitHub Copilot Instructions — DepEd Document Tracking System (DOCTRAX)

This file trains Copilot to follow the existing architecture, reuse shared patterns, and avoid duplicate/inconsistent code.

---

## 1. Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 10 (PHP 8.2) |
| Frontend | Blade templates — **no JS framework** (vanilla JS only) |
| DB | MySQL via Eloquent ORM |
| Routing | SPA-style via `/public/js/spa.js` (no full-page reloads between authed pages) |
| Auth | Laravel session auth (`auth()->user()`) |
| CSS | Self-contained `<style>` blocks per Blade file using CSS custom properties |
| Email | Mailpit (local) via Laravel Mail |

---

## 2. File Structure

```
app/
  Http/
    Controllers/
      AdminController.php         — admin-only actions
      AuthController.php          — login / register / activate / password reset
      DashboardController.php     — general dashboard, stats
      DocumentController.php      — submit / track (public + auth)
      ProfileController.php       — profile update, change password
      RecordsController.php       — Records Section document list
      RepresentativeController.php — office/representative dashboard + actions
    Middleware/
      AdminMiddleware.php         — checks role === admin|superadmin
      EnsureAuthenticated.php     — hard auth guard
      NoCacheHeaders.php          — prevent browser caching of authed pages
  Models/
    User.php
    Document.php
    Office.php
    RoutingLog.php
    ActivationToken.php
    TrackingCounter.php
  Services/
    ActivationService.php
    ReferenceNumberService.php
    TrackingNumberService.php
resources/views/
  admin/         — admin-only views
  office/        — office/representative views
  records/       — records section views
  representative/— shared representative views (profile, document detail)
  dashboard/     — general user dashboard
  submit/        — public document submission
  track/         — public document tracking
  auth/          — login, register, activation pages
public/js/
  spa.js         — SPA navigation engine (intercepts links, swaps body)
  form-utils.js  — auto-capitalize, clearable inputs, autocomplete killer
```

---

## 3. User Roles & Auth Helpers

All role checks use methods on the `User` model — **never compare `role` string directly** in views or controllers.

```php
$user->isAdmin()          // role === 'admin' OR 'superadmin'
$user->isSuperAdmin()     // role === 'superadmin' only
$user->isRecords()        // isRepresentative() && office->code === 'RECORDS'
$user->isRepresentative() // account_type === 'representative'
$user->isActive()         // status === 'active'
$user->isPending()        // status === 'pending'
$user->isSuspended()      // status === 'suspended'
```

**Account types:** `representative` (office staff) | `individual` (regular submitter)
**Roles:** `admin` | `superadmin` | `user`

Name format for representative accounts: `"Office Name - Rep Full Name"`
Parse with: `explode(' - ', $user->name, 2)` → `[$officeName, $repName]`

---

## 4. Route Conventions

Defined in `routes/web.php`. Three groups:

```php
// Public — no middleware
Route::get('/track', ...)
Route::get('/submit', ...)
Route::post('/api/submit-document', ...)
Route::post('/api/track-document', ...)

// Authenticated — middleware: ['auth', 'ensure-auth', 'no-cache']
Route::middleware(['auth', 'ensure-auth', 'no-cache'])->group(function () {
    // General
    Route::get('/dashboard', ...)
    Route::get('/office/dashboard', ...)
    Route::get('/records/documents', ...)
    Route::put('/api/profile', ...)

    // Admin-only — nested middleware: ['admin']
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/users', ...)
        Route::put('/api/admin/users/{id}', ...)
        // etc.
    });
});
```

**API routes** are prefixed `/api/` and always return JSON:
```php
return response()->json(['success' => true, 'message' => '...']);
return response()->json(['success' => false, 'message' => '...', 'errors' => []], 422);
```

**Never use `Route::resource()`** — all routes are explicit, REST-style.

---

## 5. Document Model

```php
// Status constants — always use these, never hardcode strings
Document::STATUSES        // ['submitted' => 'Submitted', ...]
Document::STATUS_COLORS   // ['submitted' => '#2563eb', ...]

// Helper methods
$doc->statusLabel()       // human-readable label
$doc->statusColor()       // hex color string

// Relationships
$doc->user()              // submitter (User)
$doc->submittedToOffice() // Office (submitted_to_office_id)
$doc->currentOffice()     // Office (current_office_id)
$doc->currentHandler()    // User (current_handler_id)
$doc->routingLogs()       // ordered ASC by created_at

// Key fields
$doc->tracking_number     // 8-char alphanumeric, auto-generated
$doc->reference_number    // human-readable reference, auto-generated
$doc->status              // see STATUSES constant
$doc->last_action_at      // datetime of last status change
```

---

## 6. Blade View Conventions

### Layout pattern (every authenticated page)

Every authed Blade page is **self-contained** — no shared layout file. Each includes:
1. Full `<head>` with meta, fonts (Poppins), Font Awesome, hamburgers.css
2. Self-contained `<style>` block using CSS custom properties
3. Sidebar (`div.sidebar`) — same structure across all pages
4. Mobile topbar (`div.mob-topbar`) + overlay (`div.mob-overlay`)
5. Main content (`div.main`)
6. Footer (`footer.site-footer`)
7. Inline `<script>` at bottom (IIFE or direct)
8. `spa.js` and `form-utils.js` loaded via `<script src="..." defer>`

### CSS custom properties (reuse everywhere)

```css
:root {
    --primary: #0056b3;
    --primary-dark: #004494;
    --primary-gradient: linear-gradient(135deg, #0056b3 0%, #004494 100%);
    --bg: #f0f2f5;
    --border: #e2e8f0;
    --text-dark: #1b263b;
    --text-muted: #64748b;
    --white: #fff;
    --shadow-sm: 0 2px 12px rgba(0,0,0,.05);
}
```

### Sidebar

Identical structure across all pages. Width: `240px`, fixed, blue `#0056b3`.
```html
<div class="sidebar" id="sidebar">
    <div class="sb-brand"><h2>DOCTRAX</h2><small>Document Tracking System</small></div>
    <nav class="sb-nav">
        <span class="nav-section">Section Label</span>
        <a href="/path" class="active"><i class="fas fa-icon"></i> Label</a>
    </nav>
    <div class="sb-footer">
        <!-- sb-user, sb-avatar, sb-user-info, btn-logout -->
    </div>
</div>
```

### Mobile topbar

```html
<div class="mob-topbar">
    <button class="hamburger hamburger--squeeze mob-hamburger" id="mobHamBtn" type="button" onclick="toggleSidebar()">
        <span class="hamburger-box"><span class="hamburger-inner"></span></span>
    </button>
    <span class="mob-brand">DOCTRAX</span>
    <button onclick="logout()" class="mob-logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
</div>
<div class="mob-overlay" id="mobOverlay" onclick="closeSidebar()"></div>
```

### Mobile responsive breakpoints (all authed pages)

```css
/* Required on every authenticated page */
.mob-topbar { display: none; position: fixed; top: 0; left: 0; right: 0; height: 52px;
    background: #0056b3; z-index: 150; align-items: center; justify-content: space-between;
    padding: 0 16px; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
.mob-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 99; }
.mob-overlay.show { display: block; }
.sidebar { transition: transform .25s ease; }

@media (max-width: 900px) {
    .mob-topbar { display: flex; }
    .sidebar { transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); }
    .main { margin-left: 0; padding: 68px 14px 40px; }
    .site-footer { margin-left: 0; width: 100%; padding: 16px 5%;
        flex-direction: column; gap: 6px; text-align: center; }
}
```

### Mobile sidebar JS (copy-paste identical across every page)

```js
window.toggleSidebar = function () {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('mobOverlay').classList.toggle('show');
    document.getElementById('mobHamBtn').classList.toggle('is-active');
};
window.closeSidebar = function () {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('mobOverlay').classList.remove('show');
    document.getElementById('mobHamBtn').classList.remove('is-active');
};
```

### Footer

```html
<footer class="site-footer">
    <div class="footer-left"><span>&copy; {{ date('Y') }} DepEd Document Tracking System</span></div>
    <div class="footer-right">Developed by Raymond Bautista</div>
</footer>
```
CSS: `margin-left: 240px; width: calc(100% - 240px);` (collapses to `margin-left:0; width:100%` on mobile).

---

## 7. Timeline (Routing Log) Pattern

Used in drawers and full-page document views. Two variants:
- **JS-rendered** (drawers): `div.tl` with `div.tl-item` built via `renderDrawer(doc)` in JS
- **Blade-rendered** (full document pages): `@foreach($tlGroups)` loops

### Required CSS (identical across all 12 timeline files)

```css
.tl { position: relative; }
.tl::before { content: ''; position: absolute; left: 7px; top: 8px; bottom: 8px;
    width: 2px; background: var(--border); z-index: -1; }
.tl-item { position: relative; margin-bottom: 20px; padding-left: 24px; }
.tl-item:last-child { margin-bottom: 0; }
.tl-dot { width: 16px; height: 16px; border-radius: 50%; border: 2.5px solid #fff;
    display: flex; align-items: center; justify-content: center;
    color: #fff; flex-shrink: 0; }
.tl-dot.c-latest { background: #f59e0b; box-shadow: 0 0 0 2px #f59e0b; }
.tl-dot.c-done   { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
.tl-office-hdr { display: flex; align-items: center; font-size: 13px; font-weight: 700;
    color: var(--text-dark); margin: 18px 0 8px -7px; padding-left: 7px;
    padding-bottom: 6px; position: relative; }
.tl-office-hdr::after { content: ''; position: absolute; left: 21px; right: 0;
    bottom: 0; height: 1.5px; background: var(--border); }
.tl-office-hdr:first-child { margin-top: 0; }
```

### HTML structure

```html
<div class="tl-office-hdr">
    <div class="tl-dot c-latest" style="margin-right:5px">
        <i class="fas fa-arrow-up" style="font-size:5px"></i>
    </div>
    <span>Office Name</span>
</div>
<div class="tl-item">
    <div class="tl-dot c-done" style="position:absolute;left:0;top:2px">...</div>
    <div class="tl-action">Action description</div>
    <div class="tl-meta">By Name · timestamp</div>
</div>
```

---

## 8. Button Loading / Form Submission Pattern

**Never use `btnLoading()` / `btnReset()` for form submit buttons.** Those cause the button to stay stuck when client-side validation fails (form-utils.js fires `btnLoading` in capture phase before validation runs).

### Correct pattern for all fetch-based buttons

```js
// 1. Add data-no-auto-loading to any <button type="submit"> to opt out of form-utils auto-intercept
// <button type="submit" id="myBtn" data-no-auto-loading>Save</button>

// 2. In JS, use plain disabled toggle — no loading dots
var btn = document.getElementById('myBtn');
btn.disabled = true;

fetch('/api/endpoint', { ... })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        if (data.success) { /* happy path */ }
        else { /* show error */ }
    })
    .catch(function() {
        btn.disabled = false;
        showToast('Something went wrong.', 'error');
    });
```

### `btnLoading` / `btnReset` ARE acceptable only for

- Action buttons **not** inside a `<form>` that has client-side validation (e.g. modal confirm buttons that fire immediately without validation checks before `btnLoading`).

---

## 9. Toast / Error Feedback

```js
// All authed pages expose window.showToast(message, type)
// type: 'success' | 'error'
showToast('Saved successfully.', 'success');
showToast('Something went wrong.', 'error');
```

Field error pattern (profile/forms with inline validation):
```js
// Show
var el = document.getElementById('err-fieldName');
var sp = el.querySelector('span');
sp.textContent = 'Error message here';
el.classList.add('show');

// Clear all
document.querySelectorAll('.field-err').forEach(function(e) { e.classList.remove('show'); });
document.querySelectorAll('.form-group input').forEach(function(i) { i.classList.remove('error'); });
```

HTML:
```html
<div class="field-err" id="err-fieldName">
    <i class="fas fa-exclamation-circle"></i><span></span>
</div>
```

---

## 10. API Response Format

All API endpoints return consistent JSON:

```php
// Success
return response()->json(['success' => true, 'message' => 'Done.', 'data' => $payload]);

// Validation failure
return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);

// Auth/permission failure
return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);

// Not found
return response()->json(['success' => false, 'message' => 'Not found.'], 404);
```

CSRF token is always read from meta tag:
```js
var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
fetch('/api/endpoint', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    body: JSON.stringify(payload)
});
```

---

## 11. SPA Navigation (`spa.js`)

- Intercepts all internal `<a>` clicks, fetches the target page, swaps `<body>` content via DOM.
- On every navigation, `document.body.removeAttribute('style')` is called to clear any leftover `overflow:hidden` from drawer/modal pages.
- Full reload is forced for: `/login`, `/register`, `/activate`, `/set-password`, `/activation-status`.
- Scripts in `<body>` are re-executed after each swap; `spa.js` and `form-utils.js` themselves are **not** re-executed (they persist across swaps via event delegation).
- `window.spa:reinit` CustomEvent fires when an already-loaded external script is skipped.

**Do not add `window.location.href` redirects** for internal navigation — use SPA links or let the page reload naturally after a success action.

---

## 12. form-utils.js Behaviors (Auto-Applied)

These run automatically on every page — **do not re-implement them manually**:

| Behavior | Trigger | Opt-out |
|----------|---------|---------|
| Auto-capitalize (title case) | `input` event on `type="text"` | `data-no-capitalize` attribute |
| Clearable `×` button | All `type="text"` and `type="search"` | `data-no-clearable` attribute |
| Autocomplete disabled | All inputs, selects, forms | — (always applied) |
| Submit button auto-loading dots | `<form>` submit event | `data-no-auto-loading` on button |

Excluded from capitalize: fields whose `id`/`name`/`placeholder` contains `email`, `password`, `mobile`, `phone`, `track`.

---

## 13. Drawer Pattern

Right-panel side drawer used for document detail. Structure:

```html
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
<div class="drawer" id="drawer">
    <div class="drawer-head">
        <div class="drawer-head-info">
            <h3 id="dTitle"></h3>
            <div class="drawer-ref" id="dRef"></div>
            <div class="drawer-status" id="dStatus"></div>
        </div>
        <button class="drawer-close" onclick="closeDrawer()">&#10005;</button>
    </div>
    <div class="drawer-body">
        <div class="drawer-meta"> <!-- 2-col grid of metadata --> </div>
        <div class="drawer-tl-head">Routing History</div>
        <div class="drawer-timeline"><div class="tl" id="tlContainer"></div></div>
    </div>
</div>
```

Opening a drawer sets `document.body.style.overflow = 'hidden'`.
Closing resets `document.body.style.overflow = ''`.

---

## 14. Services

| Service | Purpose |
|---------|---------|
| `TrackingNumberService` | Generates 8-char uppercase tracking numbers (sequential + encoded) |
| `ReferenceNumberService` | Generates human-readable reference numbers (e.g. `REF-2026-00001`) |
| `ActivationService` | Creates activation tokens, sends `ActivationMail` |

Always inject via constructor or call statically — never replicate their logic inline.

---

## 15. Patterns to Avoid

- ❌ `Route::resource()` — use explicit named routes
- ❌ Shared Blade layout (`@extends`) — each view is self-contained
- ❌ Hardcoding status strings (`'submitted'`, `'received'`) — use `Document::STATUSES`
- ❌ Direct `$user->role === 'admin'` checks — use `$user->isAdmin()`
- ❌ `btnLoading()` on submit buttons with client-side validation — use `btn.disabled = true/false`
- ❌ Inline `overflow:hidden` on body without a corresponding reset in drawer-close handler
- ❌ New JS utility functions for capitalize / clear / autocomplete — form-utils.js handles them
- ❌ Adding `window.location.href` for SPA-navigable routes — let spa.js handle it
- ❌ Duplicating sidebar/topbar/footer HTML — copy the exact structure from an existing page
