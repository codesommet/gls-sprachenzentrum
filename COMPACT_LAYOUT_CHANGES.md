# Compact Layout Changes

Changes made to reduce the "zoomed in" feel of the backoffice admin panel.

## File Modified

`resources/views/layouts/main.blade.php` — inline `<style>` block (lines 27–133)

## How to Undo

Delete everything between `<style>` and `</style>` in `main.blade.php` and replace with the original code below:

```html
<style>
    .logo-lg {
        width: 160px !important;
        height: 60px !important;
        object-fit: contain !important;
        display: block !important;
    }

    .m-header .b-brand {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
    }

    img[src*="assets/images/logo/gls.png"],
    img[src$="gls.png"] {
        width: 160px !important;
        height: 60px !important;
        object-fit: contain !important;
    }
</style>
```

## What Changed

### Sidebar

| What | Before | After |
|------|--------|-------|
| Width | 280px | 250px |
| Link padding | 14px 20px | 9px 18px |
| Link font-size | 14px | 13px |
| Icon size | 20px | 18px |
| Icon container | 24x24 | 20x20 |
| Icon margin-right | 15px | 10px |
| Item margin | 0 10px | 0 8px |
| Submenu link padding | default | 7px 18px 7px 52px |
| Caption padding | 16px 23px 8px | 12px 20px 6px |
| Caption font-size | 12px | 11px |
| Caption span font-size | 14px | 13px |
| m-header height | 74px | 64px |
| User card dropdown width | 250px | 220px |

### Header

| What | Before | After |
|------|--------|-------|
| min-height | 74px | 64px |
| left offset | 280px | 250px |
| m-header width | 280px | 250px |
| m-header height | 74px | 64px |
| pc-h-item min-height | 74px | 64px |

### Footer

| What | Before | After |
|------|--------|-------|
| margin-left | 280px | 250px |
| margin-top | 74px | 64px |

### Logo

| What | Before | After |
|------|--------|-------|
| width | 160px | 140px |
| height | 60px | 50px |

### Choices.js Dropdowns

| What | Before | After |
|------|--------|-------|
| Root font-size | 16px | 13px |
| Item padding | 10px | 6px 10px |
| Item font-size | 14px | 13px |
| Inner padding | default | 4px 8px |
| Inner min-height | default | 36px |
| Input font-size | default | 13px |
